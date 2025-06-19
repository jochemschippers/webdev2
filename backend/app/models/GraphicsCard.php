<?php
// app/models/GraphicCard.php

class GraphicCard {
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
}
?>
