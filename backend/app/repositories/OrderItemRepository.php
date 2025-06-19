<?php
// app/repositories/OrderItemRepository.php

namespace App\Repositories;

use \PDO; // Import PDO from the global namespace
use \PDOException; // Import PDOException from the global namespace

require_once dirname(__FILE__) . '/../models/OrderItem.php';
require_once dirname(__FILE__) . '/../models/GraphicCard.php'; // For join info
require_once __DIR__ . '/Repository.php';

class OrderItemRepository extends Repository {
    /**
     * Constructor for OrderItemRepository.
     * Calls the parent constructor to establish the database connection.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Reads all order items for a specific order, with graphic card name.
     * @param int $order_id
     * @return array An array of associative arrays of order item data.
     */
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
        $query = "INSERT INTO order_items (order_id, graphic_card_id, quantity, price_at_purchase) VALUES (:order_id, :graphic_card_id, :quantity, :price_at_purchase)";
        $stmt = $this->connection->prepare($query);

        // Sanitize data
        $order_id = $data['order_id'];
        $graphic_card_id = $data['graphic_card_id'];
        $quantity = $data['quantity'];
        $price_at_purchase = $data['price_at_purchase'];

        // Bind values
        $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT);
        $stmt->bindParam(":graphic_card_id", $graphic_card_id, PDO::PARAM_INT);
        $stmt->bindParam(":quantity", $quantity, PDO::PARAM_INT);
        $stmt->bindParam(":price_at_purchase", $price_at_purchase);

        if ($stmt->execute()) {
            return (int)$this->connection->lastInsertId();
        }
        return false;
    }

    // You can add update/delete for order items if needed, but often handled via parent order
}
?>
