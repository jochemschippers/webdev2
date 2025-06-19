<?php
// app/controllers/OrderController.php

namespace App\Controllers; // Use the same namespace as the base Controller

require_once __DIR__ . '/Controller.php'; // Require the base Controller
require_once dirname(__FILE__) . '/../services/OrderService.php'; // Require the OrderService

use App\Services\OrderService; // Use the OrderService from its namespace

class OrderController extends Controller
{
    private $orderService;

    /**
     * Constructor for OrderController.
     * Initializes the OrderService.
     */
    public function __construct()
    {
        parent::__construct(); // Call the parent constructor to set up headers
        $this->orderService = new OrderService(); // Instantiate the OrderService
    }

    /**
     * Handles retrieving all orders.
     * Route: GET /api/orders
     */
    public function index()
    {
        $orders = $this->orderService->getAllOrders();

        if (!empty($orders)) {
            $orders_arr = [];
            foreach ($orders as $order) {
                $orders_arr[] = [
                    "id" => $order->id,
                    "user_id" => $order->user_id,
                    "username" => $order->username, // From join
                    "total_amount" => $order->total_amount,
                    "status" => $order->status,
                    "order_date" => $order->order_date,
                    "updated_at" => $order->updated_at
                ];
            }
            $this->jsonResponse($orders_arr, 200);
        } else {
            $this->jsonResponse(["message" => "No orders found."], 404);
        }
    }

    /**
     * Handles retrieving a single order by ID.
     * Route: GET /api/orders/{id}
     * @param int $id The ID of the order to retrieve.
     */
    public function show(int $id)
    {
        $orderWithItems = $this->orderService->getOrderById($id);

        if ($orderWithItems) {
            $order = $orderWithItems['order'];
            $items = $orderWithItems['items'];

            $order_response = [
                "id" => $order->id,
                "user_id" => $order->user_id,
                "username" => $order->username,
                "total_amount" => $order->total_amount,
                "status" => $order->status,
                "order_date" => $order->order_date,
                "updated_at" => $order->updated_at,
                "items" => []
            ];

            foreach ($items as $item) {
                $order_response['items'][] = [
                    "id" => $item->id,
                    "graphic_card_id" => $item->graphic_card_id,
                    "graphic_card_name" => $item->graphic_card_name,
                    "quantity" => $item->quantity,
                    "price_at_purchase" => $item->price_at_purchase
                ];
            }
            $this->jsonResponse($order_response, 200);
        } else {
            $this->errorResponse("Order not found.", 404);
        }
    }

    /**
     * Handles creating a new order.
     * Route: POST /api/orders
     */
    public function store()
    {
        $data = $this->getJsonInput();

        if (empty($data['user_id']) || empty($data['items']) || !is_array($data['items'])) {
            $this->errorResponse("User ID and an array of items are required.", 400);
        }

        $userId = (int)$data['user_id'];
        $items = $data['items']; // Array of graphic_card_id and quantity

        $newOrder = $this->orderService->createOrder($userId, $items);

        if ($newOrder) {
            $order_response = [
                "message" => "Order created successfully.",
                "order" => [
                    "id" => $newOrder->id,
                    "user_id" => $newOrder->user_id,
                    "total_amount" => $newOrder->total_amount,
                    "status" => $newOrder->status,
                    "order_date" => $newOrder->order_date,
                    "updated_at" => $newOrder->updated_at,
                    "items" => []
                ]
            ];
            // Add items to the response if they are part of the returned Order object
            if (isset($newOrder->items) && is_array($newOrder->items)) {
                foreach ($newOrder->items as $item) {
                    $order_response['order']['items'][] = [
                        "id" => $item->id,
                        "graphic_card_id" => $item->graphic_card_id,
                        "graphic_card_name" => $item->graphic_card_name, // Assuming this is populated in the service
                        "quantity" => $item->quantity,
                        "price_at_purchase" => $item->price_at_purchase
                    ];
                }
            }
            $this->jsonResponse($order_response, 201);
        } else {
            $this->errorResponse("Failed to create order. Check item availability, user ID, or internal server logs.", 500);
        }
    }

    /**
     * Handles updating an existing order.
     * Route: PUT /api/orders/{id}
     * @param int $id The ID of the order to update.
     */
    public function update(int $id)
    {
        $data = $this->getJsonInput();

        // Allow partial updates for status or total_amount, but ensure at least one is present
        if (empty($data['user_id']) && empty($data['total_amount']) && empty($data['status'])) {
            $this->errorResponse("At least one field (user_id, total_amount, or status) is required for update.", 400);
        }

        $success = $this->orderService->updateOrder($id, $data);

        if ($success) {
            // Fetch updated data to return to client
            $updatedOrderWithItems = $this->orderService->getOrderById($id);
            if ($updatedOrderWithItems) {
                $order = $updatedOrderWithItems['order'];
                $items = $updatedOrderWithItems['items'];

                $order_response = [
                    "message" => "Order updated successfully.",
                    "order" => [
                        "id" => $order->id,
                        "user_id" => $order->user_id,
                        "username" => $order->username,
                        "total_amount" => $order->total_amount,
                        "status" => $order->status,
                        "order_date" => $order->order_date,
                        "updated_at" => $order->updated_at,
                        "items" => []
                    ]
                ];
                foreach ($items as $item) {
                    $order_response['order']['items'][] = [
                        "id" => $item->id,
                        "graphic_card_id" => $item->graphic_card_id,
                        "graphic_card_name" => $item->graphic_card_name,
                        "quantity" => $item->quantity,
                        "price_at_purchase" => $item->price_at_purchase
                    ];
                }
                $this->jsonResponse($order_response, 200);
            } else {
                 $this->errorResponse("Failed to fetch updated order data after successful update.", 500);
            }
        } else {
            $this->errorResponse("Failed to update order or order not found.", 404);
        }
    }

    /**
     * Handles deleting an order.
     * Route: DELETE /api/orders/{id}
     * @param int $id The ID of the order to delete.
     */
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
