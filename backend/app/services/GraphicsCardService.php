<?php
// app/services/GraphicCardService.php

namespace App\Services;

require_once dirname(__FILE__) . '/../repositories/GraphicCardRepository.php';
require_once dirname(__FILE__) . '/../models/GraphicCard.php';

use App\Repositories\GraphicCardRepository;
use App\Models\GraphicCard;

class GraphicCardService {
    private $graphicCardRepository;

    /**
     * Constructor for GraphicCardService.
     * Initializes the GraphicCardRepository.
     */
    public function __construct() {
        $this->graphicCardRepository = new GraphicCardRepository();
    }

    /**
     * Retrieves all graphic cards.
     * @return array An array of GraphicCard model instances.
     */
    public function getAllGraphicCards() {
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
    public function getGraphicCardById(int $id) {
        $graphicCardData = $this->graphicCardRepository->getById($id);
        if ($graphicCardData) {
            return new GraphicCard($graphicCardData);
        }
        return false;
    }

    /**
     * Creates a new graphic card.
     * @param array $data Associative array of graphic card properties.
     * @return GraphicCard|false Returns the created GraphicCard object on success, false on failure.
     */
    public function createGraphicCard(array $data) {
        // Basic validation for required fields (can be more extensive)
        if (empty($data['name']) || empty($data['brand_id']) || empty($data['gpu_model']) || empty($data['vram_gb']) || empty($data['price']) || empty($data['stock'])) {
            return false;
        }

        $graphicCardId = $this->graphicCardRepository->create($data);
        if ($graphicCardId) {
            return $this->getGraphicCardById($graphicCardId);
        }
        return false;
    }

    /**
     * Updates an existing graphic card.
     * @param int $id The ID of the graphic card to update.
     * @param array $data Associative array of graphic card properties to update.
     * @return bool True on success, false on failure.
     */
    public function updateGraphicCard(int $id, array $data) {
        // Ensure ID is valid and graphic card exists
        if (!$this->getGraphicCardById($id)) {
            return false; // Graphic card not found
        }
        // Basic validation for required fields in update (can be more extensive)
        if (empty($data['name']) || empty($data['brand_id']) || empty($data['gpu_model']) || empty($data['vram_gb']) || empty($data['price']) || empty($data['stock'])) {
            return false;
        }
        return $this->graphicCardRepository->update($id, $data);
    }

    /**
     * Deletes a graphic card.
     * @param int $id The ID of the graphic card to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteGraphicCard(int $id) {
        // Ensure graphic card exists before attempting to delete
        if (!$this->getGraphicCardById($id)) {
            return false; // Graphic card not found
        }
        return $this->graphicCardRepository->delete($id);
    }
}
?>
