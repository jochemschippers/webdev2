<?php
// app/models/OrderItem.php

class OrderItem {
    private $conn;
    private $table_name = "order_items";

    public $id;
    public $order_id;
    public $graphic_card_id;
    public $quantity;
    public $price_at_purchase;
    public $graphic_card_name; // For joining

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Reads all order items for a specific order, with graphic card name.
     * @param int $order_id
     * @return PDOStatement
     */
    public function readAllByOrderId($order_id) {
        $query = "SELECT oi.id, oi.order_id, oi.graphic_card_id, oi.quantity, oi.price_at_purchase, gc.name as graphic_card_name
                  FROM " . $this->table_name . " oi
                  LEFT JOIN graphic_cards gc ON oi.graphic_card_id = gc.id
                  WHERE oi.order_id = ?
                  ORDER BY oi.id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $order_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Creates a new order item.
     * @return bool True on success, false on failure.
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (order_id, graphic_card_id, quantity, price_at_purchase) VALUES (:order_id, :graphic_card_id, :quantity, :price_at_purchase)";
        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->price_at_purchase = htmlspecialchars(strip_tags($this->price_at_purchase));

        // Bind values
        $stmt->bindParam(":order_id", $this->order_id, PDO::PARAM_INT);
        $stmt->bindParam(":graphic_card_id", $this->graphic_card_id, PDO::PARAM_INT);
        $stmt->bindParam(":quantity", $this->quantity, PDO::PARAM_INT);
        $stmt->bindParam(":price_at_purchase", $this->price_at_purchase);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }
}
?>
