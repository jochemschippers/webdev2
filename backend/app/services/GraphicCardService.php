<?php
// app/services/GraphicCardService.php

namespace App\Services;

require_once dirname(__FILE__) . '/../repositories/GraphicCardRepository.php';
require_once dirname(__FILE__) . '/../models/GraphicCard.php';
require_once dirname(__FILE__) . '/../utils/ImageUploader.php'; // NEW: Include ImageUploader

use App\Repositories\GraphicCardRepository;
use App\Models\GraphicCard;
use App\Utils\ImageUploader; // NEW: Use ImageUploader class

class GraphicCardService
{
    private $graphicCardRepository;
    private $imageUploader; // NEW: Declare ImageUploader instance

    /**
     * Constructor for GraphicCardService.
     * Initializes the GraphicCardRepository and ImageUploader.
     */
    public function __construct()
    {
        $this->graphicCardRepository = new GraphicCardRepository();
        $this->imageUploader = new ImageUploader(); // NEW: Initialize ImageUploader
    }

    /**
     * Retrieves all graphic cards.
     * @return array An array of GraphicCard model instances.
     */
    public function getAllGraphicCards()
    {
        $graphicCardsData = $this->graphicCardRepository->getAll();
        $graphicCards = [];
        foreach ($graphicCardsData as $data) {
            $graphicCards[] = new GraphicCard($data);
        }
        return $graphicCards;
    }

    /**
     * Retrieves a single graphic card by ID.
     * @param int $id
     * @return GraphicCard|false Returns a GraphicCard model instance if found, false otherwise.
     */
    public function getGraphicCardById(int $id)
    {
        $graphicCardData = $this->graphicCardRepository->getById($id);
        if ($graphicCardData) {
            return new GraphicCard($graphicCardData);
        }
        return false;
    }

    /**
     * Creates a new graphic card, handling image upload if a file is provided.
     *
     * @param array $data Associative array of graphic card data (typically from $_POST).
     * @param array $fileData Associative array of file data (typically from $_FILES['image']).
     * @return GraphicCard|false Returns the created GraphicCard object on success, false on failure.
     */
    public function createGraphicCard(array $data, array $fileData = [])
    {
        // Add image_url to $data if an image is uploaded
        $data['image_url'] = null; // Default to null

        if (!empty($fileData) && $fileData['error'] === UPLOAD_ERR_OK) {
            $uploadedImagePath = $this->imageUploader->uploadImage($fileData);
            if ($uploadedImagePath === false) {
                error_log("GraphicCardService: Image upload failed during creation.");
                return false; // Image upload failed
            }
            $data['image_url'] = $uploadedImagePath;
            error_log("GraphicCardService: Image uploaded. Path: " . $data['image_url']);
        } else {
             error_log("GraphicCardService: No image file provided or error for creation.");
        }


        // Basic validation for required fields
        if (empty($data['name']) || empty($data['brand_id']) || empty($data['gpu_model']) ||
            empty($data['vram_gb']) || empty($data['interface']) || empty($data['price']) ||
            !isset($data['stock'])) { // Stock can be 0, so check with isset
            error_log("GraphicCardService: Missing required fields for creation.");
            return false;
        }

        $graphicCardId = $this->graphicCardRepository->create($data);

        if ($graphicCardId) {
            return $this->getGraphicCardById($graphicCardId);
        }
        error_log("GraphicCardService: Database creation failed for graphic card.");
        return false;
    }

    /**
     * Updates an existing graphic card, handling image upload/replacement/deletion.
     *
     * @param int $id The ID of the graphic card to update.
     * @param array $data Associative array of graphic card data (typically from $_POST).
     * @param array $fileData Associative array of file data (typically from $_FILES['image']).
     * @return bool True on success, false on failure.
     */
    public function updateGraphicCard(int $id, array $data, array $fileData = [])
    {
        // Fetch current graphic card to get its existing image_url
        $currentGraphicCard = $this->getGraphicCardById($id);
        if (!$currentGraphicCard) {
            error_log("GraphicCardService: Graphic card not found for update (ID: " . $id . ").");
            return false; // Graphic card not found
        }

        $oldImageUrl = $currentGraphicCard->image_url;
        $newImageUrl = $oldImageUrl; // Start with the old URL

        // Case 1: A new image file is uploaded
        if (!empty($fileData) && $fileData['error'] === UPLOAD_ERR_OK) {
            $uploadedImagePath = $this->imageUploader->uploadImage($fileData);
            if ($uploadedImagePath === false) {
                error_log("GraphicCardService: New image upload failed during update for ID: " . $id);
                // If new upload fails, retain old image or set to null based on original intent.
                // For now, we'll fail the update entirely if image upload fails.
                return false;
            }
            $newImageUrl = $uploadedImagePath;
            // If a new image is successfully uploaded, delete the old one if it existed.
            if ($oldImageUrl) {
                $this->imageUploader->deleteImage($oldImageUrl);
                error_log("GraphicCardService: Old image deleted during update for ID: " . $id . ". Path: " . $oldImageUrl);
            }
            error_log("GraphicCardService: New image uploaded and old deleted for ID: " . $id . ". New path: " . $newImageUrl);
        }
        // Case 2: Frontend explicitly sends 'image_url' as null/empty string,
        // indicating the existing image should be removed without a new upload.
        // This handles explicit image removal from the frontend.
        elseif (array_key_exists('image_url', $data) && empty($data['image_url'])) {
            if ($oldImageUrl) {
                $this->imageUploader->deleteImage($oldImageUrl);
                error_log("GraphicCardService: Existing image explicitly removed for ID: " . $id . ". Path: " . $oldImageUrl);
            }
            $newImageUrl = null; // Set to null in database
        }
        // Case 3: No new file uploaded, and image_url not explicitly nullified.
        // This implies retaining the existing image. $newImageUrl already holds $oldImageUrl.

        // Ensure image_url in data reflects the outcome of image handling
        $data['image_url'] = $newImageUrl;

        // Ensure basic validation for required fields (can be partial update logic later)
        // For now, assume all fields might be provided or validation happens here.
        if (empty($data['name']) || empty($data['brand_id']) || empty($data['gpu_model']) ||
            empty($data['vram_gb']) || empty($data['interface']) || empty($data['price']) ||
            !isset($data['stock']))
        {
            error_log("GraphicCardService: Missing required fields for update (ID: " . $id . ").");
            return false;
        }

        $success = $this->graphicCardRepository->update($id, $data);

        if (!$success) {
            error_log("GraphicCardService: Database update failed for graphic card ID: " . $id);
        }
        return $success;
    }

    /**
     * Deletes a graphic card and its associated image from the server.
     * @param int $id
     * @return bool True on success, false on failure.
     */
    public function deleteGraphicCard(int $id)
    {
        // First, get the graphic card to retrieve its image URL
        $graphicCard = $this->getGraphicCardById($id);

        if (!$graphicCard) {
            error_log("GraphicCardService: Graphic card not found for deletion (ID: " . $id . ").");
            return false; // Graphic card not found
        }

        // Delete the record from the database
        $dbDeleteSuccess = $this->graphicCardRepository->delete($id);

        if ($dbDeleteSuccess) {
            // If database record is deleted, attempt to delete the image file
            if ($graphicCard->image_url) {
                $imageDeleteSuccess = $this->imageUploader->deleteImage($graphicCard->image_url);
                if (!$imageDeleteSuccess) {
                    error_log("GraphicCardService: WARNING: Graphic card record deleted from DB, but failed to delete image file: " . $graphicCard->image_url);
                    // Decide if you want to return true here (DB delete was main goal) or false (full cleanup failed).
                    // For now, we'll return true if DB delete succeeded, logging the image error.
                } else {
                    error_log("GraphicCardService: Image file deleted for ID: " . $id . ". Path: " . $graphicCard->image_url);
                }
            }
            error_log("GraphicCardService: Graphic card deleted successfully from DB (ID: " . $id . ").");
            return true;
        }
        error_log("GraphicCardService: Failed to delete graphic card record from DB (ID: " . $id . ").");
        return false;
    }
}
