<?php

namespace App\Services\Ride;

use App\Events\NewRideRequestCreated;
use App\Repositories\Driver\DriverLocationRepository;

class RideBroadcastService
{
  public function __construct(
    private DriverLocationRepository $drivers
  ) {}

  public function sendToNearbyDrivers($rideRequest)
  {
    $drivers = $this->drivers->nearbyActiveDrivers(
      $rideRequest->pickup_lat,
      $rideRequest->pickup_lng
    );

    foreach ($drivers as $driver) {
      broadcast(
        new NewRideRequestCreated($rideRequest, $driver->driver_id)
      );
    }
  }
}
