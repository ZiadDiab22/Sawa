<?php

namespace App\Services\Ride;

use App\Repositories\Ride\RideInfoRepository;
use \Illuminate\Support\Collection;

class RideInfoService
{
    public function __construct(
        private readonly RideInfoRepository $repository
    )
    {
    }

    public function list(string $status):Collection
    {
        return $this->repository->getRides($status);
    }

    public function getRideDetails(int $rideId)
    {
        return $this->repository->findWithDetails($rideId);
    }
}
