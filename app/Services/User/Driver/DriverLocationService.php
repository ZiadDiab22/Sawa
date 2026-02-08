<?php

namespace App\Services\User\Driver;

use App\Events\DriverLocationUpdated;
use App\Models\Ride;
use App\Repositories\Driver\DriverLocationRepository;

class DriverLocationService
{
    public function __construct(private DriverLocationRepository $repository)
    {
    }

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
        $ride = Ride::query()
            ->whereIn('status', ['driver_on_way', 'on_going'])
            ->where('driver_id', $driverId)
            ->first();

        if ($ride) {
            broadcast(new DriverLocationUpdated($lat, $lng, $ride))->toOthers();
        }

        return $this->repository->createOrUpdate($driverId, $lat, $lng);
    }
}
