<?php
// app/controllers/GraphicCardController.php

namespace App\Controllers; // Use the same namespace as the base Controller

require_once __DIR__ . '/Controller.php'; // Require the base Controller
require_once dirname(__FILE__) . '/../services/GraphicCardService.php'; // Require the GraphicCardService


use App\Services\GraphicCardService; 

class GraphicCardController extends Controller
{
    private $graphicCardService;

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
                $graphicCards_arr[] = [
                    "id" => $card->id,
                    "name" => $card->name,
                    "brand_id" => $card->brand_id,
                    "gpu_model" => $card->gpu_model,
                    "vram_gb" => $card->vram_gb,
                    "interface" => $card->interface,
                    "boost_clock_mhz" => $card->boost_clock_mhz,
                    "cuda_cores" => $card->cuda_cores,
                    "stream_processors" => $card->stream_processors,
                    "price" => $card->price,
                    "stock" => $card->stock,
                    "description" => $card->description,
                    "image_url" => $card->image_url,
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
                "vram_gb" => $graphicCard->vram_gb,
                "interface" => $graphicCard->interface,
                "boost_clock_mhz" => $graphicCard->boost_clock_mhz,
                "cuda_cores" => $graphicCard->cuda_cores,
                "stream_processors" => $graphicCard->stream_processors,
                "price" => $graphicCard->price,
                "stock" => $graphicCard->stock,
                "description" => $graphicCard->description,
                "image_url" => $graphicCard->image_url,
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
     */
    public function store()
    {
        $data = $this->getJsonInput();

        // Basic validation: Check for essential fields
        if (empty($data['name']) || empty($data['brand_id']) || empty($data['gpu_model']) ||
            empty($data['vram_gb']) || empty($data['interface']) || empty($data['price']) ||
            !isset($data['stock'])) // stock can be 0, so check with isset
        {
            $this->errorResponse("Missing required fields: name, brand_id, gpu_model, vram_gb, interface, price, stock.", 400);
        }

        // Delegate to service to handle creation logic
        $newGraphicCard = $this->graphicCardService->createGraphicCard($data);

        if ($newGraphicCard) {
            $this->jsonResponse([
                "message" => "Graphic card created successfully.",
                "graphic_card" => [
                    "id" => $newGraphicCard->id,
                    "name" => $newGraphicCard->name,
                    "brand_id" => $newGraphicCard->brand_id,
                    "gpu_model" => $newGraphicCard->gpu_model,
                    "vram_gb" => $newGraphicCard->vram_gb,
                    "interface" => $newGraphicCard->interface,
                    "boost_clock_mhz" => $newGraphicCard->boost_clock_mhz,
                    "cuda_cores" => $newGraphicCard->cuda_cores,
                    "stream_processors" => $newGraphicCard->stream_processors,
                    "price" => $newGraphicCard->price,
                    "stock" => $newGraphicCard->stock,
                    "description" => $newGraphicCard->description,
                    "image_url" => $newGraphicCard->image_url
                ]
            ], 201);
        } else {
            $this->errorResponse("Failed to create graphic card. Check data validity or internal server logs.", 500); // 500 Internal Server Error for generic failure
        }
    }

    /**
     * Handles updating an existing graphic card.
     * Route: PUT /api/graphic-cards/{id}
     * @param int $id The ID of the graphic card to update.
     */
    public function update(int $id)
    {
        $data = $this->getJsonInput();

        // Basic validation: Check for essential fields for update (can be partial update logic later)
        // For now, assume all fields might be provided or validation happens in service.
        if (empty($data['name']) || empty($data['brand_id']) || empty($data['gpu_model']) ||
            empty($data['vram_gb']) || empty($data['interface']) || empty($data['price']) ||
            !isset($data['stock']))
        {
            $this->errorResponse("Missing required fields for update: name, brand_id, gpu_model, vram_gb, interface, price, stock.", 400);
        }

        $success = $this->graphicCardService->updateGraphicCard($id, $data);

        if ($success) {
            // Fetch updated data to return to client
            $updatedGraphicCard = $this->graphicCardService->getGraphicCardById($id);
            $this->jsonResponse([
                "message" => "Graphic card updated successfully.",
                "graphic_card" => [
                    "id" => $updatedGraphicCard->id,
                    "name" => $updatedGraphicCard->name,
                    "brand_id" => $updatedGraphicCard->brand_id,
                    "gpu_model" => $updatedGraphicCard->gpu_model,
                    "vram_gb" => $updatedGraphicCard->vram_gb,
                    "interface" => $updatedGraphicCard->interface,
                    "boost_clock_mhz" => $updatedGraphicCard->boost_clock_mhz,
                    "cuda_cores" => $updatedGraphicCard->cuda_cores,
                    "stream_processors" => $updatedGraphicCard->stream_processors,
                    "price" => $updatedGraphicCard->price,
                    "stock" => $updatedGraphicCard->stock,
                    "description" => $updatedGraphicCard->description,
                    "image_url" => $updatedGraphicCard->image_url
                ]
            ], 200);
        } else {
            $this->errorResponse("Failed to update graphic card or graphic card not found.", 404);
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
            $this->errorResponse("Failed to delete graphic card or graphic card not found.", 404);
        }
    }
}
?>
