<?php
// app/models/OrderItem.php

class OrderItem {
    private $table_name = "order_items";

    public $id;
    public $order_id;
    public $graphic_card_id;
    public $quantity;
    public $price_at_purchase;
    public $graphic_card_name; // For joining
}
?>
