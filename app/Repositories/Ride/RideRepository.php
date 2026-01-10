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

  public function findForUser(int $id, int $userId): ?Ride
  {
    return Ride::where('id', $id)
      ->where('user_id', $userId)
      ->lockForUpdate()
      ->first();
  }

  public function updateStatus(Ride $ride, string $status): void
  {
    $ride->update(['status' => $status]);
  }
}
