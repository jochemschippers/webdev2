<?php
// app/repositories/GraphicCardRepository.php

namespace App\Repositories;

use \PDO;
use \PDOException;

require_once dirname(__FILE__) . '/../models/GraphicCard.php';
require_once dirname(__FILE__) . '/../models/Brand.php'; // For join info
require_once dirname(__FILE__) . '/../models/Manufacturer.php'; // For join info
require_once __DIR__ . '/Repository.php';

class GraphicCardRepository extends Repository
{
    /**
     * Constructor for GraphicCardRepository.
     * Calls the parent constructor to establish the database connection.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retrieves all graphic cards from the database, including brand and manufacturer names.
     *
     * @return array An array of associative arrays, where each inner array represents a graphic card.
     * Returns an empty array if no graphic cards are found.
     */
    public function getAll()
    {
        $query = "SELECT gc.id, gc.name, gc.brand_id, gc.gpu_model, gc.vram_gb, gc.interface,
                         gc.boost_clock_mhz, gc.cuda_cores, gc.stream_processors, gc.price,
                         gc.stock, gc.description, gc.image_url,
                         b.name as brand_name, m.name as manufacturer_name
                  FROM graphic_cards gc
                  LEFT JOIN brands b ON gc.brand_id = b.id
                  LEFT JOIN manufacturers m ON b.manufacturer_id = m.id
                  ORDER BY gc.name ASC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves a single graphic card by ID, including brand and manufacturer names.
     *
     * @param int $id The ID of the graphic card to retrieve.
     * @return array|false Returns an associative array of graphic card data if found, false otherwise.
     */
    public function getById(int $id)
    {
        $query = "SELECT gc.id, gc.name, gc.brand_id, gc.gpu_model, gc.vram_gb, gc.interface,
                         gc.boost_clock_mhz, gc.cuda_cores, gc.stream_processors, gc.price,
                         gc.stock, gc.description, gc.image_url,
                         b.name as brand_name, m.name as manufacturer_name
                  FROM graphic_cards gc
                  LEFT JOIN brands b ON gc.brand_id = b.id
                  LEFT JOIN manufacturers m ON b.manufacturer_id = m.id
                  WHERE gc.id = ? LIMIT 0,1";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Creates a new graphic card in the database.
     *
     * @param array $data Associative array of graphic card data.
     * @return int|false Returns the ID of the newly created graphic card on success, false on failure.
     */
    public function create(array $data)
    {
        $query = "INSERT INTO graphic_cards (name, brand_id, gpu_model, vram_gb, interface,
                                            boost_clock_mhz, cuda_cores, stream_processors,
                                            price, stock, description, image_url)
                  VALUES (:name, :brand_id, :gpu_model, :vram_gb, :interface,
                          :boost_clock_mhz, :cuda_cores, :stream_processors,
                          :price, :stock, :description, :image_url)";
        $stmt = $this->connection->prepare($query);

        // Sanitize and bind values
        $name = htmlspecialchars(strip_tags($data['name']));
        $brand_id = (int)$data['brand_id'];
        $gpu_model = htmlspecialchars(strip_tags($data['gpu_model']));
        $vram_gb = (int)$data['vram_gb'];
        $interface = htmlspecialchars(strip_tags($data['interface']));
        $boost_clock_mhz = isset($data['boost_clock_mhz']) && $data['boost_clock_mhz'] !== '' ? (int)$data['boost_clock_mhz'] : null;
        $cuda_cores = isset($data['cuda_cores']) && $data['cuda_cores'] !== '' ? (int)$data['cuda_cores'] : null;
        $stream_processors = isset($data['stream_processors']) && $data['stream_processors'] !== '' ? (int)$data['stream_processors'] : null;
        $price = (float)$data['price'];
        $stock = (int)$data['stock'];
        $description = htmlspecialchars(strip_tags($data['description'] ?? ''));
        $image_url = $data['image_url'] ?? null; // Image URL is now part of the data

        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":brand_id", $brand_id, PDO::PARAM_INT);
        $stmt->bindParam(":gpu_model", $gpu_model);
        $stmt->bindParam(":vram_gb", $vram_gb, PDO::PARAM_INT);
        $stmt->bindParam(":interface", $interface);
        $stmt->bindParam(":boost_clock_mhz", $boost_clock_mhz, PDO::PARAM_INT);
        $stmt->bindParam(":cuda_cores", $cuda_cores, PDO::PARAM_INT);
        $stmt->bindParam(":stream_processors", $stream_processors, PDO::PARAM_INT);
        $stmt->bindParam(":price", $price); // PDO will handle FLOAT/DECIMAL
        $stmt->bindParam(":stock", $stock, PDO::PARAM_INT);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":image_url", $image_url); // Bind image_url

        try {
            if ($stmt->execute()) {
                return (int)$this->connection->lastInsertId();
            }
        } catch (PDOException $e) {
            error_log("GraphicCardRepository: Create error: " . $e->getMessage());
        }
        return false;
    }

    /**
     * Updates an existing graphic card in the database.
     *
     * @param int $id The ID of the graphic card to update.
     * @param array $data Associative array of graphic card data to update.
     * @return bool True on success, false on failure.
     */
    public function update(int $id, array $data)
    {
        $setParts = [];
        $params = [':id' => $id];

        // Dynamically build the SET clause based on provided data
        if (isset($data['name'])) {
            $setParts[] = 'name = :name';
            $params[':name'] = htmlspecialchars(strip_tags($data['name']));
        }
        if (isset($data['brand_id'])) {
            $setParts[] = 'brand_id = :brand_id';
            $params[':brand_id'] = (int)$data['brand_id'];
        }
        if (isset($data['gpu_model'])) {
            $setParts[] = 'gpu_model = :gpu_model';
            $params[':gpu_model'] = htmlspecialchars(strip_tags($data['gpu_model']));
        }
        if (isset($data['vram_gb'])) {
            $setParts[] = 'vram_gb = :vram_gb';
            $params[':vram_gb'] = (int)$data['vram_gb'];
        }
        if (isset($data['interface'])) {
            $setParts[] = 'interface = :interface';
            $params[':interface'] = htmlspecialchars(strip_tags($data['interface']));
        }
        if (isset($data['boost_clock_mhz'])) {
            $setParts[] = 'boost_clock_mhz = :boost_clock_mhz';
            $params[':boost_clock_mhz'] = $data['boost_clock_mhz'] !== '' ? (int)$data['boost_clock_mhz'] : null;
        }
        if (isset($data['cuda_cores'])) {
            $setParts[] = 'cuda_cores = :cuda_cores';
            $params[':cuda_cores'] = $data['cuda_cores'] !== '' ? (int)$data['cuda_cores'] : null;
        }
        if (isset($data['stream_processors'])) {
            $setParts[] = 'stream_processors = :stream_processors';
            $params[':stream_processors'] = $data['stream_processors'] !== '' ? (int)$data['stream_processors'] : null;
        }
        if (isset($data['price'])) {
            $setParts[] = 'price = :price';
            $params[':price'] = (float)$data['price'];
        }
        if (isset($data['stock'])) {
            $setParts[] = 'stock = :stock';
            $params[':stock'] = (int)$data['stock'];
        }
        if (isset($data['description'])) {
            $setParts[] = 'description = :description';
            $params[':description'] = htmlspecialchars(strip_tags($data['description']));
        }
        // Handle image_url update: it can be set to a new path, or null if removed.
        // Check for 'image_url' specifically, allowing null.
        if (array_key_exists('image_url', $data)) {
            $setParts[] = 'image_url = :image_url';
            $params[':image_url'] = $data['image_url']; // Can be string path or null
        }


        if (empty($setParts)) {
            error_log("GraphicCardRepository: No valid fields to update for graphic card ID: " . $id);
            return false;
        }

        $query = "UPDATE graphic_cards SET " . implode(', ', $setParts) . ", updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->connection->prepare($query);

        try {
            foreach ($params as $key => $value) {
                // Determine PDO parameter type based on value
                if ($key === ':vram_gb' || $key === ':stock' || $key === ':brand_id') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } elseif ($key === ':boost_clock_mhz' || $key === ':cuda_cores' || $key === ':stream_processors') {
                    $stmt->bindValue($key, $value, $value === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                } elseif ($key === ':price') {
                    // Prices are often DECIMAL, bind as string to preserve precision
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                } elseif ($key === ':image_url') {
                    $stmt->bindValue($key, $value, $value === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                }
                else {
                    $stmt->bindValue($key, $value); // PDO will infer for strings, etc.
                }
            }

            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("GraphicCardRepository: Update error for ID " . $id . ": " . $e->getMessage());
        }
        return false;
    }

    /**
     * Deletes a graphic card from the database.
     * @param int $id The ID of the graphic card to delete.
     * @return bool True on success, false on failure.
     */
    public function delete(int $id)
    {
        $query = "DELETE FROM graphic_cards WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("GraphicCardRepository: Delete error for ID " . $id . ": " . $e->getMessage());
        }
        return false;
    }

    /**
     * Decrements the stock of a graphic card.
     * @param int $id The ID of the graphic card.
     * @param int $quantity The quantity to decrement by.
     * @return bool True on success, false on failure (e.g., insufficient stock).
     */
    public function decrementStock(int $id, int $quantity): bool {
        // Use a SQL transaction for atomicity if this is part of a larger operation.
        // Or ensure the calling service handles transactions.
        $query = "UPDATE graphic_cards SET stock = stock - ? WHERE id = ? AND stock >= ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $quantity, PDO::PARAM_INT);
        $stmt->bindParam(2, $id, PDO::PARAM_INT);
        $stmt->bindParam(3, $quantity, PDO::PARAM_INT); // Condition to prevent negative stock

        try {
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return true; // Stock was decremented
            }
        } catch (PDOException $e) {
            error_log("GraphicCardRepository: Error decrementing stock for ID {$id}: " . $e->getMessage());
        }
        return false; // Stock not decremented (e.g., insufficient stock or item not found)
    }
}
