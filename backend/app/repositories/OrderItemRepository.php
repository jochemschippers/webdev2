<?php
// app/repositories/OrderItemRepository.php

namespace App\Repositories; // Use the same namespace as the base Repository

require_once dirname(__FILE__) . '/../models/OrderItem.php'; // Model for data structure
require_once dirname(__FILE__) . '/../models/GraphicCard.php'; // For join info
require_once __DIR__ . '/Repository.php'; // Require the base Repository class

class OrderItemRepository extends Repository {
    // The $this->connection is now inherited from the base Repository

    public function __construct() {
        parent::__construct(); // Call the parent constructor to establish connection
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
