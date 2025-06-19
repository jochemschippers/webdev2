<?php
// app/models/Order.php

class Order {
    private $conn;
    private $table_name = "orders";

    public $id;
    public $user_id;
    public $total_amount;
    public $status;
    public $order_date;
    public $updated_at;
    public $username; // For joining

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Reads all orders, optionally with username.
     * @return PDOStatement
     */
    public function readAll() {
        $query = "SELECT o.id, o.user_id, o.total_amount, o.status, o.order_date, u.username
                  FROM " . $this->table_name . " o
                  LEFT JOIN users u ON o.user_id = u.id
                  ORDER BY o.order_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Reads one order by ID, with username.
     * @param int $id
     * @return bool True if order found, false otherwise.
     */
    public function readOne($id) {
        $query = "SELECT o.id, o.user_id, o.total_amount, o.status, o.order_date, u.username
                  FROM " . $this->table_name . " o
                  LEFT JOIN users u ON o.user_id = u.id
                  WHERE o.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->total_amount = $row['total_amount'];
            $this->status = $row['status'];
            $this->order_date = $row['order_date'];
            $this->username = $row['username'];
            return true;
        }
        return false;
    }

    /**
     * Creates a new order.
     * @return bool True on success, false on failure.
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (user_id, total_amount, status) VALUES (:user_id, :total_amount, :status)";
        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->total_amount = htmlspecialchars(strip_tags($this->total_amount));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id, PDO::PARAM_INT);
        $stmt->bindParam(":total_amount", $this->total_amount);
        $stmt->bindParam(":status", $this->status);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Updates an existing order.
     * @return bool True on success, false on failure.
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET user_id = :user_id, total_amount = :total_amount, status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->total_amount = htmlspecialchars(strip_tags($this->total_amount));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id, PDO::PARAM_INT);
        $stmt->bindParam(":total_amount", $this->total_amount);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Deletes an order.
     * @param int $id The ID of the order to delete.
     * @return bool True on success, false on failure.
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
