<?php
// app/repositories/GraphicCardRepository.php

namespace App\Repositories;

use \PDO;
use \PDOException;

require_once dirname(__FILE__) . '/../models/GraphicCard.php';
require_once dirname(__FILE__) . '/../models/Brand.php';
require_once dirname(__FILE__) . '/../models/Manufacturer.php';
require_once __DIR__ . '/Repository.php';

class GraphicCardRepository extends Repository {
    /**
     * Constructor for GraphicCardRepository.
     * Calls the parent constructor to establish the database connection.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Retrieves all graphic cards with joined brand and manufacturer names.
     * @return array An array of associative arrays of graphic card data.
     */
    public function getAll() {
        $query = "SELECT gc.id, gc.name, gc.gpu_model, gc.vram_gb, gc.interface,
                         gc.boost_clock_mhz, gc.cuda_cores, gc.stream_processors,
                         gc.price, gc.stock, gc.description, gc.image_url,
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
     * Retrieves a single graphic card by ID with joined brand and manufacturer names.
     * @param int $id
     * @return array|false Returns associative array of graphic card data if found, false otherwise.
     */
    public function getById($id) {
        $query = "SELECT gc.id, gc.name, gc.gpu_model, gc.vram_gb, gc.interface,
                         gc.boost_clock_mhz, gc.cuda_cores, gc.stream_processors,
                         gc.price, gc.stock, gc.description, gc.image_url,
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
     * @param array $data Graphic card data.
     * @return int|false Returns the ID of the newly created graphic card on success, false on failure.
     */
    public function create(array $data) {
        $query = "INSERT INTO graphic_cards
                  (name, brand_id, gpu_model, vram_gb, interface, boost_clock_mhz, cuda_cores, stream_processors, price, stock, description, image_url)
                  VALUES (:name, :brand_id, :gpu_model, :vram_gb, :interface, :boost_clock_mhz, :cuda_cores, :stream_processors, :price, :stock, :description, :image_url)";

        $stmt = $this->connection->prepare($query);

        // Sanitize data (optional, but good practice for strings)
        $name = htmlspecialchars(strip_tags($data['name']));
        $brand_id = $data['brand_id']; // Assumed to be validated before this layer
        $gpu_model = htmlspecialchars(strip_tags($data['gpu_model']));
        $vram_gb = $data['vram_gb'];
        $interface = htmlspecialchars(strip_tags($data['interface']));
        $boost_clock_mhz = $data['boost_clock_mhz'] ?? null;
        $cuda_cores = $data['cuda_cores'] ?? null;
        $stream_processors = $data['stream_processors'] ?? null;
        $price = $data['price'];
        $stock = $data['stock'];
        $description = htmlspecialchars(strip_tags($data['description'] ?? ''));
        $image_url = htmlspecialchars(strip_tags($data['image_url'] ?? ''));

        // Bind values
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":brand_id", $brand_id, PDO::PARAM_INT);
        $stmt->bindParam(":gpu_model", $gpu_model);
        $stmt->bindParam(":vram_gb", $vram_gb, PDO::PARAM_INT);
        $stmt->bindParam(":interface", $interface);
        $stmt->bindParam(":boost_clock_mhz", $boost_clock_mhz, PDO::PARAM_INT);
        $stmt->bindParam(":cuda_cores", $cuda_cores, PDO::PARAM_INT);
        $stmt->bindParam(":stream_processors", $stream_processors, PDO::PARAM_INT);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":stock", $stock, PDO::PARAM_INT);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":image_url", $image_url);

        if ($stmt->execute()) {
            return (int)$this->connection->lastInsertId();
        }
        return false;
    }

    /**
     * Updates an existing graphic card in the database.
     * @param int $id The ID of the graphic card to update.
     * @param array $data Graphic card data to update.
     * @return bool True on success, false on failure.
     */
    public function update($id, array $data) {
        $query = "UPDATE graphic_cards
                  SET name = :name, brand_id = :brand_id, gpu_model = :gpu_model,
                      vram_gb = :vram_gb, interface = :interface,
                      boost_clock_mhz = :boost_clock_mhz, cuda_cores = :cuda_cores,
                      stream_processors = :stream_processors, price = :price,
                      stock = :stock, description = :description, image_url = :image_url
                  WHERE id = :id";

        $stmt = $this->connection->prepare($query);

        // Sanitize data
        $name = htmlspecialchars(strip_tags($data['name']));
        $brand_id = $data['brand_id'];
        $gpu_model = htmlspecialchars(strip_tags($data['gpu_model']));
        $vram_gb = $data['vram_gb'];
        $interface = htmlspecialchars(strip_tags($data['interface']));
        $boost_clock_mhz = $data['boost_clock_mhz'] ?? null;
        $cuda_cores = $data['cuda_cores'] ?? null;
        $stream_processors = $data['stream_processors'] ?? null;
        $price = $data['price'];
        $stock = $data['stock'];
        $description = htmlspecialchars(strip_tags($data['description'] ?? ''));
        $image_url = htmlspecialchars(strip_tags($data['image_url'] ?? ''));

        // Bind values
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":brand_id", $brand_id, PDO::PARAM_INT);
        $stmt->bindParam(":gpu_model", $gpu_model);
        $stmt->bindParam(":vram_gb", $vram_gb, PDO::PARAM_INT);
        $stmt->bindParam(":interface", $interface);
        $stmt->bindParam(":boost_clock_mhz", $boost_clock_mhz, PDO::PARAM_INT);
        $stmt->bindParam(":cuda_cores", $cuda_cores, PDO::PARAM_INT);
        $stmt->bindParam(":stream_processors", $stream_processors, PDO::PARAM_INT);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":stock", $stock, PDO::PARAM_INT);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":image_url", $image_url);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Deletes a graphic card from the database.
     * @param int $id The ID of the graphic card to delete.
     * @return bool True on success, false on failure.
     */
    public function delete($id) {
        $query = "DELETE FROM graphic_cards WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
