<?php
// app/repositories/GraphicCardRepository.php

class GraphicCardRepository {

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Reads all manufacturers.
     * @return PDOStatement
     */
    public function readAll() {
        $query = "SELECT * FROM manufacturers ";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Reads one manufacturer by ID.
     * @param int $id
     * @return bool True if manufacturer found, false otherwise.
     */
    public function readOne($id) {
        $query = "SELECT id, name FROM manufacturers WHERE id = ?";
        $stmt = $this->connection->prepare($query);
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
