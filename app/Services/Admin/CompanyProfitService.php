<?php

namespace App\Services\Admin;

use App\Repositories\Ride\ProfitRepository;
use App\Repositories\Ride\RideRepository;
use App\Services\User\Driver\DriverDashboardService;

class CompanyProfitService
{
    public function __construct(
        protected DriverDashboardService $service,
        protected ProfitRepository       $profits,
        protected RideRepository         $rides)
    {
    }

    public function stats(
        string  $period,
        ?string $from,
        ?string $to,
    ): array
    {

        [$fromDate, $toDate] = $this->service->resolveRange(
            period: $period,
            from: $from,
            to: $to
        );

        $totalProfit = $this->profits->companySumBetween($fromDate, $toDate);
        $rides = $this->rides->ridesWithCommisions($fromDate, $toDate);

        return [
            'from' => $fromDate?->toDateTimeString(),
            'to' => $toDate?->toDateTimeString(),
            'total_count' => $rides->count(),
            'total_profit' => $totalProfit,
            'rides' => $rides->get(),
        ];
    }
}
