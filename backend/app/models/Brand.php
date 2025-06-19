<?php
// app/models/Brand.php

class Brand {
    private $conn;
    private $table_name = "brands";

    public $id;
    public $name;
    public $manufacturer_id;
    public $manufacturer_name; // For joining
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Reads all brands, optionally with manufacturer name.
     * @return PDOStatement
     */
    public function readAll() {
        $query = "SELECT b.id, b.name, b.manufacturer_id, m.name as manufacturer_name
                  FROM " . $this->table_name . " b
                  LEFT JOIN manufacturers m ON b.manufacturer_id = m.id
                  ORDER BY b.name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Reads one brand by ID, with manufacturer name.
     * @param int $id
     * @return bool True if brand found, false otherwise.
     */
    public function readOne($id) {
        $query = "SELECT b.id, b.name, b.manufacturer_id, m.name as manufacturer_name
                  FROM " . $this->table_name . " b
                  LEFT JOIN manufacturers m ON b.manufacturer_id = m.id
                  WHERE b.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->manufacturer_id = $row['manufacturer_id'];
            $this->manufacturer_name = $row['manufacturer_name'];
            return true;
        }
        return false;
    }
}
?>
