<?php
// app/services/ManufacturerService.php

namespace App\Services;

require_once dirname(__FILE__) . '/../repositories/ManufacturerRepository.php';
require_once dirname(__FILE__) . '/../models/Manufacturer.php';

use App\Repositories\ManufacturerRepository;
use App\Models\Manufacturer;

class ManufacturerService {
    private $manufacturerRepository;

    /**
     * Constructor for ManufacturerService.
     * Initializes the ManufacturerRepository.
     */
    public function __construct() {
        $this->manufacturerRepository = new ManufacturerRepository();
    }

    /**
     * Retrieves all manufacturers.
     * @return array An array of Manufacturer model instances.
     */
    public function getAllManufacturers() {
        $manufacturersData = $this->manufacturerRepository->getAll();
        $manufacturers = [];
        foreach ($manufacturersData as $data) {
            $manufacturers[] = new Manufacturer($data);
        }
        return $manufacturers;
    }

    /**
     * Retrieves a single manufacturer by ID.
     * @param int $id
     * @return Manufacturer|false Returns a Manufacturer model instance if found, false otherwise.
     */
    public function getManufacturerById(int $id) {
        $manufacturerData = $this->manufacturerRepository->getById($id);
        if ($manufacturerData) {
            return new Manufacturer($manufacturerData);
        }
        return false;
    }

    /**
     * Creates a new manufacturer.
     * @param string $name
     * @return Manufacturer|false Returns the created Manufacturer object on success, false on failure.
     */
    public function createManufacturer(string $name) {
        if (empty($name)) {
            return false; // Name cannot be empty
        }
        $manufacturerId = $this->manufacturerRepository->create(['name' => $name]);
        if ($manufacturerId) {
            return $this->getManufacturerById($manufacturerId);
        }
        return false;
    }

    /**
     * Updates an existing manufacturer.
     * @param int $id
     * @param string $name
     * @return bool True on success, false on failure.
     */
    public function updateManufacturer(int $id, string $name) {
        if (empty($name)) {
            return false; // Name cannot be empty
        }
        return $this->manufacturerRepository->update($id, ['name' => $name]);
    }

    /**
     * Deletes a manufacturer.
     * @param int $id
     * @return bool True on success, false on failure.
     */
    public function deleteManufacturer(int $id) {
        return $this->manufacturerRepository->delete($id);
    }
}
?>
