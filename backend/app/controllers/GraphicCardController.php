<?php
// app/controllers/GraphicCardController.php

namespace App\Controllers; // Use the same namespace as the base Controller

require_once __DIR__ . '/Controller.php'; // Require the base Controller
require_once dirname(__FILE__) . '/../services/GraphicCardService.php'; // Require the GraphicCardService
require_once dirname(__FILE__) . '/../utils/Response.php'; // Ensure Response is included
use App\Utils\Response; // Use Response class

use App\Services\GraphicCardService; // Use the GraphicCardService from its namespace

class GraphicCardController extends Controller
{
    private $graphicCardService;

    /**
     * Constructor for GraphicCardController.
     * Initializes the GraphicCardService.
     */
    public function __construct()
    {
        parent::__construct(); // Call the parent constructor to set up headers
        $this->graphicCardService = new GraphicCardService(); // Instantiate the GraphicCardService
    }

    /**
     * Handles retrieving all graphic cards.
     * Route: GET /api/graphic-cards
     */
    public function index()
    {
        $graphicCards = $this->graphicCardService->getAllGraphicCards();

        if (!empty($graphicCards)) {
            $graphicCards_arr = [];
            foreach ($graphicCards as $card) {
                // Ensure price is formatted correctly and add full image URL for frontend
                $graphicCards_arr[] = [
                    "id" => $card->id,
                    "name" => $card->name,
                    "brand_id" => $card->brand_id,
                    "gpu_model" => $card->gpu_model,
                    "vram_gb" => (int)$card->vram_gb,
                    "interface" => $card->interface,
                    "boost_clock_mhz" => $card->boost_clock_mhz !== null ? (int)$card->boost_clock_mhz : null,
                    "cuda_cores" => $card->cuda_cores !== null ? (int)$card->cuda_cores : null,
                    "stream_processors" => $card->stream_processors !== null ? (int)$card->stream_processors : null,
                    "price" => (float)$card->price, // Cast to float for consistency
                    "stock" => (int)$card->stock,
                    "description" => $card->description,
                    // Prepend full domain for image_url if it exists, otherwise provide null
                    "image_url" => $card->image_url ? $card->image_url : null,
                    "brand_name" => $card->brand_name, // From join
                    "manufacturer_name" => $card->manufacturer_name // From join
                ];
            }
            $this->jsonResponse($graphicCards_arr, 200);
        } else {
            $this->jsonResponse(["message" => "No graphic cards found."], 404);
        }
    }

    /**
     * Handles retrieving a single graphic card by ID.
     * Route: GET /api/graphic-cards/{id}
     * @param int $id The ID of the graphic card to retrieve.
     */
    public function show(int $id)
    {
        $graphicCard = $this->graphicCardService->getGraphicCardById($id);

        if ($graphicCard) {
            $this->jsonResponse([
                "id" => $graphicCard->id,
                "name" => $graphicCard->name,
                "brand_id" => $graphicCard->brand_id,
                "gpu_model" => $graphicCard->gpu_model,
                "vram_gb" => (int)$graphicCard->vram_gb,
                "interface" => $graphicCard->interface,
                "boost_clock_mhz" => $graphicCard->boost_clock_mhz !== null ? (int)$graphicCard->boost_clock_mhz : null,
                "cuda_cores" => $graphicCard->cuda_cores !== null ? (int)$graphicCard->cuda_cores : null,
                "stream_processors" => $graphicCard->stream_processors !== null ? (int)$graphicCard->stream_processors : null,
                "price" => (float)$graphicCard->price,
                "stock" => (int)$graphicCard->stock,
                "description" => $graphicCard->description,
                "image_url" => $graphicCard->image_url ? $graphicCard->image_url : null,
                "brand_name" => $graphicCard->brand_name,
                "manufacturer_name" => $graphicCard->manufacturer_name
            ], 200);
        } else {
            $this->errorResponse("Graphic card not found.", 404);
        }
    }

    /**
     * Handles creating a new graphic card.
     * Route: POST /api/graphic-cards
     * Data comes from $_POST and $_FILES for multipart/form-data.
     */
    public function store()
    {
        // For multipart/form-data, data is in $_POST and files in $_FILES
        $data = $_POST;
        $file = $_FILES['image'] ?? null; // Get the uploaded file, if any

        // Basic validation: Check for essential fields
        if (empty($data['name']) || empty($data['brand_id']) || empty($data['gpu_model']) ||
            empty($data['vram_gb']) || empty($data['interface']) || empty($data['price']) ||
            !isset($data['stock'])) // stock can be 0, so check with isset
        {
            $this->errorResponse("Missing required fields: name, brand_id, gpu_model, vram_gb, interface, price, stock.", 400);
        }

        // Delegate to service to handle creation logic, passing both data and file
        $newGraphicCard = $this->graphicCardService->createGraphicCard($data, $file);

        if ($newGraphicCard) {
            $this->jsonResponse([
                "message" => "Graphic card created successfully.",
                "graphic_card" => [
                    "id" => $newGraphicCard->id,
                    "name" => $newGraphicCard->name,
                    "brand_id" => $newGraphicCard->brand_id,
                    "gpu_model" => $newGraphicCard->gpu_model,
                    "vram_gb" => (int)$newGraphicCard->vram_gb,
                    "interface" => $newGraphicCard->interface,
                    "boost_clock_mhz" => $newGraphicCard->boost_clock_mhz !== null ? (int)$newGraphicCard->boost_clock_mhz : null,
                    "cuda_cores" => $newGraphicCard->cuda_cores !== null ? (int)$newGraphicCard->cuda_cores : null,
                    "stream_processors" => $newGraphicCard->stream_processors !== null ? (int)$newGraphicCard->stream_processors : null,
                    "price" => (float)$newGraphicCard->price,
                    "stock" => (int)$newGraphicCard->stock,
                    "description" => $newGraphicCard->description,
                    "image_url" => $newGraphicCard->image_url // This will be the path returned by the uploader
                ]
            ], 201);
        } else {
            $this->errorResponse("Failed to create graphic card. Check data validity or internal server logs (e.g., image upload issues).", 500); // 500 for generic failure, includes image upload
        }
    }

    /**
     * Handles updating an existing graphic card.
     * Route: PUT /api/graphic-cards/{id}
     * Data can come from $_POST (multipart/form-data) or php://input (application/json).
     * We need to handle both.
     * @param int $id The ID of the graphic card to update.
     */
    public function update(int $id)
    {
        // Check Content-Type to determine how to get input
        $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
        $data = [];

        // If it's multipart/form-data, use $_POST and $_FILES
        if (strpos($contentType, 'multipart/form-data') !== false) {
            $data = $_POST;
            $file = $_FILES['image'] ?? null; // Get the uploaded file, if any
            error_log("GraphicCardController: PUT request received as multipart/form-data.");
        } else {
            // Otherwise, assume application/json and use getJsonInput
            $data = $this->getJsonInput();
            $file = null;
            error_log("GraphicCardController: PUT request received as JSON.");
        }


        // Basic validation: Ensure some data is provided for update
        if (empty($data) && empty($file)) {
            $this->errorResponse("No data provided for graphic card update.", 400);
        }

        // Delegate to service to handle update logic, passing data and file
        $success = $this->graphicCardService->updateGraphicCard($id, $data, $file);

        if ($success) {
            // Fetch updated data to return to client
            $updatedGraphicCard = $this->graphicCardService->getGraphicCardById($id);
            $this->jsonResponse([
                "message" => "Graphic card updated successfully.",
                "graphic_card" => [
                    "id" => $updatedGraphicCard->id,
                    "name" => $updatedGraphicCard->name,
                    "brand_id" => $updatedGraphicCard->brand_id,
                    "gpu_model" => (string)$updatedGraphicCard->gpu_model, // Explicitly cast to string for consistency
                    "vram_gb" => (int)$updatedGraphicCard->vram_gb,
                    "interface" => (string)$updatedGraphicCard->interface, // Explicitly cast to string for consistency
                    "boost_clock_mhz" => $updatedGraphicCard->boost_clock_mhz !== null ? (int)$updatedGraphicCard->boost_clock_mhz : null,
                    "cuda_cores" => $updatedGraphicCard->cuda_cores !== null ? (int)$updatedGraphicCard->cuda_cores : null,
                    "stream_processors" => $updatedGraphicCard->stream_processors !== null ? (int)$updatedGraphicCard->stream_processors : null,
                    "price" => (float)$updatedGraphicCard->price,
                    "stock" => (int)$updatedGraphicCard->stock,
                    "description" => (string)$updatedGraphicCard->description, // Explicitly cast to string for consistency
                    "image_url" => $updatedGraphicCard->image_url // Path (possibly updated) returned by service
                ]
            ], 200);
        } else {
            $this->errorResponse("Failed to update graphic card or graphic card not found. Check server logs for details.", 500); // Changed to 500 for generic server-side failure
        }
    }

    /**
     * Handles deleting a graphic card.
     * Route: DELETE /api/graphic-cards/{id}
     * @param int $id The ID of the graphic card to delete.
     */
    public function destroy(int $id)
    {
        $success = $this->graphicCardService->deleteGraphicCard($id);

        if ($success) {
            $this->jsonResponse(["message" => "Graphic card deleted successfully."], 200);
        } else {
            $this->errorResponse("Failed to delete graphic card or graphic card not found. Check server logs for details.", 404);
        }
    }
}
