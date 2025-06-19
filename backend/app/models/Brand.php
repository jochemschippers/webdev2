<?php
// app/models/Brand.php

class Brand {
    private $table_name = "brands";

    public $id;
    public $name;
    public $manufacturer_id;
    public $manufacturer_name; // For joining
    public $created_at;
    public $updated_at;
}
?>
