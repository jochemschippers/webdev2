<?php

namespace App\Models; 

class GraphicCard {
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
    public $brand_name; // From join with brands table
    public $manufacturer_name; // From join with manufacturers table
    public function __construct(array $data = []) {
        $this->fill($data);
    }    public function fill(array $data) {
        // Iterate through the data array and assign values to matching public properties
        foreach ($data as $key => $value) {
            // Check if the property exists and is public before assigning
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
?>
