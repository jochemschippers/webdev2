<?php
// app/services/BrandService.php

namespace App\Services;

require_once dirname(__FILE__) . '/../repositories/BrandRepository.php';
require_once dirname(__FILE__) . '/../models/Brand.php';

use App\Repositories\BrandRepository;
use App\Models\Brand;

class BrandService {
    private $brandRepository;

    public function __construct() {
        $this->brandRepository = new BrandRepository();
    }

    public function getAllBrands() {
        $brandsData = $this->brandRepository->getAll();
        $brands = [];
        foreach ($brandsData as $data) {
            $brands[] = new Brand($data);
        }
        return $brands;
    }
    public function getBrandById(int $id) {
        $brandData = $this->brandRepository->getById($id);
        if ($brandData) {
            return new Brand($brandData);
        }
        return false;
    }

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

    public function updateBrand(int $id, string $name, int $manufacturerId) {
        if (empty($name) || empty($manufacturerId)) {
            return false; // Name and Manufacturer ID cannot be empty
        }
        return $this->brandRepository->update($id, ['name' => $name, 'manufacturer_id' => $manufacturerId]);
    }

    public function deleteBrand(int $id) {
        return $this->brandRepository->delete($id);
    }
}
?>
