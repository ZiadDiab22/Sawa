<?php

namespace App\Repositories\Ride;

use App\Models\RideStatusHistory;

class RideStatusHistoryRepository
{
  public function create(
    int $rideId,
    ?string $oldStatus,
    string $newStatus,
    string $changedByType,
    int $changedById
  ): RideStatusHistory {
    return RideStatusHistory::create([
      'ride_id' => $rideId,
      'old_status' => $oldStatus,
      'new_status' => $newStatus,
      'changed_by_type' => $changedByType,
      'changed_by_id' => $changedById,
    ]);
  }
}
