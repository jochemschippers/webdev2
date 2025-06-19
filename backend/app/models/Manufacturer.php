<?php
// app/models/Manufacturer.php

namespace App\Models; // <--- Ensure this namespace is present and correct

class Manufacturer {
    public $id;
    public $name;
    public $created_at;
    public $updated_at;

    /**
     * Constructor to initialize manufacturer properties from data array.
     * @param array $data Associative array of manufacturer data.
     */
    public function __construct(array $data = []) {
        $this->fill($data);
    }

    /**
     * Fills the model properties from an associative array.
     * This method iterates through the provided data and assigns to matching public properties.
     * @param array $data
     */
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