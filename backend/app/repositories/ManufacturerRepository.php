<?php
// app/repositories/ManufacturerRepository.php

namespace App\Repositories;

use \PDO;
use \PDOException;

require_once dirname(__FILE__) . '/../models/Manufacturer.php';
require_once __DIR__ . '/Repository.php';

class ManufacturerRepository extends Repository {
    public function __construct() {
        parent::__construct();
    }
    public function getAll() {
        $query = "SELECT id, name FROM manufacturers ORDER BY name ASC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT id, name FROM manufacturers WHERE id = ? LIMIT 0,1";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $query = "INSERT INTO manufacturers (name) VALUES (:name)";
        $stmt = $this->connection->prepare($query);

        $name = htmlspecialchars(strip_tags($data['name']));
        $stmt->bindParam(":name", $name);

        if ($stmt->execute()) {
            return (int)$this->connection->lastInsertId();
        }
        return false;
    }


    public function update($id, array $data) {
        $query = "UPDATE manufacturers SET name = :name WHERE id = :id";
        $stmt = $this->connection->prepare($query);

        $name = htmlspecialchars(strip_tags($data['name']));
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($id) {
        $query = "DELETE FROM manufacturers WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
