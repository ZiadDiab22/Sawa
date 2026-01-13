<?php

namespace App\Services\User\Driver;

use App\Repositories\DriverRatingRepository;
use App\Repositories\Ride\ProfitRepository;
use App\Repositories\Ride\RideRepository;
use Carbon\Carbon;

class DriverDashboardService
{
  public function __construct(
    private RideRepository          $rides,
    private DriverRatingRepository  $ratings,
    private ProfitRepository  $profits,
  ) {}

  public function handle(int $driverId): array
  {
    $today = Carbon::today();

    return [
      'completed_rides_count' => $this->rides
        ->countCompletedByDriverForDate($driverId, $today),

      'average_rating' => $this->ratings
        ->averageForDriverByDate($driverId, $today),

      'total_profit' => $this->profits
        ->sumForDriverByDate($driverId, $today),
    ];
  }

  public function stats(int $driverId, string $date): array
  {
    $rides = $this->rides->dailyRides($driverId, $date);

    $totalProfit = $this->profits->sumForDate($driverId, $date);

    return [
      'total_count'     => $rides->count(),
      'total_profit'    => $totalProfit,
      'rides'           => $rides,
    ];
  }
}
