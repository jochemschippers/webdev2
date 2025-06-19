<?php
// app/models/Manufacturer.php

class Manufacturer {
    private $conn;
    private $table_name = "manufacturers";

    public $id;
    public $name;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Reads all manufacturers.
     * @return PDOStatement
     */
    public function readAll() {
        $query = "SELECT id, name FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Reads one manufacturer by ID.
     * @param int $id
     * @return bool True if manufacturer found, false otherwise.
     */
    public function readOne($id) {
        $query = "SELECT id, name FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            return true;
        }
        return false;
    }
}
?>
