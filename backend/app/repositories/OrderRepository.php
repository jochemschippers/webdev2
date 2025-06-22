<?php
// app/repositories/OrderRepository.php

namespace App\Repositories;

use \PDO;
use \PDOException;
use App\Models\Order; // Import the Order model
use App\Models\OrderItem; // Import the OrderItem model
use App\Models\User; // Import the User model

require_once dirname(__FILE__) . '/../models/Order.php';
require_once dirname(__FILE__) . '/../models/OrderItem.php';
require_once dirname(__FILE__) . '/../models/User.php'; // Require User model for getUserById
require_once __DIR__ . '/Repository.php';

class OrderRepository extends Repository {
    public function __construct() {
        parent::__construct();
    }

    public function beginTransaction(): bool {
        return $this->connection->beginTransaction();
    }

    public function commit(): bool {
        return $this->connection->commit();
    }

    public function rollBack(): bool {
        return $this->connection->rollBack();
    }
    public function getAll(?int $userId = null) {
        $query = "SELECT o.id, o.user_id, o.total_amount, o.status, o.order_date, o.updated_at, u.username
                  FROM orders o
                  JOIN users u ON o.user_id = u.id";

        $conditions = [];
        $params = [];

        if ($userId !== null) {
            $conditions[] = "o.user_id = :user_id";
            $params[':user_id'] = $userId;
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $query .= " ORDER BY o.order_date DESC";

        $stmt = $this->connection->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT o.id, o.user_id, o.total_amount, o.status, o.order_date, o.updated_at, u.username
                  FROM orders o
                  JOIN users u ON o.user_id = u.id
                  WHERE o.id = ? LIMIT 0,1";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOrderItemsByOrderId(int $orderId) {
        $query = "SELECT oi.id, oi.order_id, oi.graphic_card_id, oi.quantity, oi.price_at_purchase, gc.name AS graphic_card_name
                  FROM order_items oi
                  JOIN graphic_cards gc ON oi.graphic_card_id = gc.id
                  WHERE oi.order_id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $orderId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        // Ensure status is set, default to 'pending'
        $status = $data['status'] ?? 'pending';

        $query = "INSERT INTO orders (user_id, total_amount, status)
                  VALUES (:user_id, :total_amount, :status)";
        $stmt = $this->connection->prepare($query);

        // Bind parameters
        $userId = $data['user_id'];
        $totalAmount = $data['total_amount'];

        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":total_amount", $totalAmount); // PDO handles FLOAT/DECIMAL
        $stmt->bindParam(":status", $status, PDO::PARAM_STR);

        try {
            $success = $stmt->execute();
            if ($success) {
                return (int)$this->connection->lastInsertId();
            } else {
                error_log("OrderRepository: Failed to execute order creation statement. ErrorInfo: " . print_r($stmt->errorInfo(), true));
                return false;
            }
        } catch (PDOException $e) {
            error_log("OrderRepository: PDOException during order creation: " . $e->getMessage());
            return false;
        }
    }

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
        if (isset($data['status'])) { // Allow updating status
            $setParts[] = 'status = :status';
            $params[':status'] = $data['status'];
        }
        // updated_at is automatically updated by the database TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

        if (empty($setParts)) {
            error_log("OrderRepository: No valid fields to update for order ID: " . $id);
            return false;
        }

        $query = "UPDATE orders SET " . implode(', ', $setParts) . " WHERE id = :id";
        $stmt = $this->connection->prepare($query);

        try {
            foreach ($params as $key => $value) {
                // Determine PDO parameter type
                if ($key === ':user_id') {
                    $type = PDO::PARAM_INT;
                } elseif ($key === ':total_amount') {
                    $type = PDO::PARAM_STR; // PDO handles FLOAT/DECIMAL types well with STR
                } elseif ($key === ':status') {
                    $type = PDO::PARAM_STR;
                } else {
                    $type = PDO::PARAM_STR;
                }
                $stmt->bindValue($key, $value, $type);
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

    public function delete($id) {
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

    public function getUserById(int $userId) {
        $query = "SELECT id, username, email FROM users WHERE id = ? LIMIT 0,1";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            error_log("OrderRepository: User found for ID {$userId}: " . $user['username']);
        } else {
            error_log("OrderRepository: User not found for ID: {$userId}");
        }
        return $user;
    }
}
