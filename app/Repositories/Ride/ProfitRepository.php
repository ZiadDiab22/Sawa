<?php

namespace App\Repositories\Ride;

use App\Models\CompanyCommission;
use App\Models\DriverProfit;
use App\Models\Ride;

class ProfitRepository
{
  public function createDriverProfit(int $driverId, int $rideId, float $amount): void
  {
    DriverProfit::create([
      'user_id' => $driverId,
      'ride_id' => $rideId,
      'amount'  => $amount,
    ]);
  }

  public function createCompanyCommission(int $userId, int $rideId, float $amount): void
  {
    CompanyCommission::create([
      'user_id'   => $userId,
      'ride_id'   => $rideId,
      'amount'    => $amount,
    ]);
  }

  public function driverTotalCompanyDebt(int $driverId): float
  {
    return CompanyCommission::where('user_id', $driverId)
      ->where('is_collected', false)
      ->sum('amount');
  }
}
