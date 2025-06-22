<?php

namespace App\Models; 

class Brand {
    public $id;
    public $name;
    public $manufacturer_id;
    public $manufacturer_name; // This will be set when joining with Manufacturer data
    public $created_at;
    public $updated_at;
    public function __construct(array $data = []) {
        $this->fill($data);
    }

    public function fill(array $data) {
        foreach ($data as $key => $value) {
            // Check if the property exists and is public before assigning
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
?>
