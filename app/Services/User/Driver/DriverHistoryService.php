<?php

namespace App\Services\User\Driver;

use App\Http\Resources\Ride\DriverCancelledRideResource;
use App\Http\Resources\Ride\DriverRideRequestResource;
use App\Repositories\Driver\DriverHistoryRepository;

class DriverHistoryService
{
  public function __construct(
    protected DriverHistoryRepository $repo
  ) {}

  public function handle(int $driverId)
  {
    return [
      'accepted_requests' => DriverRideRequestResource::collection(
        $this->repo->acceptedRequests($driverId)
      ),
      'skipped_requests' => DriverRideRequestResource::collection(
        $this->repo->skippedRequests($driverId)
      ),
      'cancelled_rides' => DriverCancelledRideResource::collection(
        $this->repo->cancelledRides($driverId)
      ),
    ];
  }
}
