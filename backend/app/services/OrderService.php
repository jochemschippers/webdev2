<?php
// app/services/OrderService.php

namespace App\Services;

require_once dirname(__FILE__) . '/../repositories/OrderRepository.php';
require_once dirname(__FILE__) . '/../repositories/OrderItemRepository.php';
require_once dirname(__FILE__) . '/../repositories/GraphicCardRepository.php';
require_once dirname(__FILE__) . '/../repositories/UserRepository.php';
require_once dirname(__FILE__) . '/../models/Order.php';
require_once dirname(__FILE__) . '/../models/OrderItem.php';
require_once dirname(__FILE__) . '/../models/User.php';
require_once dirname(__FILE__) . '/../utils/Mailer.php';
require_once dirname(__FILE__) . '/../utils/PdfGenerator.php';

use App\Repositories\OrderRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\GraphicCardRepository;
use App\Repositories\UserRepository;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Utils\Mailer;
use App\Utils\PdfGenerator;

class OrderService {
    private $orderRepository;
    private $graphicCardRepository;
    private $orderItemRepository;
    private $userRepository;

    public function __construct() {
        $this->orderRepository = new OrderRepository();
        $this->graphicCardRepository = new GraphicCardRepository();
        $this->orderItemRepository = new OrderItemRepository();
        $this->userRepository = new UserRepository();
    }

    /**
     * Retrieves all orders, including their associated order items.
     *
     * @return array An array of Order model instances, each with an 'items' property containing OrderItem models.
     */
    public function getAllOrders() {
        $ordersData = $this->orderRepository->getAll();
        $orders = [];
        foreach ($ordersData as $data) {
            $order = new Order($data);
            
            $rawItems = $this->orderItemRepository->getAllByOrderId($order->id);
            $hydratedItems = [];
            foreach ($rawItems as $itemData) {
                $hydratedItems[] = new OrderItem($itemData);
            }
            $order->items = $hydratedItems;
            
            $orders[] = $order;
        }
        return $orders;
    }

    public function getOrderById(int $id) {
        $orderData = $this->orderRepository->getById($id);
        if ($orderData) {
            return $orderData;
        }
        return false;
    }

    /**
     * Creates a new order.
     *
     * @param int $userId The ID of the user placing the order.
     * @param array $items An array of associative arrays, each with 'graphic_card_id' and 'quantity'.
     * @return array|false Returns the created Order data (associative array) on success, false on failure.
     */
    public function createOrder(int $userId, array $items) {
        if (empty($userId) || empty($items)) {
            return false;
        }

        $totalAmount = 0;
        $orderItemsDataForCreation = [];

        foreach ($items as $item) {
            if (empty($item['graphic_card_id']) || empty($item['quantity']) || !is_numeric($item['quantity']) || $item['quantity'] <= 0) {
                error_log("OrderService: Invalid item data received for graphic_card_id or quantity.");
                return false;
            }

            $graphicCard = $this->graphicCardRepository->getById($item['graphic_card_id']);
            if (!$graphicCard) {
                error_log("OrderService: Graphic card with ID " . $item['graphic_card_id'] . " not found.");
                return false;
            }
            if ($graphicCard['stock'] < $item['quantity']) {
                error_log("OrderService: Not enough stock for graphic card ID " . $item['graphic_card_id'] . ". Requested: " . $item['quantity'] . ", Available: " . $graphicCard['stock']);
                return false; // Not enough stock
            }

            $itemPrice = (float)$graphicCard['price'];
            $totalAmount += $itemPrice * $item['quantity'];

            $orderItemsDataForCreation[] = [
                'graphic_card_id' => (int)$item['graphic_card_id'],
                'quantity' => (int)$item['quantity'],
                'price_at_purchase' => $itemPrice,
                'graphic_card_name' => $graphicCard['name'] // Include name for email/PDF consistency
            ];
            // In a real application, you would also decrement the stock here using the GraphicCardRepository
            // e.g., $this->graphicCardRepository->decrementStock($item['graphic_card_id'], $item['quantity']);
        }

        $orderData = [
            'user_id' => $userId,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'items' => $orderItemsDataForCreation
        ];

        $newOrderId = $this->orderRepository->create($orderData);

        if ($newOrderId) {
            $createdOrderData = $this->orderRepository->getById($newOrderId);
            
            $user = $this->userRepository->getUserById($userId);
            if ($user && isset($user['email'])) {
                $recipientEmail = $user['email'];
                $subject = "Your GPU Shop Order Confirmation - Order #" . $newOrderId;
                $messageBody = "
                    <p>Dear {$user['username']},</p>
                    <p>Thank you for your order! Your order #<strong>{$newOrderId}</strong> has been successfully placed.</p>
                    <p><strong>Order Summary:</strong></p>
                    <ul>";
                foreach ($createdOrderData['items'] as $item) {
                    $messageBody .= "<li>{$item['graphic_card_name']} (x{$item['quantity']}) - \${$item['price_at_purchase']} each</li>";
                }
                $messageBody .= "
                    </ul>
                    <p><strong>Total Amount:</strong> \${$createdOrderData['total_amount']}</p>
                    <p><strong>Current Status:</strong> " . ucfirst($createdOrderData['status']) . "</p>
                    <p>We will notify you once your order has been processed and shipped.</p>
                    <p>Best regards,</p>
                    <p>The GPU Shop Team</p>
                ";
                Mailer::sendEmail($recipientEmail, $subject, $messageBody);
            } else {
                error_log("OrderService: Could not send order confirmation email. User email not found for ID: {$userId}");
            }

            return $createdOrderData;
        }
        return false;
    }

    /**
     * Updates an existing order.
     *
     * @param int $id The ID of the order to update.
     * @param array $data Associative array of fields to update (e.g., 'status').
     * @return array|false Returns the updated Order data (associative array) on success, false on failure.
     */
    public function updateOrder(int $id, array $data) {
        $currentOrder = $this->orderRepository->getById($id);
        if (!$currentOrder) {
            error_log("OrderService: Attempted to update non-existent order ID: " . $id);
            return false; // Order not found
        }

        $updateFields = [];
        // Only allow specific fields to be updated for security and logic control
        if (isset($data['user_id'])) {
             $updateFields['user_id'] = $data['user_id'];
        }
        if (isset($data['total_amount'])) {
            $updateFields['total_amount'] = $data['total_amount'];
        }
        if (isset($data['status'])) {
            $allowedStatuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
            if (!in_array($data['status'], $allowedStatuses)) {
                error_log("OrderService: Invalid status provided for order ID " . $id . ": " . $data['status']);
                return false; // Invalid status
            }
            $updateFields['status'] = $data['status'];
        }

        if (empty($updateFields)) {
            error_log("OrderService: No valid fields provided for update for order ID: " . $id);
            return false; // No valid fields to update
        }

        $success = $this->orderRepository->update($id, $updateFields);

        if ($success) {
            $updatedOrderData = $this->orderRepository->getById($id);

            // Generate PDF Invoice if status is 'completed' (or 'paid')
            if (isset($updateFields['status']) && $updateFields['status'] === 'completed') {
                $user = $this->userRepository->getUserById($updatedOrderData['user_id']);
                if ($user && isset($user['email'])) {
                    $invoiceHtml = $this->generateInvoiceHtml($updatedOrderData, $user);
                    $filename = "invoice_order_" . $updatedOrderData['id'] . "_" . date('Ymd_His') . ".pdf"; // Add timestamp for uniqueness
                    $pdfOutput = PdfGenerator::generatePdf($invoiceHtml, $filename);

                    if ($pdfOutput) {
                        error_log("OrderService: Generated PDF invoice for order #{$updatedOrderData['id']}. Attaching to email.");
                        // NEW: Prepare attachment array
                        $attachments = [[
                            'content' => $pdfOutput,
                            'name' => $filename,
                            'type' => 'application/pdf'
                        ]];
                        // NEW: Send email with PDF attachment
                        $recipientEmail = $user['email'];
                        $subject = "Your GPU Shop Invoice - Order #" . $updatedOrderData['id'];
                        $messageBody = "
                            <p>Dear {$user['username']},</p>
                            <p>Your order #<strong>{$updatedOrderData['id']}</strong> has been marked as <strong>completed</strong>. Thank you for your purchase!</p>
                            <p>Please find your invoice attached to this email.</p>
                            <p>If you have any questions, please do not hesitate to contact us.</p>
                            <p>Best regards,</p>
                            <p>The GPU Shop Team</p>
                        ";
                        Mailer::sendEmail($recipientEmail, $subject, $messageBody, $attachments);
                    } else {
                        error_log("OrderService: Failed to generate PDF invoice for order #{$updatedOrderData['id']}.");
                    }
                } else {
                    error_log("OrderService: Cannot generate invoice PDF. User email not found for ID: {$updatedOrderData['user_id']}");
                }
            }
            
            return $updatedOrderData;
        }
        error_log("OrderService: Database update failed for order ID: " . $id);
        return false;
    }

    public function deleteOrder(int $id) {
        return $this->orderRepository->delete($id);
    }

    /**
     * Generates HTML content for the invoice.
     * @param array $orderData The order data including items.
     * @param array $userData The user data.
     * @return string The HTML string for the invoice.
     */
    private function generateInvoiceHtml(array $orderData, array $userData): string {
        $itemsHtml = '';
        foreach ($orderData['items'] as $item) {
            // Ensure graphic_card_name exists for display
            $itemName = htmlspecialchars($item['graphic_card_name'] ?? 'N/A');
            $itemQuantity = htmlspecialchars($item['quantity']);
            $itemPrice = number_format($item['price_at_purchase'], 2);
            $itemTotal = number_format($item['quantity'] * $item['price_at_purchase'], 2);

            $itemsHtml .= "
                <tr>
                    <td>{$itemName}</td>
                    <td>{$itemQuantity}</td>
                    <td>\${$itemPrice}</td>
                    <td>\${$itemTotal}</td>
                </tr>";
        }

        $invoiceDate = date('Y-m-d H:i:s');
        $orderDate = date('Y-m-d', strtotime($orderData['order_date']));
        $totalAmountFormatted = number_format($orderData['total_amount'], 2);

        // Basic user address/shipping details (replace with actual fields if available in your user/order tables)
        $userAddress = "N/A<br>N/A, N/A"; // Placeholder if not in user/order data
        // Example if you had shipping_address_line1, city, etc. in orderData or userData
        // if (isset($orderData['shipping_address_line1'])) {
        //     $userAddress = htmlspecialchars($orderData['shipping_address_line1']) . "<br>";
        //     $userAddress .= htmlspecialchars($orderData['shipping_city']) . ", ";
        //     $userAddress .= htmlspecialchars($orderData['shipping_state']) . " ";
        //     $userAddress .= htmlspecialchars($orderData['shipping_zip_code']);
        // }


        return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <title>Invoice for Order #{$orderData['id']}</title>
                <style>
                    body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; line-height: 1.5; color: #333; }
                    .container { max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
                    h1, h2, h3 { color: #000; }
                    h1 { text-align: center; margin-bottom: 20px; }
                    .invoice-header, .invoice-details, .order-items, .invoice-footer { margin-bottom: 20px; }
                    .invoice-header table, .invoice-details table, .order-items table { width: 100%; border-collapse: collapse; }
                    .invoice-header td, .invoice-details td, .order-items th, .order-items td { padding: 8px; border: 1px solid #eee; text-align: left; }
                    .order-items th { background-color: #f2f2f2; }
                    .total { text-align: right; font-weight: bold; font-size: 14px; padding-top: 10px; }
                    .total td { border: none; }
                    .text-right { text-align: right; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h1>Invoice</h1>

                    <div class='invoice-header'>
                        <table>
                            <tr>
                                <td style='width:50%;'>
                                    <strong>GPU Shop</strong><br>
                                    123 Tech Avenue<br>
                                    Silicon Valley, CA 90210<br>
                                    Email: contact@gpushop.com
                                </td>
                                <td class='text-right' style='width:50%;'>
                                    <strong>INVOICE #INV-{$orderData['id']}-" . date('Ymd') . "</strong><br>
                                    Invoice Date: {$invoiceDate}<br>
                                    Order Date: {$orderDate}<br>
                                    Order ID: #{$orderData['id']}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class='invoice-details'>
                        <table>
                            <tr>
                                <td style='width:50%;'>
                                    <strong>Bill To:</strong><br>
                                    " . htmlspecialchars($userData['username']) . "<br>
                                    " . htmlspecialchars($userData['email']) . "<br>
                                    {$userAddress}
                                </td>
                                <td class='text-right' style='width:50%;'>
                                    <strong>Payment Status:</strong> Paid<br>
                                    <strong>Order Status:</strong> " . ucfirst($orderData['status']) . "
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class='order-items'>
                        <table>
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {$itemsHtml}
                            </tbody>
                            <tfoot>
                                <tr class='total'>
                                    <td colspan='3'>Total Amount:</td>
                                    <td>\${$totalAmountFormatted}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class='invoice-footer' style='text-align: center; margin-top: 30px; font-size: 10px; color: #777;'>
                        <p>Thank you for your business!</p>
                        <p>&copy; " . date('Y') . " GPU Shop. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }
}
