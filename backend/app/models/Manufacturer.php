<?php
// app/models/Manufacturer.php

namespace App\Models; // <--- Ensure this namespace is present and correct

class Manufacturer {
    public $id;
    public $name;
    public $created_at;
    public $updated_at;

    public function __construct(array $data = []) {
        $this->fill($data);
    }

    public function fill(array $data) {
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