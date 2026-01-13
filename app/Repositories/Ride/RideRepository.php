<?php

namespace App\Repositories\Ride;

use App\Models\Ride;
use Carbon\Carbon;

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

  public function countCompletedByDriverForDate(int $driverId, Carbon $date): int
  {
    return Ride::query()
      ->where('driver_id', $driverId)
      ->where('status', 'completed')
      ->whereDate('created_at', $date)
      ->count();
  }

  public function dailyRides(int $driverId, string $date)
  {
    return Ride::query()
      ->where('driver_id', $driverId)
      ->whereDate('created_at', $date)
      ->whereIn('status', ['completed', 'cancelled'])
      ->with(['profit'])
      ->get()
      ->map(function ($ride) {
        return [
          'ride_id' => $ride->id,
          'status'  => $ride->status,
          'profit'  => (float) optional($ride->profit)->amount ?? 0,
          'created_at' => $ride->created_at,
        ];
      });
  }
}
