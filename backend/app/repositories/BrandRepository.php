<?php
// app/repositories/BrandRepository.php

namespace App\Repositories;

use \PDO; // Import PDO from the global namespace
use \PDOException; // Import PDOException from the global namespace

require_once dirname(__FILE__) . '/../models/Brand.php';
require_once dirname(__FILE__) . '/../models/Manufacturer.php'; // Also required for join logic
require_once __DIR__ . '/Repository.php';

class BrandRepository extends Repository {
    /**
     * Constructor for BrandRepository.
     * Calls the parent constructor to establish the database connection.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Retrieves all brands, with manufacturer name, directly from the database.
     *
     * @return array An array of associative arrays, where each inner array represents a brand.
     * Returns an empty array if no brands are found.
     */
    public function getAll() {
        $query = "SELECT b.id, b.name, b.manufacturer_id, m.name as manufacturer_name
                  FROM brands b
                  LEFT JOIN manufacturers m ON b.manufacturer_id = m.id
                  ORDER BY b.name ASC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves a single brand by ID, with manufacturer name, directly from the database.
     *
     * @param int $id The ID of the brand to retrieve.
     * @return array|false Returns an associative array of brand data if found, false otherwise.
     */
    public function getById($id) {
        $query = "SELECT b.id, b.name, b.manufacturer_id, m.name as manufacturer_name
                  FROM brands b
                  LEFT JOIN manufacturers m ON b.manufacturer_id = m.id
                  WHERE b.id = ? LIMIT 0,1";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Creates a new brand in the database.
     * @param array $data Associative array with 'name' and 'manufacturer_id'.
     * @return int|false Returns the ID of the newly created brand on success, false on failure.
     */
    public function create(array $data) {
        $query = "INSERT INTO brands (name, manufacturer_id) VALUES (:name, :manufacturer_id)";
        $stmt = $this->connection->prepare($query);

        $name = htmlspecialchars(strip_tags($data['name']));
        $manufacturer_id = $data['manufacturer_id'];

        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":manufacturer_id", $manufacturer_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return (int)$this->connection->lastInsertId();
        }
        return false;
    }

    /**
     * Updates an existing brand.
     * @param int $id The ID of the brand to update.
     * @param array $data Associative array with 'name' and 'manufacturer_id'.
     * @return bool True on success, false on failure.
     */
    public function update($id, array $data) {
        $query = "UPDATE brands SET name = :name, manufacturer_id = :manufacturer_id WHERE id = :id";
        $stmt = $this->connection->prepare($query);

        $name = htmlspecialchars(strip_tags($data['name']));
        $manufacturer_id = $data['manufacturer_id'];

        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":manufacturer_id", $manufacturer_id, PDO::PARAM_INT);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Deletes a brand.
     * @param int $id The ID of the brand to delete.
     * @return bool True on success, false on failure.
     */
    public function delete($id) {
        $query = "DELETE FROM brands WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
