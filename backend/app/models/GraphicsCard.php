<?php
// app/models/GraphicCard.php

class GraphicCard {
    private $conn;
    private $table_name = "graphic_cards";

    public $id;
    public $name;
    public $brand_id;
    public $gpu_model;
    public $vram_gb;
    public $interface;
    public $boost_clock_mhz;
    public $cuda_cores;
    public $stream_processors;
    public $price;
    public $stock;
    public $description;
    public $image_url;
    public $brand_name; // For joining
    public $manufacturer_name; // For joining

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Reads all graphic cards with joined brand and manufacturer names.
     * @return PDOStatement
     */
    public function readAll() {
        $query = "SELECT gc.id, gc.name, gc.gpu_model, gc.vram_gb, gc.interface,
                         gc.boost_clock_mhz, gc.cuda_cores, gc.stream_processors,
                         gc.price, gc.stock, gc.description, gc.image_url,
                         b.name as brand_name, m.name as manufacturer_name
                  FROM " . $this->table_name . " gc
                  LEFT JOIN brands b ON gc.brand_id = b.id
                  LEFT JOIN manufacturers m ON b.manufacturer_id = m.id
                  ORDER BY gc.name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Reads one graphic card by ID with joined brand and manufacturer names.
     * @param int $id
     * @return bool True if graphic card found, false otherwise.
     */
    public function readOne($id) {
        $query = "SELECT gc.id, gc.name, gc.gpu_model, gc.vram_gb, gc.interface,
                         gc.boost_clock_mhz, gc.cuda_cores, gc.stream_processors,
                         gc.price, gc.stock, gc.description, gc.image_url,
                         b.name as brand_name, m.name as manufacturer_name
                  FROM " . $this->table_name . " gc
                  LEFT JOIN brands b ON gc.brand_id = b.id
                  LEFT JOIN manufacturers m ON b.manufacturer_id = m.id
                  WHERE gc.id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->brand_id = $row['brand_id'];
            $this->gpu_model = $row['gpu_model'];
            $this->vram_gb = $row['vram_gb'];
            $this->interface = $row['interface'];
            $this->boost_clock_mhz = $row['boost_clock_mhz'];
            $this->cuda_cores = $row['cuda_cores'];
            $this->stream_processors = $row['stream_processors'];
            $this->price = $row['price'];
            $this->stock = $row['stock'];
            $this->description = $row['description'];
            $this->image_url = $row['image_url'];
            $this->brand_name = $row['brand_name'];
            $this->manufacturer_name = $row['manufacturer_name'];
            return true;
        }
        return false;
    }

    /**
     * Creates a new graphic card.
     * @return bool True on success, false on failure.
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  (name, brand_id, gpu_model, vram_gb, interface, boost_clock_mhz, cuda_cores, stream_processors, price, stock, description, image_url)
                  VALUES (:name, :brand_id, :gpu_model, :vram_gb, :interface, :boost_clock_mhz, :cuda_cores, :stream_processors, :price, :stock, :description, :image_url)";

        $stmt = $this->conn->prepare($query);

        // Sanitize data (optional, but good practice for strings)
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->gpu_model = htmlspecialchars(strip_tags($this->gpu_model));
        $this->interface = htmlspecialchars(strip_tags($this->interface));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":brand_id", $this->brand_id, PDO::PARAM_INT);
        $stmt->bindParam(":gpu_model", $this->gpu_model);
        $stmt->bindParam(":vram_gb", $this->vram_gb, PDO::PARAM_INT);
        $stmt->bindParam(":interface", $this->interface);
        $stmt->bindParam(":boost_clock_mhz", $this->boost_clock_mhz, PDO::PARAM_INT);
        $stmt->bindParam(":cuda_cores", $this->cuda_cores, PDO::PARAM_INT);
        $stmt->bindParam(":stream_processors", $this->stream_processors, PDO::PARAM_INT);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":stock", $this->stock, PDO::PARAM_INT);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":image_url", $this->image_url);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId(); // Set the ID of the newly created card
            return true;
        }
        return false;
    }

    /**
     * Updates an existing graphic card.
     * @return bool True on success, false on failure.
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET name = :name, brand_id = :brand_id, gpu_model = :gpu_model,
                      vram_gb = :vram_gb, interface = :interface,
                      boost_clock_mhz = :boost_clock_mhz, cuda_cores = :cuda_cores,
                      stream_processors = :stream_processors, price = :price,
                      stock = :stock, description = :description, image_url = :image_url
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->gpu_model = htmlspecialchars(strip_tags($this->gpu_model));
        $this->interface = htmlspecialchars(strip_tags($this->interface));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":brand_id", $this->brand_id, PDO::PARAM_INT);
        $stmt->bindParam(":gpu_model", $this->gpu_model);
        $stmt->bindParam(":vram_gb", $this->vram_gb, PDO::PARAM_INT);
        $stmt->bindParam(":interface", $this->interface);
        $stmt->bindParam(":boost_clock_mhz", $this->boost_clock_mhz, PDO::PARAM_INT);
        $stmt->bindParam(":cuda_cores", $this->cuda_cores, PDO::PARAM_INT);
        $stmt->bindParam(":stream_processors", $this->stream_processors, PDO::PARAM_INT);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":stock", $this->stock, PDO::PARAM_INT);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Deletes a graphic card.
     * @param int $id The ID of the graphic card to delete.
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
