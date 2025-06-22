<?php
// app/repositories/OrderItemRepository.php

namespace App\Repositories;

use \PDO;
use \PDOException;

require_once dirname(__FILE__) . '/../models/OrderItem.php';
require_once dirname(__FILE__) . '/../models/GraphicCard.php'; // For join info in getAllByOrderId
require_once __DIR__ . '/Repository.php';

class OrderItemRepository extends Repository {
    public function __construct() {
        parent::__construct();
    }

    public function getAllByOrderId($order_id) {
        $query = "SELECT oi.id, oi.order_id, oi.graphic_card_id, oi.quantity, oi.price_at_purchase, gc.name as graphic_card_name
                  FROM order_items oi
                  LEFT JOIN graphic_cards gc ON oi.graphic_card_id = gc.id
                  WHERE oi.order_id = ?
                  ORDER BY oi.id ASC";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $order_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Creates a new order item in the database.
     * @param array $data Order item data (order_id, graphic_card_id, quantity, price_at_purchase).
     * @return int|false Returns the ID of the newly created order item on success, false on failure.
     */
    public function create(array $data) {
        error_log("OrderItemRepository: create method called with data: " . print_r($data, true));

        if (!isset($data['order_id']) || !isset($data['graphic_card_id']) || !isset($data['quantity']) || !isset($data['price_at_purchase'])) {
            error_log("OrderItemRepository: ERROR: Missing required data for creating an order item.");
            return false;
        }

        $query = "INSERT INTO order_items (order_id, graphic_card_id, quantity, price_at_purchase) VALUES (:order_id, :graphic_card_id, :quantity, :price_at_purchase)";
        
        try {
            $stmt = $this->connection->prepare($query);
            error_log("OrderItemRepository: DEBUG: Prepared statement for order item creation.");

            $order_id = (int)$data['order_id'];
            $graphic_card_id = (int)$data['graphic_card_id'];
            $quantity = (int)$data['quantity'];
            $price_at_purchase = (float)$data['price_at_purchase'];

            $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);
            $stmt->bindParam(":graphic_card_id", $graphic_card_id, PDO::PARAM_INT);
            $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
            
            // It's often safer to bind DECIMAL/FLOAT values as strings to prevent precision loss
            $price_str = sprintf("%.2f", $price_at_purchase); // Format to 2 decimal places
            $stmt->bindParam(":price_at_purchase", $price_str, PDO::PARAM_STR);
            error_log("OrderItemRepository: DEBUG: Parameters bound for order_id: {$order_id}, graphic_card_id: {$graphic_card_id}, quantity: {$quantity}, price_at_purchase (as string): {$price_str}.");

            $success = $stmt->execute();
            error_log("OrderItemRepository: DEBUG: Statement execute() returned: " . ($success ? 'TRUE' : 'FALSE'));

            if ($success) {
                $lastInsertId = (int)$this->connection->lastInsertId();
                if ($lastInsertId > 0) {
                    error_log("OrderItemRepository: SUCCESS: Order item created successfully with ID: " . $lastInsertId);
                    return $lastInsertId;
                } else {
                    error_log("OrderItemRepository: WARNING: Order item created, but lastInsertId was 0. Check table auto-increment or primary key status.");
                    // Depending on your schema, 0 might still indicate success for some DBs, but generally implies an issue.
                    // If it's a valid insert but lastInsertId is 0, you might need to manually fetch the record.
                    return true; // Consider returning true if you believe it inserted, but ID fetch failed.
                }
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("OrderItemRepository: ERROR: Failed to execute order item creation statement. PDO ErrorInfo: " . print_r($errorInfo, true));
                return false;
            }
        } catch (PDOException $e) {
            error_log("OrderItemRepository: CATCHED PDOException during order item creation: " . $e->getMessage());
            error_log("OrderItemRepository: PDOException Error Code: " . $e->getCode());
            error_log("OrderItemRepository: PDOException Error Info: " . print_r($e->errorInfo, true));
            return false;
        } catch (\Exception $e) {
            error_log("OrderItemRepository: CATCHED General Exception during order item creation: " . $e->getMessage());
            error_log("OrderItemRepository: General Exception Code: " . $e->getCode());
            return false;
        }
    }
}
