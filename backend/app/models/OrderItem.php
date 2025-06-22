<?php

namespace App\Models; 

class OrderItem {
    public $id;
    public $order_id;
    public $graphic_card_id;
    public $quantity;
    public $price_at_purchase;
    public $graphic_card_name; // From join with graphic_cards table

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