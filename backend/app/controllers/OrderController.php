<?php
// app/controllers/OrderController.php

namespace App\Controllers;

require_once __DIR__ . '/Controller.php';
require_once dirname(__FILE__) . '/../services/OrderService.php';

use App\Services\OrderService;
use App\Models\Order;
use App\Models\OrderItem;

class OrderController extends Controller
{
    private $orderService;

    public function __construct()
    {
        parent::__construct();
        $this->orderService = new OrderService();
    }

    /**
     * Handles retrieving all orders.
     * If $userId is provided (from authenticated user), it fetches orders for that user.
     * If $role is 'admin', it fetches all orders.
     *
     * @param int|null $userId The ID of the authenticated user (optional).
     * @param string|null $role The role of the authenticated user (optional).
     * Route: GET /api/orders
     */
    public function index(?int $userId = null, ?string $role = null)
    {
        $orders = [];
        if ($role === 'admin') {
            $orders = $this->orderService->getAllOrders(); // Admins see all orders
        } elseif ($userId !== null) {
            $orders = $this->orderService->getAllOrders($userId); // Regular users see their own orders
        } else {
            // This case should ideally be caught by middleware for authenticated routes,
            // but as a fallback, if no user ID, no orders can be displayed.
            $this->errorResponse("Authentication required or no orders found for this user.", 401);
            return;
        }


        if (!empty($orders)) {
            $orders_arr = [];
            foreach ($orders as $order) {
                $orderItems_arr = [];
                if (!empty($order->items) && is_array($order->items)) {
                    foreach ($order->items as $item) {
                        $orderItems_arr[] = [
                            "id" => $item->id,
                            "order_id" => $item->order_id,
                            "graphic_card_id" => $item->graphic_card_id,
                            "quantity" => (int)$item->quantity, // Ensure quantity is integer for JSON
                            "price_at_purchase" => (float)$item->price_at_purchase,
                            "graphic_card_name" => $item->graphic_card_name
                        ];
                    }
                }

                $orders_arr[] = [
                    "id" => $order->id,
                    "user_id" => $order->user_id,
                    "username" => $order->username,
                    "total_amount" => (float)$order->total_amount,
                    "status" => $order->status,
                    "order_date" => $order->order_date,
                    "updated_at" => $order->updated_at,
                    "items" => $orderItems_arr
                ];
            }
            $this->jsonResponse($orders_arr, 200);
        } else {
            $this->jsonResponse(["message" => "No orders found."], 404);
        }
    }

    /**
     * Handles retrieving a single order by ID.
     * Users can only view their own orders unless they are an admin.
     *
     * @param int $id The ID of the order to retrieve.
     * @param int|null $authenticatedUserId The ID of the currently authenticated user.
     * @param string|null $authenticatedUserRole The role of the currently authenticated user.
     * Route: GET /api/orders/{id}
     */
    public function show(int $id, ?int $authenticatedUserId = null, ?string $authenticatedUserRole = null)
    {
        $order = $this->orderService->getOrderById($id);

        if (!$order) {
            $this->errorResponse("Order not found.", 404);
            return;
        }

        // Authorization check: Only allow access if user is admin OR if it's their own order
        if ($authenticatedUserRole !== 'admin' && $order->user_id !== $authenticatedUserId) {
            $this->errorResponse("Access Denied. You do not have permission to view this order.", 403);
            return;
        }

        $orderItems_arr = [];
        if (!empty($order->items) && is_array($order->items)) {
            foreach ($order->items as $item) {
                $orderItems_arr[] = [
                    "id" => $item->id,
                    "order_id" => $item->order_id,
                    "graphic_card_id" => $item->graphic_card_id,
                    "quantity" => (int)$item->quantity, // Ensure quantity is integer for JSON
                    "price_at_purchase" => (float)$item->price_at_purchase,
                    "graphic_card_name" => $item->graphic_card_name
                ];
            }
        }
        $this->jsonResponse([
            "order" => [
                "id" => $order->id,
                "user_id" => $order->user_id,
                "username" => $order->username,
                "total_amount" => (float)$order->total_amount,
                "status" => $order->status,
                "order_date" => $order->order_date,
                "updated_at" => $order->updated_at,
                "items" => $orderItems_arr
            ]
        ], 200);
    }

    /**
     * Handles placing a new order.
     * Expects JSON input with 'items' (an array of order item data).
     * The 'user_id' is now provided by the authentication middleware.
     *
     * @param int $userId The ID of the authenticated user placing the order.
     * Route: POST /api/orders
     */
    public function store(int $userId)
    {
        $data = $this->getJsonInput();

        // The user_id is now passed directly, no need to check in $data
        if (!isset($data['items']) || !is_array($data['items']) || empty($data['items'])) {
            $this->errorResponse("A non-empty array of items is required to place an order.", 400);
            return;
        }

        $items = $data['items'];

        $newOrderResult = $this->orderService->createOrder($userId, $items);

        if ($newOrderResult instanceof Order) {
            $responseItems = [];
            foreach ($newOrderResult->items as $item) {
                $responseItems[] = [
                    "id" => $item->id,
                    "order_id" => $item->order_id,
                    "graphic_card_id" => $item->graphic_card_id,
                    "quantity" => (int)$item->quantity, // Ensure quantity is integer for JSON
                    "price_at_purchase" => (float)$item->price_at_purchase,
                    "graphic_card_name" => $item->graphic_card_name
                ];
            }

            $this->jsonResponse([
                "message" => "Order created successfully.",
                "order" => [
                    "id" => $newOrderResult->id,
                    "user_id" => $newOrderResult->user_id,
                    "username" => $newOrderResult->username,
                    "total_amount" => (float)$newOrderResult->total_amount,
                    "status" => $newOrderResult->status,
                    "order_date" => $newOrderResult->order_date,
                    "updated_at" => $newOrderResult->updated_at,
                    "items" => $responseItems
                ]
            ], 201);
        } elseif (is_array($newOrderResult) && isset($newOrderResult['success']) && $newOrderResult['success'] === false) {
            $message = $newOrderResult['message'] ?? 'Failed to create order.';
            $statusCode = $newOrderResult['http_status'] ?? 400;
            $this->errorResponse($message, $statusCode);
        } else {
            $this->errorResponse("Failed to create order. An unexpected server error occurred.", 500);
        }
    }

    /**
     * Handles updating an existing order.
     * This route is protected by `AuthMiddleware` to require 'admin' role in `index.php`.
     * @param int $id The ID of the order to update.
     * Route: PUT /api/orders/{id}
     */
    public function update(int $id)
    {
        $data = $this->getJsonInput();

        if (empty($data)) {
            $this->errorResponse("No data provided for order update.", 400);
            return;
        }

        $updatedOrder = $this->orderService->updateOrder($id, $data);

        if ($updatedOrder) {
            $responseItems = [];
            if (!empty($updatedOrder->items) && is_array($updatedOrder->items)) {
                foreach ($updatedOrder->items as $item) {
                    $responseItems[] = [
                        "id" => $item->id,
                        "order_id" => $item->order_id,
                        "graphic_card_id" => $item->graphic_card_id,
                        "quantity" => (int)$item->quantity, // Ensure quantity is integer for JSON
                        "price_at_purchase" => (float)$item->price_at_purchase,
                        "graphic_card_name" => $item->graphic_card_name
                    ];
                }
            }

            $this->jsonResponse([
                "message" => "Order updated successfully.",
                "order" => [
                    "id" => $updatedOrder->id,
                    "user_id" => $updatedOrder->user_id,
                    "username" => $updatedOrder->username,
                    "total_amount" => (float)$updatedOrder->total_amount,
                    "status" => $updatedOrder->status,
                    "order_date" => $updatedOrder->order_date,
                    "updated_at" => $updatedOrder->updated_at,
                    "items" => $responseItems
                ]
            ], 200);
        } else {
            $this->errorResponse("Failed to update order or order not found/invalid data.", 404);
        }
    }

    /**
     * Handles deleting an order.
     * This route is protected by `AuthMiddleware` to require 'admin' role in `index.php`.
     * @param int $id The ID of the order to delete.
     * Route: DELETE /api/orders/{id}
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
