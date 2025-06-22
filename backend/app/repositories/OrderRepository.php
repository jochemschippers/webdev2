<?php
// app/repositories/OrderRepository.php

namespace App\Repositories;

use \PDO;
use \PDOException;

require_once dirname(__FILE__) . '/../models/Order.php';
require_once dirname(__FILE__) . '/../models/User.php'; // Required for join info
require_once __DIR__ . '/Repository.php'; // Require the base Repository class

class OrderRepository extends Repository {
    /**
     * Constructor for OrderRepository.
     * Calls the parent constructor to establish the database connection.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Retrieves all orders from the database, optionally filtered by user ID.
     * Includes username from the users table.
     * @param int|null $userId Optional user ID to filter orders.
     * @return array An array of associative arrays, where each inner array represents an order.
     * Returns an empty array if no orders are found.
     */
    public function getAll(?int $userId = null) {
        $query = "SELECT o.id, o.user_id, o.total_amount, o.status, o.order_date, o.updated_at, u.username
                  FROM orders o
                  LEFT JOIN users u ON o.user_id = u.id";
        $params = [];

        if ($userId !== null) {
            $query .= " WHERE o.user_id = ?";
            $params[] = $userId;
        }

        $query .= " ORDER BY o.order_date DESC"; // Order by most recent first

        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves a single order by ID from the database, including username.
     *
     * @param int $id The ID of the order to retrieve.
     * @return array|false Returns an associative array of order data if found, false otherwise.
     */
    public function getById($id) {
        $query = "SELECT o.id, o.user_id, o.total_amount, o.status, o.order_date, o.updated_at, u.username
                  FROM orders o
                  LEFT JOIN users u ON o.user_id = u.id
                  WHERE o.id = ? LIMIT 0,1";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function create(array $data) {
        error_log("OrderRepository: create method called with data: " . print_r($data, true)); // DEBUG

        // Ensure these keys exist and are of appropriate types
        if (!isset($data['user_id']) || !isset($data['total_amount']) || !isset($data['status'])) {
            error_log("OrderRepository: Missing required data for creating an order.");
            return false;
        }

        $query = "INSERT INTO orders (user_id, total_amount, status) VALUES (:user_id, :total_amount, :status)";
        $stmt = $this->connection->prepare($query);

        // Sanitize and bind values
        $userId = $data['user_id'];
        $totalAmount = $data['total_amount'];
        $status = htmlspecialchars(strip_tags($data['status']));

        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":total_amount", $totalAmount); // PDO will handle DECIMAL/FLOAT
        $stmt->bindParam(":status", $status, PDO::PARAM_STR);

        try {
            $success = $stmt->execute();
            if ($success) {
                $lastInsertId = (int)$this->connection->lastInsertId();
                error_log("OrderRepository: Order record created successfully with ID: " . $lastInsertId); // DEBUG
                return $lastInsertId;
            } else {
                error_log("OrderRepository: Failed to execute order creation statement. ErrorInfo: " . print_r($stmt->errorInfo(), true)); // DEBUG
                return false;
            }
        } catch (PDOException $e) {
            error_log("OrderRepository: PDOException during order creation: " . $e->getMessage()); // DEBUG
            return false;
        }
    }

    /**
     * Updates an existing order.
     * @param int $id The ID of the order to update.
     * @param array $data Associative array of order properties to update.
     * @return bool True on success, false on failure.
     */
    public function update($id, array $data) {
        $setParts = [];
        $params = [':id' => $id];

        if (isset($data['user_id'])) {
            $setParts[] = 'user_id = :user_id';
            $params[':user_id'] = $data['user_id'];
        }
        if (isset($data['total_amount'])) {
            $setParts[] = 'total_amount = :total_amount';
            $params[':total_amount'] = $data['total_amount'];
        }
        if (isset($data['status'])) {
            $setParts[] = 'status = :status';
            $params[':status'] = htmlspecialchars(strip_tags($data['status']));
        }

        if (empty($setParts)) {
            error_log("OrderRepository: No valid fields to update for order ID: " . $id);
            return false;
        }

        $query = "UPDATE orders SET " . implode(', ', $setParts) . " WHERE id = :id";
        $stmt = $this->connection->prepare($query);

        try {
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $success = $stmt->execute();
            if ($success && $stmt->rowCount() > 0) {
                error_log("OrderRepository: Order ID " . $id . " updated successfully.");
                return true;
            } else {
                error_log("OrderRepository: Failed to update order ID " . $id . ". No rows affected or error. ErrorInfo: " . print_r($stmt->errorInfo(), true));
                return false;
            }
        } catch (PDOException $e) {
            error_log("OrderRepository: PDOException during order update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes an order from the database.
     * @param int $id The ID of the order to delete.
     * @return bool True on success, false on failure.
     */
    public function delete($id) {
        // Deleting an order will typically cascade delete order_items due to foreign key ON DELETE CASCADE.
        $query = "DELETE FROM orders WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);

        try {
            $success = $stmt->execute();
            if ($success && $stmt->rowCount() > 0) {
                error_log("OrderRepository: Order ID " . $id . " deleted successfully. Rows affected: " . $stmt->rowCount());
                return true;
            } else {
                error_log("OrderRepository: Failed to delete order ID " . $id . ". No rows affected or error. ErrorInfo: " . print_r($stmt->errorInfo(), true));
                return false;
            }
        } catch (PDOException $e) {
            error_log("OrderRepository: PDOException during order deletion: " . $e->getMessage());
            return false;
        }
    }
}
