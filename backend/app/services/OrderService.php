<?php
// app/services/OrderService.php

namespace App\Services;

use \PDOException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\OrderRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\GraphicCardRepository;
use App\Repositories\UserRepository;

// REQUIRED REPOSITORIES
require_once dirname(__FILE__) . '/../repositories/OrderRepository.php';
require_once dirname(__FILE__) . '/../repositories/OrderItemRepository.php';
require_once dirname(__FILE__) . '/../repositories/GraphicCardRepository.php';
require_once dirname(__FILE__) . '/../repositories/UserRepository.php';

// REQUIRED MODELS
require_once dirname(__FILE__) . '/../models/Order.php';
require_once dirname(__FILE__) . '/../models/OrderItem.php';
require_once dirname(__FILE__) . '/../models/User.php';


class OrderService {
    private $orderRepository;
    private $orderItemRepository;
    private $graphicCardRepository;
    private $userRepository;

    public function __construct() {
        $this->orderRepository = new OrderRepository();
        $this->orderItemRepository = new OrderItemRepository();
        $this->graphicCardRepository = new GraphicCardRepository();
        $this->userRepository = new UserRepository();
    }

    public function getAllOrders(?int $userId = null) {
        $ordersData = $this->orderRepository->getAll($userId);
        $orders = [];
        foreach ($ordersData as $orderData) {
            $order = new Order($orderData);
            if (empty($order->username) && isset($orderData['user_id'])) {
                $userData = $this->userRepository->getUserById($orderData['user_id']);
                if ($userData) {
                    $order->username = $userData['username'];
                }
            }
            $itemsData = $this->orderItemRepository->getAllByOrderId($order->id);
            $orderItems = [];
            foreach ($itemsData as $itemData) {
                $orderItems[] = new OrderItem($itemData);
            }
            $order->items = $orderItems;
            $orders[] = $order;
        }
        return $orders;
    }

    public function getOrderById(int $orderId) {
        $orderData = $this->orderRepository->getById($orderId);
        if (!$orderData) {
            return false;
        }
        $order = new Order($orderData);
        if (empty($order->username) && isset($orderData['user_id'])) {
            $userData = $this->userRepository->getUserById($orderData['user_id']);
            if ($userData) {
                $order->username = $userData['username'];
            }
        }
        $itemsData = $this->orderItemRepository->getAllByOrderId($order->id);
        $orderItems = [];
        foreach ($itemsData as $itemData) {
            $orderItems[] = new OrderItem($itemData);
        }
        $order->items = $orderItems;
        return $order;
    }

    /**
     * Creates a new order along with its items and decrements stock.
     * This entire operation is wrapped in a database transaction to ensure atomicity.
     *
     * @param int $userId The ID of the user placing the order.
     * @param array $itemsData An array of associative arrays, each representing an order item:
     * ['graphic_card_id' => int, 'quantity' => int, 'price_at_purchase' => float]
     * @return Order|array Returns the created Order object (with items and username) on success,
     * or an associative array with error details on failure (e.g., ['success' => false, 'message' => '...', 'error_code' => '...']).
     */
    public function createOrder(int $userId, array $itemsData) {
        error_log("OrderService: createOrder method started.");
        $totalAmount = 0;
        $processedItems = [];

        error_log("OrderService: Attempting to begin transaction.");
        if (!$this->orderRepository->beginTransaction()) {
            error_log("OrderService: Failed to begin transaction.");
            return ['success' => false, 'message' => 'Failed to initiate order transaction.', 'error_code' => 'TRANSACTION_FAILED'];
        }
        error_log("OrderService: Transaction begun successfully.");

        try {
            // 1. Validate items and calculate total amount before any database modifications
            foreach ($itemsData as $item) {
                if (!isset($item['graphic_card_id']) || !isset($item['quantity']) || !isset($item['price_at_purchase'])) {
                    throw new \Exception("Missing required item data.", 400); // Using HTTP status codes for custom exception codes
                }

                $graphicCard = $this->graphicCardRepository->getById($item['graphic_card_id']);

                if (!$graphicCard) {
                    throw new \Exception("Graphic card not found for ID: " . $item['graphic_card_id'], 404);
                }
                if ($graphicCard['stock'] < $item['quantity']) {
                    throw new \Exception("Insufficient stock for graphic_card_id: " . $item['graphic_card_id'] . ". Available: " . $graphicCard['stock'] . ", Requested: " . $item['quantity'], 409); // 409 Conflict for insufficient stock
                }

                error_log("OrderService: Validated and prepared item graphic_card_id: " . $item['graphic_card_id'] . ", quantity: " . $item['quantity']);

                $totalAmount += $item['quantity'] * $item['price_at_purchase'];
                $processedItems[] = $item;
            }

            error_log("OrderService: All items validated successfully. Calculated total amount: " . $totalAmount);

            // 2. Create the main order record
            $orderData = [
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'status' => 'pending'
            ];

            error_log("OrderService: Attempting to create main order record.");
            $orderId = $this->orderRepository->create($orderData);

            if (!$orderId) {
                throw new \Exception("Failed to create main order record in the database.", 500);
            }
            error_log("OrderService: Main order record created with ID: " . $orderId . ". Proceeding to decrement stock.");

            // 3. Decrement stock for each item
            foreach ($processedItems as $item) {
                error_log("OrderService: Decrementing stock for graphic_card_id: " . $item['graphic_card_id'] . ", quantity: " . $item['quantity']);
                $stockDecremented = $this->graphicCardRepository->decrementStock($item['graphic_card_id'], $item['quantity']);
                if (!$stockDecremented) {
                    // This scenario should be rare if stock was checked before and transaction is active,
                    // but it's a fallback for race conditions or unexpected DB behavior.
                    throw new \Exception("Failed to decrement stock for graphic_card_id: " . $item['graphic_card_id'] . ". Stock may have been insufficient during update.", 409);
                }
            }
            error_log("OrderService: All item stocks decremented successfully.");

            // 4. Create individual order item records
            $createdOrderItems = [];
            foreach ($processedItems as $item) {
                $orderItemData = [
                    'order_id' => $orderId,
                    'graphic_card_id' => $item['graphic_card_id'],
                    'quantity' => $item['quantity'],
                    'price_at_purchase' => $item['price_at_purchase']
                ];
                error_log("OrderService: Creating order item for graphic_card_id: " . $item['graphic_card_id']);
                $orderItemId = $this->orderItemRepository->create($orderItemData);
                if (!$orderItemId) {
                    throw new \Exception("Failed to create order item for graphic_card_id: " . $item['graphic_card_id'], 500);
                }

                $graphicCardInfo = $this->graphicCardRepository->getById($item['graphic_card_id']);
                $orderItemData['id'] = $orderItemId;
                $orderItemData['graphic_card_name'] = $graphicCardInfo['name'] ?? 'Unknown Graphic Card';
                $createdOrderItems[] = new OrderItem($orderItemData);
            }
            error_log("OrderService: All order items created successfully.");

            // If all operations were successful, commit the transaction
            error_log("OrderService: Attempting to commit transaction.");
            if (!$this->orderRepository->commitTransaction()) {
                throw new \Exception("Failed to commit transaction.", 500);
            }
            error_log("OrderService: Transaction committed successfully.");

            // Fetch the complete order details including username for the final response
            $finalOrder = $this->orderRepository->getById($orderId);
            if ($finalOrder) {
                $orderModel = new Order($finalOrder);
                $orderModel->items = $createdOrderItems;
                return $orderModel; // Success: Return the Order model
            } else {
                error_log("OrderService: Failed to retrieve order after successful creation and commit. Order ID: " . $orderId);
                return ['success' => false, 'message' => 'Order created but failed to retrieve details.', 'error_code' => 'ORDER_RETRIEVAL_FAILED', 'http_status' => 500];
            }

        } catch (PDOException $e) {
            error_log("OrderService: PDOException during order creation: " . $e->getMessage());
            error_log("OrderService: Rolling back transaction due to PDOException.");
            $this->orderRepository->rollBack();
            return ['success' => false, 'message' => 'Database error during order creation: ' . $e->getMessage(), 'error_code' => 'DB_ERROR', 'http_status' => 500];
        } catch (\Exception $e) {
            error_log("OrderService: General Exception during order creation: " . $e->getMessage());
            error_log("OrderService: Rolling back transaction due to general exception.");
            $this->orderRepository->rollBack();
            // Return structured error including the custom code (HTTP status) from the exception
            return ['success' => false, 'message' => $e->getMessage(), 'error_code' => 'APPLICATION_ERROR', 'http_status' => $e->getCode() ?: 400];
        } finally {
            error_log("OrderService: createOrder method finished.");
            // The rollBack() method itself checks if a transaction is active before trying to roll back.
            $this->orderRepository->rollBack(); // Ensure rollback is attempted if transaction is still active for any reason
        }
    }

    public function updateOrder(int $orderId, array $data) {
        $success = $this->orderRepository->update($orderId, $data);
        if ($success) {
            return $this->getOrderById($orderId);
        }
        return false;
    }

    public function deleteOrder(int $orderId) {
        return $this->orderRepository->delete($orderId);
    }
}
