<?php
// app/controllers/BrandController.php

namespace App\Controllers; 

require_once __DIR__ . '/Controller.php'; 
require_once dirname(__FILE__) . '/../services/BrandService.php'; 

use App\Services\BrandService; 

class BrandController extends Controller
{
    private $brandService;
    public function __construct()
    {
        parent::__construct(); // Call the parent constructor to set up headers
        $this->brandService = new BrandService(); // Instantiate the BrandService
    }
    public function index()
    {
        $brands = $this->brandService->getAllBrands();

        if (!empty($brands)) {
            $brands_arr = [];
            foreach ($brands as $brand) {
                $brands_arr[] = [
                    "id" => $brand->id,
                    "name" => $brand->name,
                    "manufacturer_id" => $brand->manufacturer_id,
                    "manufacturer_name" => $brand->manufacturer_name 
                ];
            }
            $this->jsonResponse($brands_arr, 200);
        } else {
            $this->jsonResponse(["message" => "No brands found."], 404);
        }
    }
    public function show(int $id)
    {
        $brand = $this->brandService->getBrandById($id);

        if ($brand) {
            $this->jsonResponse([
                "id" => $brand->id,
                "name" => $brand->name,
                "manufacturer_id" => $brand->manufacturer_id,
                "manufacturer_name" => $brand->manufacturer_name
            ], 200);
        } else {
            $this->errorResponse("Brand not found.", 404);
        }
    }

    public function store()
    {
        $data = $this->getJsonInput();

        if (empty($data['name']) || empty($data['manufacturer_id'])) {
            $this->errorResponse("Brand name and manufacturer_id are required.", 400);
        }

        $name = $data['name'];
        $manufacturerId = (int)$data['manufacturer_id']; // Ensure it's an integer

        $newBrand = $this->brandService->createBrand($name, $manufacturerId);

        if ($newBrand) {
            $this->jsonResponse([
                "message" => "Brand created successfully.",
                "brand" => [
                    "id" => $newBrand->id,
                    "name" => $newBrand->name,
                    "manufacturer_id" => $newBrand->manufacturer_id,
                    "manufacturer_name" => $newBrand->manufacturer_name
                ]
            ], 201);
        } else {
            $this->errorResponse("Failed to create brand. Name might already exist or manufacturer_id is invalid.", 409);
        }
    }

    public function update(int $id)
    {
        $data = $this->getJsonInput();

        if (empty($data['name']) || empty($data['manufacturer_id'])) {
            $this->errorResponse("Brand name and manufacturer_id are required for update.", 400);
        }

        $name = $data['name'];
        $manufacturerId = (int)$data['manufacturer_id'];
        $success = $this->brandService->updateBrand($id, $name, $manufacturerId);

        if ($success) {
            // Fetch updated data to return to client
            $updatedBrand = $this->brandService->getBrandById($id);
            $this->jsonResponse([
                "message" => "Brand updated successfully.",
                "brand" => [
                    "id" => $updatedBrand->id,
                    "name" => $updatedBrand->name,
                    "manufacturer_id" => $updatedBrand->manufacturer_id,
                    "manufacturer_name" => $updatedBrand->manufacturer_name
                ]
            ], 200);
        } else {
            $this->errorResponse("Failed to update brand or brand not found.", 404);
        }
    }
    public function destroy(int $id)
    {
        $success = $this->brandService->deleteBrand($id);

        if ($success) {
            $this->jsonResponse(["message" => "Brand deleted successfully."], 200);
        } else {
            $this->errorResponse("Failed to delete brand or brand not found.", 404);
        }
    }
}
?>
