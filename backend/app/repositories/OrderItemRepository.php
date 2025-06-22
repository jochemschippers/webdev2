<?php
// app/repositories/OrderItemRepository.php

namespace App\Repositories;

use \PDO;
use \PDOException;

require_once dirname(__FILE__) . '/../models/OrderItem.php';
require_once __DIR__ . '/Repository.php'; // Require the base Repository class

class OrderItemRepository extends Repository {
    public function __construct(PDO $existingConnection = null) {
        parent::__construct($existingConnection);
    }

    public function getAllByOrderId(int $orderId) {
        $query = "SELECT id, order_id, graphic_card_id, quantity, price_at_purchase
                  FROM order_items
                  WHERE order_id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $orderId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        error_log("OrderItemRepository: create method called with data: " . print_r($data, true)); // DEBUG

        // Ensure all required keys exist and are of appropriate types
        if (!isset($data['order_id']) || !isset($data['graphic_card_id']) ||
            !isset($data['quantity']) || !isset($data['price_at_purchase'])) {
            error_log("OrderItemRepository: Missing required data for creating an order item.");
            return false;
        }

        $query = "INSERT INTO order_items (order_id, graphic_card_id, quantity, price_at_purchase)
                  VALUES (:order_id, :graphic_card_id, :quantity, :price_at_purchase)";
        $stmt = $this->connection->prepare($query);

        // Sanitize and bind values
        $orderId = $data['order_id'];
        $graphicCardId = $data['graphic_card_id'];
        $quantity = $data['quantity'];
        $priceAtPurchase = $data['price_at_purchase'];

        $stmt->bindParam(":order_id", $orderId, PDO::PARAM_INT);
        $stmt->bindParam(":graphic_card_id", $graphicCardId, PDO::PARAM_INT);
        $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
        $stmt->bindParam(":price_at_purchase", $priceAtPurchase); // PDO handles FLOAT/DECIMAL

        try {
            $success = $stmt->execute();
            if ($success) {
                $lastInsertId = (int)$this->connection->lastInsertId();
                error_log("OrderItemRepository: Order item created successfully with ID: " . $lastInsertId . " for order ID: " . $orderId); // DEBUG
                return $lastInsertId;
            } else {
                error_log("OrderItemRepository: Failed to execute order item creation statement. ErrorInfo: " . print_r($stmt->errorInfo(), true)); // DEBUG
                return false;
            }
        } catch (PDOException $e) {
            error_log("OrderItemRepository: PDOException during order item creation: " . $e->getMessage()); // DEBUG
            // In case of an exception, log the full error and return false
            return false;
        }
    }
    public function update($id, array $data) {
        $setParts = [];
        $params = [':id' => $id];

        if (isset($data['order_id'])) {
            $setParts[] = 'order_id = :order_id';
            $params[':order_id'] = $data['order_id'];
        }
        if (isset($data['graphic_card_id'])) {
            $setParts[] = 'graphic_card_id = :graphic_card_id';
            $params[':graphic_card_id'] = $data['graphic_card_id'];
        }
        if (isset($data['quantity'])) {
            $setParts[] = 'quantity = :quantity';
            $params[':quantity'] = $data['quantity'];
        }
        if (isset($data['price_at_purchase'])) {
            $setParts[] = 'price_at_purchase = :price_at_purchase';
            $params[':price_at_purchase'] = $data['price_at_purchase'];
        }

        if (empty($setParts)) {
            error_log("OrderItemRepository: No valid fields to update for order item ID: " . $id);
            return false;
        }

        $query = "UPDATE order_items SET " . implode(', ', $setParts) . " WHERE id = :id";
        $stmt = $this->connection->prepare($query);

        try {
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $success = $stmt->execute();
            if ($success && $stmt->rowCount() > 0) {
                error_log("OrderItemRepository: Order item ID " . $id . " updated successfully.");
                return true;
            } else {
                error_log("OrderItemRepository: Failed to update order item ID " . $id . ". No rows affected or error. ErrorInfo: " . print_r($stmt->errorInfo(), true));
                return false;
            }
        } catch (PDOException $e) {
            error_log("OrderItemRepository: PDOException during order item update: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        $query = "DELETE FROM order_items WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);

        try {
            $success = $stmt->execute();
            if ($success && $stmt->rowCount() > 0) {
                error_log("OrderItemRepository: Order item ID " . $id . " deleted successfully. Rows affected: " . $stmt->rowCount());
                return true;
            } else {
                error_log("OrderItemRepository: Failed to delete order item ID " . $id . ". No rows affected or error. ErrorInfo: " . print_r($stmt->errorInfo(), true));
                return false;
            }
        } catch (PDOException $e) {
            error_log("OrderItemRepository: PDOException during order item deletion: " . $e->getMessage());
            return false;
        }
    }
}
