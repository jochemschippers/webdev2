<?php
// app/repositories/GraphicCardRepository.php

namespace App\Repositories;

use \PDO;
use \PDOException;

require_once dirname(__FILE__) . '/../models/GraphicCard.php';
require_once __DIR__ . '/Repository.php';

class GraphicCardRepository extends Repository {

    public function __construct(PDO $existingConnection = null) {
        parent::__construct($existingConnection);
    }

    public function getAll(array $filters = []) {
        $query = "SELECT gc.id, gc.name, gc.brand_id, b.name AS brand_name,
                         m.name AS manufacturer_name, gc.gpu_model, gc.vram_gb,
                         gc.interface, gc.boost_clock_mhz, gc.cuda_cores,
                         gc.stream_processors, gc.price, gc.stock, gc.description,
                         gc.image_url, gc.created_at, gc.updated_at
                  FROM graphic_cards gc
                  LEFT JOIN brands b ON gc.brand_id = b.id
                  LEFT JOIN manufacturers m ON b.manufacturer_id = m.id";

        $conditions = [];
        $params = [];

        if (isset($filters['brand_id']) && is_numeric($filters['brand_id'])) {
            $conditions[] = "gc.brand_id = :brand_id";
            $params[':brand_id'] = (int)$filters['brand_id'];
        }
        if (isset($filters['manufacturer_id']) && is_numeric($filters['manufacturer_id'])) {
            $conditions[] = "m.id = :manufacturer_id";
            $params[':manufacturer_id'] = (int)$filters['manufacturer_id'];
        }
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $conditions[] = "gc.price >= :min_price";
            $params[':min_price'] = (float)$filters['min_price'];
        }
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $conditions[] = "gc.price <= :max_price";
            $params[':max_price'] = (float)$filters['max_price'];
        }
        if (isset($filters['name_like']) && !empty($filters['name_like'])) {
            $conditions[] = "gc.name LIKE :name_like";
            $params[':name_like'] = '%' . $filters['name_like'] . '%';
        }
        if (isset($filters['min_vram']) && is_numeric($filters['min_vram'])) {
            $conditions[] = "gc.vram_gb >= :min_vram";
            $params[':min_vram'] = (int)$filters['min_vram'];
        }
        if (isset($filters['max_vram']) && is_numeric($filters['max_vram'])) {
            $conditions[] = "gc.vram_gb <= :max_vram";
            $params[':max_vram'] = (int)$filters['max_vram'];
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $query .= " ORDER BY gc.name ASC";

        $stmt = $this->connection->prepare($query);
        foreach ($params as $key => $val) {
            // Determine PDO parameter type based on value
            if (is_int($val)) {
                $type = PDO::PARAM_INT;
            } elseif (is_bool($val)) {
                $type = PDO::PARAM_BOOL;
            } elseif (is_null($val)) {
                $type = PDO::PARAM_NULL;
            } else {
                $type = PDO::PARAM_STR;
            }
            $stmt->bindValue($key, $val, $type);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT gc.id, gc.name, gc.brand_id, b.name AS brand_name,
                         m.name AS manufacturer_name, gc.gpu_model, gc.vram_gb,
                         gc.interface, gc.boost_clock_mhz, gc.cuda_cores,
                         gc.stream_processors, gc.price, gc.stock, gc.description,
                         gc.image_url, gc.created_at, gc.updated_at
                  FROM graphic_cards gc
                  LEFT JOIN brands b ON gc.brand_id = b.id
                  LEFT JOIN manufacturers m ON b.manufacturer_id = m.id
                  WHERE gc.id = ? LIMIT 0,1";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $query = "INSERT INTO graphic_cards (name, brand_id, gpu_model, vram_gb, interface,
                                    boost_clock_mhz, cuda_cores, stream_processors,
                                    price, stock, description, image_url)
                  VALUES (:name, :brand_id, :gpu_model, :vram_gb, :interface,
                          :boost_clock_mhz, :cuda_cores, :stream_processors,
                          :price, :stock, :description, :image_url)";
        $stmt = $this->connection->prepare($query);

        // Sanitize and bind parameters
        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":brand_id", $data['brand_id'], PDO::PARAM_INT);
        $stmt->bindParam(":gpu_model", $data['gpu_model']);
        $stmt->bindParam(":vram_gb", $data['vram_gb'], PDO::PARAM_INT);
        $stmt->bindParam(":interface", $data['interface']);
        
        // Handle nullable integer fields, bind as NULL if empty string or null
        $boost_clock_mhz = !empty($data['boost_clock_mhz']) ? (int)$data['boost_clock_mhz'] : null;
        $stmt->bindParam(":boost_clock_mhz", $boost_clock_mhz, PDO::PARAM_INT);

        $cuda_cores = !empty($data['cuda_cores']) ? (int)$data['cuda_cores'] : null;
        $stmt->bindParam(":cuda_cores", $cuda_cores, PDO::PARAM_INT);

        $stream_processors = !empty($data['stream_processors']) ? (int)$data['stream_processors'] : null;
        $stmt->bindParam(":stream_processors", $stream_processors, PDO::PARAM_INT);

        $stmt->bindParam(":price", $data['price']); // PDO handles FLOAT/DECIMAL
        $stmt->bindParam(":stock", $data['stock'], PDO::PARAM_INT);

        $description = $data['description'] ?? null;
        $stmt->bindParam(":description", $description);

        // Image URL can be NULL
        $image_url = $data['image_url'] ?? null;
        $stmt->bindParam(":image_url", $image_url);


        try {
            if ($stmt->execute()) {
                return (int)$this->connection->lastInsertId();
            }
        } catch (PDOException $e) {
            error_log("GraphicCardRepository: PDOException during graphic card creation: " . $e->getMessage());
        }
        return false;
    }

    public function update(int $id, array $data) {
        $setParts = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            // Skip ID and timestamps for direct update, as they are managed differently
            if (in_array($key, ['id', 'created_at', 'updated_at'])) {
                continue;
            }

            $setParts[] = "`{$key}` = :{$key}";
            // Special handling for nullable integer fields
            if (in_array($key, ['boost_clock_mhz', 'cuda_cores', 'stream_processors'])) {
                $params[":{$key}"] = ($value === '' || $value === null) ? null : (int)$value;
            } elseif ($key === 'price') {
                $params[":{$key}"] = (float)$value;
            } elseif ($key === 'stock' || $key === 'vram_gb' || $key === 'brand_id') {
                $params[":{$key}"] = (int)$value;
            } else {
                $params[":{$key}"] = $value;
            }
        }

        if (empty($setParts)) {
            return false; // No fields to update
        }

        $query = "UPDATE graphic_cards SET " . implode(', ', $setParts) . " WHERE id = :id";
        $stmt = $this->connection->prepare($query);

        foreach ($params as $key => $value) {
            // Use specific PDO parameter types where appropriate
            if (is_int($value)) {
                $type = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $type = PDO::PARAM_BOOL;
            } elseif (is_null($value)) {
                $type = PDO::PARAM_NULL;
            } else {
                $type = PDO::PARAM_STR;
            }
            $stmt->bindValue($key, $value, $type);
        }

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("GraphicCardRepository: PDOException during graphic card update (ID: {$id}): " . $e->getMessage());
        }
        return false;
    }
    public function updateStock(int $id, int $newStock): bool {
        $query = "UPDATE graphic_cards SET stock = :stock WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(":stock", $newStock, PDO::PARAM_INT);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        try {
            $success = $stmt->execute();
            if ($success && $stmt->rowCount() > 0) {
                error_log("GraphicCardRepository: Stock updated for ID {$id} to {$newStock}.");
                return true;
            } else {
                error_log("GraphicCardRepository: Failed to update stock for ID {$id}. No rows affected or error. ErrorInfo: " . print_r($stmt->errorInfo(), true));
                return false;
            }
        } catch (PDOException $e) {
            error_log("GraphicCardRepository: PDOException during stock update for ID {$id}: " . $e->getMessage());
            return false;
        }
    }


    public function delete(int $id) {
        $query = "DELETE FROM graphic_cards WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("GraphicCardRepository: PDOException during graphic card delete: " . $e->getMessage());
        }
        return false;
    }
}
