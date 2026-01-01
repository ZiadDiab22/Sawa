<?php

namespace App\Repositories\Ride;

use App\Models\Ride;

class RideRepository
{
  public function findForDriver(int $rideId, int $driverId)
  {
    return Ride::where('id', $rideId)
      ->where('driver_id', $driverId)
      ->lockForUpdate()
      ->first();
  }

  public function updateStatus(Ride $ride, string $status): void
  {
    $ride->update(['status' => $status]);
  }
}
