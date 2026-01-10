<?php

namespace App\Repositories\Driver;

use App\Models\Ride;
use App\Models\RideRequestResponse;

class DriverHistoryRepository
{
  public function acceptedRequests(int $driverId)
  {
    return RideRequestResponse::with([
      'rideRequest.user:id,name',
      'rideRequest.vehicleType:id,name',
    ])
      ->where('driver_id', $driverId)
      ->where('status', 'accepted')
      ->latest()
      ->limit(100)
      ->get();
  }

  public function skippedRequests(int $driverId)
  {
    return RideRequestResponse::with([
      'rideRequest.user:id,name',
      'rideRequest.vehicleType:id,name',
    ])
      ->where('driver_id', $driverId)
      ->where('status', 'skipped')
      ->latest()
      ->limit(100)
      ->get();
  }

  public function cancelledRides(int $driverId)
  {
    return Ride::with([
      'user:id,name',
      'statusHistory' => fn($q) =>
      $q->where('new_status', 'cancelled')->latest(),
    ])
      ->where('driver_id', $driverId)
      ->where('status', 'cancelled')
      ->latest()
      ->limit(100)
      ->get();
  }
}
