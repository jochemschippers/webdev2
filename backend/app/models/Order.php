<?php
// app/models/Order.php

class Order {
    private $table_name = "orders";

    public $id;
    public $user_id;
    public $total_amount;
    public $status;
    public $order_date;
    public $updated_at;
    public $username; // For joining
}
?>
