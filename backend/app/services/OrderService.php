<?php
// app/services/OrderService.php

namespace App\Services;

require_once dirname(__FILE__) . '/../repositories/OrderRepository.php';
require_once dirname(__FILE__) . '/../repositories/OrderItemRepository.php';
require_once dirname(__FILE__) . '/../repositories/GraphicCardRepository.php'; // For stock management
require_once dirname(__FILE__) . '/../models/Order.php';
require_once dirname(__FILE__) . '/../models/OrderItem.php';
require_once dirname(__FILE__) . '/../models/GraphicCard.php';
require_once dirname(__FILE__) . '/../utils/PdfGenerator.php'; // For invoice generation
require_once dirname(__FILE__) . '/../utils/Mailer.php'; // For sending email
require_once dirname(__FILE__) . '/../repositories/Repository.php'; // Needed to pass common connection

use App\Repositories\OrderRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\GraphicCardRepository;
use App\Repositories\Repository; // Use the base Repository to get the connection
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\GraphicCard;
use App\Utils\PdfGenerator;
use App\Utils\Mailer;

class OrderService {
    private $orderRepository;
    public function __construct() {
        $this->orderRepository = new OrderRepository();
    }

    public function getAllOrders(?int $userId = null) {
        // These methods do not need to be part of a transaction, so use default repositories
        $orderRepository = new OrderRepository();
        $ordersData = $orderRepository->getAll($userId);
        $orders = [];
        foreach ($ordersData as $orderData) {
            $order = new Order($orderData);
            // Fetch and set order items
            $orderItemsData = $orderRepository->getOrderItemsByOrderId($order->id);
            $order->items = [];
            foreach ($orderItemsData as $itemData) {
                $order->items[] = new OrderItem($itemData);
            }
            $orders[] = $order;
        }
        return $orders;
    }

    public function getOrderById(int $id) {
        // These methods do not need to be part of a transaction, so use default repositories
        $orderRepository = new OrderRepository();
        $orderData = $orderRepository->getById($id);
        if ($orderData) {
            $order = new Order($orderData);
            // Fetch and set order items
            $orderItemsData = $orderRepository->getOrderItemsByOrderId($order->id);
            $order->items = [];
            foreach ($orderItemsData as $itemData) {
                $order->items[] = new OrderItem($itemData);
            }
            return $order;
        }
        return false;
    }

    public function createOrder(int $userId, array $items): Order|array {
        if (empty($items)) {
            error_log("OrderService: Cannot create order with no items.");
            return ['success' => false, 'message' => 'No items provided for the order.', 'http_status' => 400];
        }

        // We instantiate a new OrderRepository here to ensure we get a fresh connection
        // to begin the transaction.
        $mainOrderRepository = new OrderRepository();
        $mainOrderRepository->beginTransaction(); // Start the transaction on this connection

        // Get the underlying PDO connection from the OrderRepository
        // This connection will be shared with other repositories for atomicity.
        $sharedConnection = $mainOrderRepository->getConnection(); // Assuming Repository has a getConnection() method

        // Instantiate other repositories with the shared connection
        $orderItemRepository = new OrderItemRepository($sharedConnection);
        $graphicCardRepository = new GraphicCardRepository($sharedConnection);


        // Calculate total amount and validate stock
        $totalAmount = 0;
        $graphicCardsToUpdate = []; // Stores card_id => quantity for stock decrement
        
        try {
            foreach ($items as $item) {
                if (!isset($item['graphic_card_id']) || !isset($item['quantity']) || !isset($item['price_at_purchase'])) {
                    throw new \Exception('Invalid order item data: missing graphic_card_id, quantity, or price_at_purchase.');
                }

                // Fetch graphic card details using the repository with the shared connection
                $graphicCardData = $graphicCardRepository->getById($item['graphic_card_id']);
                if (!$graphicCardData) {
                    throw new \Exception("Product (ID: {$item['graphic_card_id']}) not found.");
                }
                $graphicCard = new GraphicCard($graphicCardData);

                if ((int)$graphicCard->stock < (int)$item['quantity']) {
                    throw new \Exception("Insufficient stock for {$graphicCard->name}. Only {$graphicCard->stock} available.");
                }

                $totalAmount += $item['quantity'] * $item['price_at_purchase'];
                $graphicCardsToUpdate[$item['graphic_card_id']] = (int)$graphicCard->stock - (int)$item['quantity'];
            }

            // Create the main order record within the shared transaction
            $orderData = [
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'status' => 'pending' // Default status when created
            ];
            $newOrderId = $mainOrderRepository->create($orderData);

            if (!$newOrderId) {
                throw new \Exception('Failed to create order in main repository.');
            }

            // Create order items and decrement stock using repositories with the shared connection
            foreach ($items as $item) {
                $orderItemData = [
                    'order_id' => $newOrderId,
                    'graphic_card_id' => $item['graphic_card_id'],
                    'quantity' => $item['quantity'],
                    'price_at_purchase' => $item['price_at_purchase']
                ];
                $newOrderItemId = $orderItemRepository->create($orderItemData);
                if (!$newOrderItemId) {
                    throw new \Exception('Failed to create order item for graphic card ID ' . $item['graphic_card_id']);
                }

                // Decrement stock using the repository with the shared connection
                $graphicCardId = $item['graphic_card_id'];
                $newStock = $graphicCardsToUpdate[$graphicCardId];
                $stockUpdated = $graphicCardRepository->updateStock($graphicCardId, $newStock);
                if (!$stockUpdated) {
                    throw new \Exception('Failed to update stock for graphic card ID ' . $graphicCardId);
                }
            }

            $mainOrderRepository->commit(); // Commit the transaction

            // Fetch the newly created order with its items for the response
            // This also needs to use the mainOrderRepository or a new one to fetch the committed data
            $createdOrder = $this->getOrderById($newOrderId);
            if ($createdOrder) {
                error_log("OrderService: Order ID {$newOrderId} created and stock updated successfully.");
                return $createdOrder;
            } else {
                // This case is unlikely if commit succeeded, but good for robustness
                throw new \Exception('Order created but failed to retrieve after creation.');
            }

        } catch (\Exception $e) {
            $mainOrderRepository->rollBack(); // Rollback the transaction on error
            error_log("OrderService: Error creating order: " . $e->getMessage());
            // Determine HTTP status based on the error type
            $httpStatus = 500;
            if (strpos($e->getMessage(), 'not found') !== false) {
                $httpStatus = 404;
            } elseif (strpos($e->getMessage(), 'Insufficient stock') !== false) {
                $httpStatus = 409;
            } elseif (strpos($e->getMessage(), 'Invalid order item data') !== false) {
                $httpStatus = 400;
            }
            return ['success' => false, 'message' => 'Failed to create order: ' . $e->getMessage(), 'http_status' => $httpStatus];
        }
    }

    public function updateOrder(int $orderId, array $data): Order|false {
        if (empty($data)) {
            error_log("OrderService: No data provided for order update (ID: {$orderId}).");
            return false;
        }

        // Validate status if it's being updated
        if (isset($data['status'])) {
            $allowedStatuses = ['pending', 'paid', 'processing', 'shipped', 'completed', 'cancelled'];
            if (!in_array($data['status'], $allowedStatuses)) {
                error_log("OrderService: Invalid status provided for order ID {$orderId}: {$data['status']}");
                return false;
            }
        }

        $success = $this->orderRepository->update($orderId, $data);

        if ($success) {
            // If the status is updated to 'paid' or 'processing', send an invoice email
            if (isset($data['status']) && ($data['status'] === 'paid' || $data['status'] === 'processing')) {
                $updatedOrder = $this->getOrderById($orderId); // Fetch full order details
                if ($updatedOrder) {
                    $this->sendOrderConfirmationEmail($updatedOrder);
                }
            }
            return $this->getOrderById($orderId); // Fetch and return the updated order
        }
        error_log("OrderService: Failed to update order in repository (ID: {$orderId}).");
        return false;
    }

    public function deleteOrder(int $id): bool {
        // The database schema likely handles cascading deletes for order_items,
        // so deleting the order record should also delete its items.
        return $this->orderRepository->delete($id);
    }
    private function sendOrderConfirmationEmail(Order $order): bool {
        $user = $this->orderRepository->getUserById($order->user_id); // Assuming OrderRepository has a method to get user by ID

        if (!$user || empty($user['email'])) {
            error_log("OrderService: Cannot send order confirmation. User or user email not found for order ID: {$order->id}");
            return false;
        }

        $subject = "Your GPU Shop Order Confirmation - Order #{$order->id}";

        // Prepare HTML for the invoice
        $invoiceHtml = $this->generateInvoiceHtml($order, $user);
        $pdfContent = PdfGenerator::generatePdf($invoiceHtml, "invoice_order_{$order->id}");

        if (!$pdfContent) {
            error_log("OrderService: Failed to generate PDF invoice for order ID: {$order->id}. Skipping email attachment.");
            // Continue to send email without PDF if PDF generation failed
        }

        $attachments = [];
        if ($pdfContent) {
            $attachments[] = [
                'content' => $pdfContent,
                'name' => "invoice_order_{$order->id}.pdf",
                'type' => 'application/pdf',
                'encoding' => 'base64' // Dompdf output is binary, so base64 encode for email
            ];
        }

        // Email body content
        $messageBody = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
                    h2 { color: #5c6ac4; }
                    ul { list-style: none; padding: 0; }
                    li { margin-bottom: 5px; }
                    .total { font-weight: bold; font-size: 1.1em; margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px; }
                    .footer { margin-top: 20px; font-size: 0.9em; color: #777; text-align: center; }
                    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #e2e8f0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Order Confirmation - GPU Shop</h2>
                    <p>Dear {$user['username']},</p>
                    <p>Thank you for your recent purchase! Your order <strong>#{$order->id}</strong> has been successfully placed and is now <strong>{$order->status}</strong>.</p>
                    
                    <h3>Order Details:</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>";
                            foreach ($order->items as $item) {
                                $messageBody .= "
                                <tr>
                                    <td>{$item->graphic_card_name}</td>
                                    <td>{$item->quantity}</td>
                                    <td>$" . number_format($item->price_at_purchase, 2) . "</td>
                                    <td>$" . number_format($item->quantity * $item->price_at_purchase, 2) . "</td>
                                </tr>";
                            }
                            $messageBody .= "
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan='3' style='text-align:right;'><strong>Total Amount:</strong></td>
                                <td><strong>$" . number_format($order->total_amount, 2) . "</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <p>We will notify you once your order has been shipped.</p>
                    <p>If you have any questions, please don't hesitate to contact us.</p>
                    <p>Thank you for choosing GPU Shop!</p>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " GPU Shop. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        return Mailer::sendEmail($user['email'], $subject, $messageBody, $attachments);
    }

    private function generateInvoiceHtml(Order $order, array $user): string {
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Invoice for Order #{$order->id}</title>
            <style>
                body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; margin: 0; padding: 0; }
                .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, .15); font-size: 14px; line-height: 20px; color: #555; }
                .invoice-box table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
                .invoice-box table td, .invoice-box table th { padding: 8px; vertical-align: top; }
                .invoice-box table tr.top table td { padding-bottom: 20px; }
                .invoice-box table tr.top table td.title { font-size: 35px; line-height: 35px; color: #333; }
                .invoice-box table tr.information table td { padding-bottom: 30px; }
                .invoice-box table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; padding: 8px; }
                .invoice-box table tr.details td { padding-bottom: 20px; }
                .invoice-box table tr.item td{ border-bottom: 1px solid #eee; }
                .invoice-box table tr.item.last td { border-bottom: none; }
                .invoice-box table tr.total td:nth-child(2) { border-top: 2px solid #eee; font-weight: bold; }
                .invoice-box table td.total-amount { text-align: right; font-size: 18px; font-weight: bold; }
                .invoice-box .rtl { direction: rtl; }
                .invoice-box .rtl table { text-align: right; }
                .invoice-box .rtl table tr td:nth-child(2) { text-align: left; }
                .text-right { text-align: right; }
            </style>
        </head>
        <body>
            <div class='invoice-box'>
                <table cellpadding='0' cellspacing='0'>
                    <tr class='top'>
                        <td colspan='4'>
                            <table>
                                <tr>
                                    <td class='title'>
                                        GPU Shop
                                    </td>
                                    <td class='text-right'>
                                        Invoice #: {$order->id}<br>
                                        Order Date: " . date('M d, Y', strtotime($order->order_date)) . "<br>
                                        Status: " . ucfirst($order->status) . "
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr class='information'>
                        <td colspan='4'>
                            <table>
                                <tr>
                                    <td>
                                        Customer Name: {$user['username']}<br>
                                        Customer Email: {$user['email']}<br>
                                        Order User ID: {$order->user_id}
                                    </td>
                                    <td>
                                        GPU Shop Inc.<br>
                                        123 GPU Lane<br>
                                        Tech City, TX 75001
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr class='heading'>
                        <td>Item</td>
                        <td class='text-right'>Quantity</td>
                        <td class='text-right'>Unit Price</td>
                        <td class='text-right'>Subtotal</td>
                    </tr>";
                    foreach ($order->items as $item) {
                        $html .= "<tr class='item'>
                            <td>{$item->graphic_card_name}</td>
                            <td class='text-right'>{$item->quantity}</td>
                            <td class='text-right'>$" . number_format($item->price_at_purchase, 2) . "</td>
                            <td class='text-right'>$" . number_format($item->quantity * $item->price_at_purchase, 2) . "</td>
                        </tr>";
                    }
                    $html .= "
                    <tr class='total'>
                        <td colspan='3'></td>
                        <td class='total-amount'>Total: $" . number_format($order->total_amount, 2) . "</td>
                    </tr>
                </table>
                <div style='text-align: center; margin-top: 30px; font-size: 12px; color: #999;'>
                    Thank you for your business!
                </div>
            </div>
        </body>
        </html>";

        return $html;
    }
}
