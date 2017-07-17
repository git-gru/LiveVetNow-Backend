<?php

namespace App\Traits;
use App\Locations;

trait CommonTrait {
    public function getAllLocations() {
        // Get all cities list
        $brands = Brand::all();

        return $brands;
    }
}