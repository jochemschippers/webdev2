<?php
// app/repositories/ManufacturerRepository.php

namespace App\Repositories;

use \PDO;
use \PDOException;

require_once dirname(__FILE__) . '/../models/Manufacturer.php';
require_once __DIR__ . '/Repository.php';

class ManufacturerRepository extends Repository {
    /**
     * Constructor for ManufacturerRepository.
     * Calls the parent constructor to establish the database connection.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Retrieves all manufacturers directly from the database.
     *
     * @return array An array of associative arrays, where each inner array represents a manufacturer.
     * Returns an empty array if no manufacturers are found.
     */
    public function getAll() {
        $query = "SELECT id, name FROM manufacturers ORDER BY name ASC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves a single manufacturer by ID directly from the database.
     *
     * @param int $id The ID of the manufacturer to retrieve.
     * @return array|false Returns an associative array of manufacturer data if found, false otherwise.
     */
    public function getById($id) {
        $query = "SELECT id, name FROM manufacturers WHERE id = ? LIMIT 0,1";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Creates a new manufacturer in the database.
     * @param array $data Associative array with 'name'.
     * @return int|false Returns the ID of the newly created manufacturer on success, false on failure.
     */
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

    /**
     * Updates an existing manufacturer.
     * @param int $id The ID of the manufacturer to update.
     * @param array $data Associative array with 'name'.
     * @return bool True on success, false on failure.
     */
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

    /**
     * Deletes a manufacturer.
     * @param int $id The ID of the manufacturer to delete.
     * @return bool True on success, false on failure.
     */
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
