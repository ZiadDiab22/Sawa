<?php

namespace App\Services\User\Driver;

use App\Repositories\Driver\DriverLocationRepository;

class DriverLocationService
{
    public function __construct(private DriverLocationRepository $repository) {}

    public function getAllLocations()
    {
        return $this->repository->all();
    }

    public function show(int $id)
    {
        return $this->repository->show($id);
    }

    public function storeOrUpdateLocation(int $driverId, float $lat, float $lng)
    {
        return $this->repository->createOrUpdate($driverId, $lat, $lng);
    }
}
