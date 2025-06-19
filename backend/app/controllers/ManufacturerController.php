<?php
// app/controllers/ManufacturerController.php

namespace App\Controllers; // Use the same namespace as the base Controller

require_once __DIR__ . '/Controller.php'; // Require the base Controller
require_once dirname(__FILE__) . '/../services/ManufacturerService.php'; // Require the ManufacturerService

use App\Services\ManufacturerService; // Use the ManufacturerService from its namespace

class ManufacturerController extends Controller
{
    private $manufacturerService;

    /**
     * Constructor for ManufacturerController.
     * Initializes the ManufacturerService.
     */
    public function __construct()
    {
        parent::__construct(); // Call the parent constructor to set up headers
        $this->manufacturerService = new ManufacturerService(); // Instantiate the ManufacturerService
    }

    /**
     * Handles retrieving all manufacturers.
     * Route: GET /api/manufacturers
     */
    public function index()
    {
        $manufacturers = $this->manufacturerService->getAllManufacturers();

        if (!empty($manufacturers)) {
            $manufacturers_arr = [];
            foreach ($manufacturers as $manufacturer) {
                $manufacturers_arr[] = [
                    "id" => $manufacturer->id,
                    "name" => $manufacturer->name
                ];
            }
            $this->jsonResponse($manufacturers_arr, 200);
        } else {
            $this->jsonResponse(["message" => "No manufacturers found."], 404);
        }
    }

    /**
     * Handles retrieving a single manufacturer by ID.
     * Route: GET /api/manufacturers/{id}
     * @param int $id The ID of the manufacturer to retrieve.
     */
    public function show(int $id)
    {
        $manufacturer = $this->manufacturerService->getManufacturerById($id);

        if ($manufacturer) {
            $this->jsonResponse([
                "id" => $manufacturer->id,
                "name" => $manufacturer->name
            ], 200);
        } else {
            $this->errorResponse("Manufacturer not found.", 404);
        }
    }

    /**
     * Handles creating a new manufacturer.
     * Route: POST /api/manufacturers
     */
    public function store()
    {
        $data = $this->getJsonInput();

        if (empty($data['name'])) {
            $this->errorResponse("Manufacturer name is required.", 400);
        }

        $name = $data['name'];
        $newManufacturer = $this->manufacturerService->createManufacturer($name);

        if ($newManufacturer) {
            $this->jsonResponse([
                "message" => "Manufacturer created successfully.",
                "manufacturer" => [
                    "id" => $newManufacturer->id,
                    "name" => $newManufacturer->name
                ]
            ], 201);
        } else {
            $this->errorResponse("Failed to create manufacturer. Name might already exist.", 409);
        }
    }

    /**
     * Handles updating an existing manufacturer.
     * Route: PUT /api/manufacturers/{id}
     * @param int $id The ID of the manufacturer to update.
     */
    public function update(int $id)
    {
        $data = $this->getJsonInput();

        if (empty($data['name'])) {
            $this->errorResponse("Manufacturer name is required for update.", 400);
        }

        $name = $data['name'];
        $success = $this->manufacturerService->updateManufacturer($id, $name);

        if ($success) {
            // Fetch updated data to return to client
            $updatedManufacturer = $this->manufacturerService->getManufacturerById($id);
            $this->jsonResponse([
                "message" => "Manufacturer updated successfully.",
                "manufacturer" => [
                    "id" => $updatedManufacturer->id,
                    "name" => $updatedManufacturer->name
                ]
            ], 200);
        } else {
            $this->errorResponse("Failed to update manufacturer or manufacturer not found.", 404);
        }
    }

    /**
     * Handles deleting a manufacturer.
     * Route: DELETE /api/manufacturers/{id}
     * @param int $id The ID of the manufacturer to delete.
     */
    public function destroy(int $id)
    {
        $success = $this->manufacturerService->deleteManufacturer($id);

        if ($success) {
            $this->jsonResponse(["message" => "Manufacturer deleted successfully."], 200);
        } else {
            $this->errorResponse("Failed to delete manufacturer or manufacturer not found.", 404);
        }
    }
}
?>
