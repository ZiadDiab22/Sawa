<?php

namespace App\Repositories\Ride;

use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Collection;

class RideInfoRepository
{
    private const STATUSES = [
        'completed',
        'cancelled',
        'on_going',
        'driver_on_way',
    ];

    public function getRides(string $status):Collection
    {
        return DB::table('rides')
            ->join('users as drivers', 'drivers.id', '=', 'rides.driver_id')
            ->join('users as passengers', 'passengers.id', '=', 'rides.user_id')
            ->join('driver_profiles as dp', 'dp.user_id', '=', 'drivers.id')

            ->select([
                'rides.*',
                'drivers.name as driver_name',
                'passengers.name as passenger_name',
                'dp.vehicle_model',
                'dp.vehicle_year',
                'dp.vehicle_color',
                'dp.vehicle_plate_number',
            ])

            ->when(
                $status !== 'all' && in_array($status, self::STATUSES),
                fn ($q) => $q->where('rides.status', $status)
            )

            ->latest('rides.created_at')
            ->get();
    }
}
