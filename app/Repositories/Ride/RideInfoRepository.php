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
    'rides.cancellation_reason_id', 
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
            'driver',
            'driver.location',
            'user',
            'driverProfile',
            'statusHistory',
            'driverRating',
            'passengerRating',
            'driverProfit',
            'companyCommission',
        ])
            ->withAvg('driverRatings', 'rating')
            ->withAvg('passengerRatings', 'rating')
            ->findOrFail($rideId);
    }

    public function findRequestWithDetails(int $id): Collection
    {
        $data = RideRequest::query()
            ->where('ride_requests.id', $id)
            ->leftJoin('users as u', 'u.id', '=', 'ride_requests.user_id')
            ->leftJoin('vehicle_types as v', 'v.id', '=', 'ride_requests.vehicle_type_id')
            ->leftJoin('rides as r', 'r.ride_request_id', 'ride_requests.id')
            ->select([
                'ride_requests.*',
                'r.id as ride_id',
                'r.driver_id as driver_id',
                'u.name as user_name',
                'u.phone as user_phone',
                'u.email as user_email',
                'u.gender as user_gender',
                'u.profile_image as profile_image',
                'v.name as vehicle_type_name',
                DB::raw('(
                SELECT CAST(ROUND(AVG(rating), 1) AS DECIMAL(3,1))
                FROM passenger_ratings
                WHERE passenger_ratings.user_id = u.id
            ) as user_avg_rating'),
            ])
            ->get();

        $data->transform(function ($item) {
            $item->profile_image = $item->profile_image
                ? asset('storage/' . $item->profile_image)
                : null;

            return $item;
        });

        return $data;
    }
public function deleteByIds(array $ids): int
{
    return DB::table('rides')
        ->whereIn('id', $ids)
        ->delete();
}
}
