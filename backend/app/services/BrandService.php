<?php
// app/services/BrandService.php

namespace App\Services;

require_once dirname(__FILE__) . '/../repositories/BrandRepository.php';
require_once dirname(__FILE__) . '/../models/Brand.php';

use App\Repositories\BrandRepository;
use App\Models\Brand;

class BrandService {
    private $brandRepository;

    /**
     * Constructor for BrandService.
     * Initializes the BrandRepository.
     */
    public function __construct() {
        $this->brandRepository = new BrandRepository();
    }

    /**
     * Retrieves all brands.
     * @return array An array of Brand model instances.
     */
    public function getAllBrands() {
        $brandsData = $this->brandRepository->getAll();
        $brands = [];
        foreach ($brandsData as $data) {
            $brands[] = new Brand($data);
        }
        return $brands;
    }

    /**
     * Retrieves a single brand by ID.
     * @param int $id
     * @return Brand|false Returns a Brand model instance if found, false otherwise.
     */
    public function getBrandById(int $id) {
        $brandData = $this->brandRepository->getById($id);
        if ($brandData) {
            return new Brand($brandData);
        }
        return false;
    }

    /**
     * Creates a new brand.
     * @param string $name
     * @param int $manufacturerId
     * @return Brand|false Returns the created Brand object on success, false on failure.
     */
    public function createBrand(string $name, int $manufacturerId) {
        if (empty($name) || empty($manufacturerId)) {
            return false; // Name and Manufacturer ID cannot be empty
        }
        $brandId = $this->brandRepository->create(['name' => $name, 'manufacturer_id' => $manufacturerId]);
        if ($brandId) {
            return $this->getBrandById($brandId);
        }
        return false;
    }

    /**
     * Updates an existing brand.
     * @param int $id
     * @param string $name
     * @param int $manufacturerId
     * @return bool True on success, false on failure.
     */
    public function updateBrand(int $id, string $name, int $manufacturerId) {
        if (empty($name) || empty($manufacturerId)) {
            return false; // Name and Manufacturer ID cannot be empty
        }
        return $this->brandRepository->update($id, ['name' => $name, 'manufacturer_id' => $manufacturerId]);
    }

    /**
     * Deletes a brand.
     * @param int $id
     * @return bool True on success, false on failure.
     */
    public function deleteBrand(int $id) {
        return $this->brandRepository->delete($id);
    }
}
?>
