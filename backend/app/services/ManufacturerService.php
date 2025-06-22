<?php
// app/services/ManufacturerService.php

namespace App\Services;

require_once dirname(__FILE__) . '/../repositories/ManufacturerRepository.php';
require_once dirname(__FILE__) . '/../models/Manufacturer.php';

use App\Repositories\ManufacturerRepository;
use App\Models\Manufacturer;

class ManufacturerService {
    private $manufacturerRepository;

    public function __construct() {
        $this->manufacturerRepository = new ManufacturerRepository();
    }
    public function getAllManufacturers() {
        $manufacturersData = $this->manufacturerRepository->getAll();
        $manufacturers = [];
        foreach ($manufacturersData as $data) {
            $manufacturers[] = new Manufacturer($data);
        }
        return $manufacturers;
    }
    public function getManufacturerById(int $id) {
        $manufacturerData = $this->manufacturerRepository->getById($id);
        if ($manufacturerData) {
            return new Manufacturer($manufacturerData);
        }
        return false;
    }
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

    public function updateManufacturer(int $id, string $name) {
        if (empty($name)) {
            return false; // Name cannot be empty
        }
        return $this->manufacturerRepository->update($id, ['name' => $name]);
    }
    public function deleteManufacturer(int $id) {
        return $this->manufacturerRepository->delete($id);
    }
}
?>
