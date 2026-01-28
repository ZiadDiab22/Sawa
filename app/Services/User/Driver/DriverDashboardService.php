<?php

namespace App\Services\User\Driver;

use App\Repositories\DriverRatingRepository;
use App\Repositories\Ride\ProfitRepository;
use App\Repositories\Ride\RideRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

    public function stats(
        int $driverId,
        string $period,
        ?string $from,
        ?string $to,
        bool $forAdmin
    ): array {

        [$fromDate, $toDate] = $this->resolveRange(
            period: $period,
            from: $from,
            to: $to
        );

        $totalProfit = $this->profits->sumBetween($driverId, $fromDate, $toDate);
        if (!$forAdmin) $rides = $this->rides->ridesBetween($driverId, $fromDate, $toDate);
        else $rides = $this->rides->ridesBetweenDates($driverId, $fromDate, $toDate);

        return [
            'from'         => $fromDate->toDateTimeString(),
            'to'           => $toDate->toDateTimeString(),
            'total_count'  => $rides->count(),
            'total_profit' => $totalProfit,
            'rides'        => $forAdmin ? $rides->get() : $rides,
        ];
    }

    private function resolveRange(
        string $period,
        ?string $from,
        ?string $to
    ): array {

        if (strtotime($period) !== false) {
            $date = Carbon::parse($period);

            return [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ];
        }

        return match ($period) {
            'today' => [
                now()->startOfDay(),
                now()->endOfDay(),
            ],

            'last_7_days' => [
                now()->subDays(6)->startOfDay(),
                now()->endOfDay(),
            ],

            'last_30_days' => [
                now()->subDays(29)->startOfDay(),
                now()->endOfDay(),
            ],

            'this_month' => [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ],

            'custom' => [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay(),
            ],

            default => throw new \InvalidArgumentException('Invalid period value'),
        };
    }

    public function check($id): void
    {
        $isDriver = DB::table('user_roles')
            ->where('user_id', $id)
            ->where('role_id', 2)
            ->exists();

        if (!$isDriver) {
           throw new \Exception();
        }
    }

}
