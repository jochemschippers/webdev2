<?php
// app/controllers/OrderController.php

namespace App\Controllers;

require_once __DIR__ . '/Controller.php';
require_once dirname(__FILE__) . '/../services/OrderService.php';

use App\Services\OrderService;
use App\Utils\Response;

class OrderController extends Controller
{
    private $orderService;

    public function __construct()
    {
        parent::__construct();
        $this->orderService = new OrderService();
    }

    /**
     * Handles retrieving all orders (for admin) or orders for a specific user.
     * Route: GET /api/orders
     * @param int|null $authUserId The authenticated user's ID (from JWT).
     * @param string|null $authUserRole The authenticated user's role (from JWT).
     */
    public function index(?int $authUserId = null, ?string $authUserRole = null)
    {
        $orders = [];
        if ($authUserRole === 'admin') {
            // Admins can see all orders
            $orders = $this->orderService->getAllOrders();
        } elseif ($authUserId !== null) {
            // Regular users can only see their own orders
            $orders = $this->orderService->getAllOrders($authUserId);
        } else {
            Response::error("Authentication required to view orders.", 401);
            return; // Exit to prevent further execution
        }

        if (!empty($orders)) {
            $orders_arr = [];
            foreach ($orders as $order) {
                $order_items_arr = [];
                foreach ($order->items as $item) {
                    $order_items_arr[] = [
                        "id" => $item->id,
                        "graphic_card_id" => $item->graphic_card_id,
                        "graphic_card_name" => $item->graphic_card_name,
                        "quantity" => $item->quantity,
                        "price_at_purchase" => $item->price_at_purchase
                    ];
                }

                $orders_arr[] = [
                    "id" => $order->id,
                    "user_id" => $order->user_id,
                    "username" => $order->username, // Included username
                    "total_amount" => $order->total_amount,
                    "status" => $order->status,
                    "order_date" => $order->order_date,
                    "updated_at" => $order->updated_at,
                    "items" => $order_items_arr // Include order items
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
     * @param int|null $authUserId The authenticated user's ID.
     * @param string|null $authUserRole The authenticated user's role.
     */
    public function show(int $id, ?int $authUserId = null, ?string $authUserRole = null)
    {
        $order = $this->orderService->getOrderById($id);

        if (!$order) {
            $this->errorResponse("Order not found.", 404);
        }

        // Authorization check: User can only view their own orders unless they are an admin
        if ($order->user_id !== $authUserId && $authUserRole !== 'admin') {
            Response::error("Access Denied. You do not have permission to view this order.", 403);
            return;
        }

        $order_items_arr = [];
        foreach ($order->items as $item) {
            $order_items_arr[] = [
                "id" => $item->id,
                "graphic_card_id" => $item->graphic_card_id,
                "graphic_card_name" => $item->graphic_card_name,
                "quantity" => $item->quantity,
                "price_at_purchase" => $item->price_at_purchase
            ];
        }

        $this->jsonResponse([
            "id" => $order->id,
            "user_id" => $order->user_id,
            "username" => $order->username,
            "total_amount" => $order->total_amount,
            "status" => $order->status,
            "order_date" => $order->order_date,
            "updated_at" => $order->updated_at,
            "items" => $order_items_arr
        ], 200);
    }

    /**
     * Handles creating a new order.
     * Route: POST /api/orders
     * @param int $authUserId The ID of the authenticated user placing the order.
     */
    public function store(int $authUserId)
    {
        $data = $this->getJsonInput();

        if (empty($data['items'])) {
            $this->errorResponse("No items provided for the order.", 400);
        }

        $items = $data['items'];
        $newOrder = $this->orderService->createOrder($authUserId, $items);

        if ($newOrder instanceof \App\Models\Order) { // Check if it's an Order object
            // Format the response to include basic order details
            $response = [
                "message" => "Order placed successfully.",
                "order" => [
                    "id" => $newOrder->id,
                    "user_id" => $newOrder->user_id,
                    "total_amount" => $newOrder->total_amount,
                    "status" => $newOrder->status,
                    "order_date" => $newOrder->order_date,
                    "items" => array_map(function($item) {
                        return [
                            "graphic_card_id" => $item->graphic_card_id,
                            "quantity" => $item->quantity,
                            "price_at_purchase" => $item->price_at_purchase
                        ];
                    }, $newOrder->items)
                ]
            ];
            $this->jsonResponse($response, 201);
        } elseif (is_array($newOrder) && isset($newOrder['success']) && $newOrder['success'] === false) {
            // Handle errors returned as an array from service layer
            $this->errorResponse($newOrder['message'], $newOrder['http_status'] ?? 400);
        } else {
            $this->errorResponse("Failed to place order.", 500);
        }
    }

    /**
     * Handles updating an existing order's status.
     * Route: PUT /api/orders/{id}
     * This action is typically restricted to admin users.
     * @param int $id The ID of the order to update.
     */
    public function update(int $id)
    {
        $data = $this->getJsonInput();

        if (empty($data['status'])) {
            $this->errorResponse("Order status is required for update.", 400);
        }

        $status = $data['status'];
        $success = $this->orderService->updateOrder($id, ['status' => $status]);

        if ($success) {
            $this->jsonResponse([
                "message" => "Order status updated successfully.",
                "order" => [
                    "id" => $success->id,
                    "status" => $success->status,
                    "total_amount" => $success->total_amount // Return updated details
                ]
            ], 200);
        } else {
            $this->errorResponse("Failed to update order status or order not found.", 400);
        }
    }

    /**
     * Handles deleting an order.
     * Route: DELETE /api/orders/{id}
     * This action is typically restricted to admin users.
     * @param int $id The ID of the order to delete.
     */
    public function destroy(int $id)
    {
        $success = $this->orderService->deleteOrder($id);

        if ($success) {
            $this->jsonResponse(["message" => "Order deleted successfully."], 200);
        } else {
            $this->errorResponse("Failed to delete order or order not found.", 400);
        }
    }
}
