<?php

namespace App\Models; 

class Order {
    public $id;
    public $user_id;
    public $total_amount;
    public $status;
    public $order_date;
    public $updated_at;
    public $username; // From join with users table

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