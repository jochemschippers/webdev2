<?php
// app/services/OrderService.php

namespace App\Services;

require_once dirname(__FILE__) . '/../repositories/OrderRepository.php';
require_once dirname(__FILE__) . '/../models/Order.php';
require_once dirname(__FILE__) . '/../models/OrderItem.php';
require_once dirname(__FILE__) . '/../repositories/GraphicCardRepository.php'; // To get graphic card prices/stock
require_once dirname(__FILE__) . '/../repositories/OrderItemRepository.php'; // For creating order items

use App\Repositories\OrderRepository;
use App\Repositories\GraphicCardRepository;
use App\Repositories\OrderItemRepository; // Added for explicit use
use App\Models\Order;
use App\Models\OrderItem;

class OrderService {
    private $orderRepository;
    private $graphicCardRepository; // Needed for price/stock checks
    private $orderItemRepository; // Added

    /**
     * Constructor for OrderService.
     * Initializes the OrderRepository and GraphicCardRepository.
     */
    public function __construct() {
        $this->orderRepository = new OrderRepository();
        $this->graphicCardRepository = new GraphicCardRepository(); // For checking graphic card details
        $this->orderItemRepository = new OrderItemRepository(); // Initialize
    }

    /**
     * Retrieves all orders.
     * @return array An array of Order model instances.
     */
    public function getAllOrders() {
        $ordersData = $this->orderRepository->getAll();
        $orders = [];
        foreach ($ordersData as $data) {
            $orders[] = new Order($data);
        }
        return $orders;
    }

    /**
     * Retrieves a single order by ID, including its items.
     * @param int $id
     * @return array|false Returns an associative array of Order model instance and its OrderItem models if found, false otherwise.
     */
    public function getOrderById(int $id) {
        $orderData = $this->orderRepository->getById($id);
        if ($orderData) {
            $order = new Order($orderData);
            // Re-map items from raw array to OrderItem models if desired for consistency
            $items = [];
            foreach ($orderData['items'] as $itemData) {
                $items[] = new OrderItem($itemData);
            }
            return ['order' => $order, 'items' => $items];
        }
        return false;
    }

    /**
     * Creates a new order.
     *
     * @param int $userId The ID of the user placing the order.
     * @param array $items An array of associative arrays, each with 'graphic_card_id' and 'quantity'.
     * @return Order|false Returns the created Order model instance on success, false on failure.
     */
    public function createOrder(int $userId, array $items) {
        if (empty($userId) || empty($items)) {
            return false; // User ID and items are required
        }

        $totalAmount = 0;
        $orderItemsDataForCreation = []; // Data specifically for order_items table

        // Validate items, calculate total amount, and prepare order items data
        foreach ($items as $item) {
            if (empty($item['graphic_card_id']) || empty($item['quantity']) || !is_numeric($item['quantity']) || $item['quantity'] <= 0) {
                return false; // Invalid item data
            }

            $graphicCard = $this->graphicCardRepository->getById($item['graphic_card_id']);
            if (!$graphicCard || $graphicCard['stock'] < $item['quantity']) {
                // Graphic card not found or insufficient stock
                return false;
            }

            $itemPrice = (float)$graphicCard['price'];
            $totalAmount += $itemPrice * $item['quantity'];

            $orderItemsDataForCreation[] = [
                'graphic_card_id' => (int)$item['graphic_card_id'],
                'quantity' => (int)$item['quantity'],
                'price_at_purchase' => $itemPrice // Store the price at the time of purchase
            ];
            // In a real application, you would also decrement the stock here using the GraphicCardRepository
            // e.g., $this->graphicCardRepository->decrementStock($item['graphic_card_id'], $item['quantity']);
        }

        $orderData = [
            'user_id' => $userId,
            'total_amount' => $totalAmount,
            'status' => 'pending', // Default status
            // The 'items' array passed to orderRepository->create is handled within that method's transaction
            'items' => $orderItemsDataForCreation
        ];

        // This call to orderRepository->create will handle both the order and its items in a transaction
        $newOrderId = $this->orderRepository->create($orderData);

        if ($newOrderId) {
            $createdOrderData = $this->orderRepository->getById($newOrderId);
            if ($createdOrderData) {
                $order = new Order($createdOrderData);
                // Attach order items as models to the order object for a complete return
                $order->items = [];
                foreach ($createdOrderData['items'] as $itemData) {
                    $order->items[] = new OrderItem($itemData);
                }
                return $order;
            }
        }
        return false;
    }

    /**
     * Updates the status or total amount of an existing order.
     * @param int $id The order ID.
     * @param array $data Associative array containing fields to update (e.g., 'status', 'total_amount').
     * @return bool True on success, false on failure.
     */
    public function updateOrder(int $id, array $data) {
        // Fetch current order to validate updates
        $currentOrder = $this->orderRepository->getById($id);
        if (!$currentOrder) {
            return false; // Order not found
        }

        // Merge existing data with new data, prioritizing new data
        // Only update specific fields allowed for update (e.g., status, total_amount)
        $updateFields = [];
        if (isset($data['user_id'])) $updateFields['user_id'] = $data['user_id'];
        if (isset($data['total_amount'])) $updateFields['total_amount'] = $data['total_amount'];
        if (isset($data['status'])) {
            // Basic validation for status if provided
            if (!in_array($data['status'], ['pending', 'processing', 'shipped', 'completed', 'cancelled'])) {
                return false; // Invalid status
            }
            $updateFields['status'] = $data['status'];
        }

        if (empty($updateFields)) {
            return false; // No valid fields to update
        }

        return $this->orderRepository->update($id, $updateFields);
    }

    /**
     * Deletes an order.
     * @param int $id The order ID.
     * @return bool True on success, false on failure.
     */
    public function deleteOrder(int $id) {
        return $this->orderRepository->delete($id);
    }
}
?>
