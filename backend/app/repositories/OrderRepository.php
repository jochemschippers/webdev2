<?php
// app/repositories/OrderRepository.php

namespace App\Repositories;

use \PDO;
use \PDOException;
use \Exception; // Make sure to use Exception class for try-catch blocks

require_once dirname(__FILE__) . '/../models/Order.php';
require_once dirname(__FILE__) . '/../models/OrderItem.php';
require_once __DIR__ . '/Repository.php';

class OrderRepository extends Repository {
    /**
     * Constructor for OrderRepository.
     * Calls the parent constructor to establish the database connection.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Retrieves all orders directly from the database, with joined username.
     *
     * @return array An array of associative arrays, where each inner array represents an order.
     * Returns an empty array if no orders are found.
     */
    public function getAll() {
        $query = "SELECT o.id, o.user_id, o.total_amount, o.status, o.order_date, o.updated_at, u.username
                  FROM orders o
                  LEFT JOIN users u ON o.user_id = u.id
                  ORDER BY o.order_date DESC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves a single order by ID, including its items, directly from the database.
     *
     * @param int $id The ID of the order to retrieve.
     * @return array|false Returns an associative array of order and its items if found, false otherwise.
     */
    public function getById($id) {
        // Fetch order details
        $orderQuery = "SELECT o.id, o.user_id, o.total_amount, o.status, o.order_date, o.updated_at, u.username
                       FROM orders o
                       LEFT JOIN users u ON o.user_id = u.id
                       WHERE o.id = ? LIMIT 0,1";
        $orderStmt = $this->connection->prepare($orderQuery);
        $orderStmt->bindParam(1, $id, PDO::PARAM_INT);
        $orderStmt->execute();
        $orderData = $orderStmt->fetch(PDO::FETCH_ASSOC);

        if (!$orderData) {
            return false; // Order not found
        }

        // Fetch order items
        $orderItemsQuery = "SELECT oi.id, oi.order_id, oi.graphic_card_id, oi.quantity, oi.price_at_purchase, gc.name as graphic_card_name
                            FROM order_items oi
                            LEFT JOIN graphic_cards gc ON oi.graphic_card_id = gc.id
                            WHERE oi.order_id = ?
                            ORDER BY oi.id ASC";
        $orderItemsStmt = $this->connection->prepare($orderItemsQuery);
        $orderItemsStmt->bindParam(1, $id, PDO::PARAM_INT);
        $orderItemsStmt->execute();
        $items = $orderItemsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Combine order data and items
        $orderData['items'] = $items;

        return $orderData;
    }

    /**
     * Creates a new order in the database.
     *
     * @param array $orderData Associative array with 'user_id', 'total_amount', 'status', and 'items' array.
     * @return int|false Returns the ID of the newly created order on success, false on failure.
     */
    public function create(array $orderData) {
        try {
            $this->connection->beginTransaction();

            $orderQuery = "INSERT INTO orders (user_id, total_amount, status) VALUES (:user_id, :total_amount, :status)";
            $orderStmt = $this->connection->prepare($orderQuery);

            // Sanitize data
            $user_id = $orderData['user_id'];
            $total_amount = $orderData['total_amount'];
            $status = htmlspecialchars(strip_tags($orderData['status'] ?? 'pending')); // Default status

            $orderStmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
            $orderStmt->bindParam(":total_amount", $total_amount);
            $orderStmt->bindParam(":status", $status);

            if ($orderStmt->execute()) {
                $orderId = $this->connection->lastInsertId();

                $orderItemQuery = "INSERT INTO order_items (order_id, graphic_card_id, quantity, price_at_purchase) VALUES (:order_id, :graphic_card_id, :quantity, :price_at_purchase)";
                $orderItemStmt = $this->connection->prepare($orderItemQuery);

                foreach ($orderData['items'] as $item) {
                    $graphic_card_id = $item['graphic_card_id'];
                    $quantity = $item['quantity'];
                    $price_at_purchase = $item['price_at_purchase']; // Use price at time of purchase

                    $orderItemStmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
                    $orderItemStmt->bindParam(":graphic_card_id", $graphic_card_id, PDO::PARAM_INT);
                    $orderItemStmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
                    $orderItemStmt->bindParam(":price_at_purchase", $price_at_purchase);

                    if (!$orderItemStmt->execute()) {
                        throw new Exception("Failed to create order item.");
                    }
                }
                $this->connection->commit();
                return (int)$orderId; // Return the ID of the newly created order
            } else {
                throw new Exception("Failed to create order.");
            }
        } catch (Exception $e) {
            $this->connection->rollBack();
            error_log("Order creation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates an existing order directly in the database.
     *
     * @param int $id The ID of the order to update.
     * @param array $data Associative array of order data to update (user_id, total_amount, status).
     * @return bool True on success, false on failure.
     */
    public function update($id, array $data) {
        $query = "UPDATE orders SET user_id = :user_id, total_amount = :total_amount, status = :status WHERE id = :id";
        $stmt = $this->connection->prepare($query);

        // Sanitize data
        $user_id = $data['user_id'];
        $total_amount = $data['total_amount'];
        $status = htmlspecialchars(strip_tags($data['status']));

        // Bind values
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":total_amount", $total_amount);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Deletes an order directly from the database.
     *
     * @param int $id The ID of the order to delete.
     * @return bool True on success, false on failure.
     */
    public function delete($id) {
        $query = "DELETE FROM orders WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
