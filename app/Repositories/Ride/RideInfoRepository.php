<?php

namespace App\Repositories\Ride;

use App\Models\Ride;
use App\Models\RideRequest;
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

    public function getRides(string $status): Collection
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
                fn($q) => $q->where('rides.status', $status)
            )
            ->latest('rides.created_at')
            ->get();
    }

    public function findWithDetails(int $rideId)
    {
        return Ride::with([
            'driver:id,name,phone',
            'driver.location',
            'user:id,name,phone',
            'driverProfile',
            'statusHistory',
            'driverRating',
            'passengerRating',
            'driverProfit',
            'companyCommission',
        ])->findOrFail($rideId);
    }

    public function findRequestWithDetails(int $id)
    {
        return RideRequest::query()->where('ride_requests.id', $id)
            ->leftJoin('users as u', 'u.id', 'ride_requests.user_id')
            ->leftJoin('vehicle_types as v', 'v.id', 'vehicle_type_id')
            ->get(['ride_requests.*',
                'u.name as user_name',
                'u.phone as user_phone',
                'u.email as user_email',
                'v.name as vehicle_type_name']);
    }
}
