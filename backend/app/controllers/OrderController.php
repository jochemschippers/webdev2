<?php
// app/controllers/OrderController.php

namespace App\Controllers;

require_once __DIR__ . '/Controller.php';
require_once dirname(__FILE__) . '/../services/OrderService.php';

use App\Services\OrderService;
use App\Models\Order;
use App\Models\OrderItem; // Ensure OrderItem model is imported if you're using its properties

class OrderController extends Controller
{
    private $orderService;

    public function __construct()
    {
        parent::__construct();
        $this->orderService = new OrderService();
    }

    public function index()
    {
        $orders = $this->orderService->getAllOrders();

        if (!empty($orders)) {
            $orders_arr = [];
            foreach ($orders as $order) {
                $orderItems_arr = [];
                // Ensure $order->items is an iterable array of OrderItem objects
                if (!empty($order->items) && is_array($order->items)) {
                    foreach ($order->items as $item) {
                        // Access properties directly from the OrderItem object
                        // Ensure 'graphic_card_name' is a property on OrderItem or is correctly joined in OrderItemRepository
                        $orderItems_arr[] = [
                            "id" => $item->id,
                            "order_id" => $item->order_id,
                            "graphic_card_id" => $item->graphic_card_id,
                            "quantity" => $item->quantity,
                            "price_at_purchase" => $item->price_at_purchase,
                            "graphic_card_name" => $item->graphic_card_name // This needs to be available
                        ];
                    }
                }

                $orders_arr[] = [
                    "id" => $order->id,
                    "user_id" => $order->user_id,
                    "username" => $order->username,
                    "total_amount" => $order->total_amount,
                    "status" => $order->status,
                    "order_date" => $order->order_date,
                    "updated_at" => $order->updated_at,
                    "items" => $orderItems_arr // Include the serialized order items
                ];
            }
            $this->jsonResponse($orders_arr, 200);
        } else {
            $this->jsonResponse(["message" => "No orders found."], 404);
        }
    }

    public function show(int $id)
    {
        $orderData = $this->orderService->getOrderById($id);

        if ($orderData) {
            $this->jsonResponse([
                "order" => [
                    "id" => $orderData['id'],
                    "user_id" => $orderData['user_id'],
                    "username" => $orderData['username'],
                    "total_amount" => $orderData['total_amount'],
                    "status" => $orderData['status'],
                    "order_date" => $orderData['order_date'],
                    "updated_at" => $orderData['updated_at'],
                    "items" => $orderData['items']
                ]
            ], 200);
        } else {
            $this->errorResponse("Order not found.", 404);
        }
    }

    public function store()
    {
        $data = $this->getJsonInput();

        if (empty($data['user_id']) || empty($data['items']) || !is_array($data['items'])) {
            $this->errorResponse("User ID and an array of items are required.", 400);
        }

        $userId = (int)$data['user_id'];
        $items = $data['items'];

        $newOrderData = $this->orderService->createOrder($userId, $items);

        if ($newOrderData) {
            $this->jsonResponse([
                "message" => "Order created successfully.",
                "order" => $newOrderData
            ], 201);
        } else {
            $this->errorResponse("Failed to create order. Check item availability, user ID, or internal server logs.", 500);
        }
    }

    public function update(int $id)
    {
        $data = $this->getJsonInput();

        if (empty($data)) {
            $this->errorResponse("No data provided for update.", 400);
        }

        $updatedOrderData = $this->orderService->updateOrder($id, $data);

        if ($updatedOrderData) {
            $this->jsonResponse([
                "message" => "Order updated successfully.",
                "order" => $updatedOrderData
            ], 200);
        } else {
            $this->errorResponse("Failed to update order or order not found/invalid data.", 404);
        }
    }

    public function destroy(int $id)
    {
        $success = $this->orderService->deleteOrder($id);

        if ($success) {
            $this->jsonResponse(["message" => "Order deleted successfully."], 200);
        } else {
            $this->errorResponse("Failed to delete order or order not found.", 404);
        }
    }
}
?>
