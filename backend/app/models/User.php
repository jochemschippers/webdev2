<?php

namespace App\Models; 

class User {
    public $id;
    public $username;
    public $email;
    public $password_hash;
    public $role;
    public $created_at;
    public $updated_at;

    public function __construct(array $data = []) {
        $this->fill($data); // Call the fill method to populate properties
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