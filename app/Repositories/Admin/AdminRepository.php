<?php

namespace App\Repositories\Admin;

use App\Models\DriverProfile;
use Illuminate\Support\Facades\DB;

class AdminRepository
{
    //dashboard
    public function countApprovedDrivers(): int
    {
        return DriverProfile::where('status', 'approved')->count();
    }

    public function countPendingDrivers(): int
    {
        return DriverProfile::where('status', 'pending')->count();
    }

    public function countPassengers(): int
    {
        return DB::table('users')
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('roles.name', 'user')
            ->count();
    }

    public function countAllRides(): int
    {
        return DB::table('ride_requests')->count();
    }

    public function countCompletedRides(): int
    {
        return DB::table('ride_requests')
            ->where('status', 'accepted')
            ->count();
    }

  public function getLastFiveRidesWithPassengerAndDriver()
    {
        return DB::table('rides')
            ->join('users as passengers', 'passengers.id', '=', 'rides.user_id')
            ->join('users as drivers', 'drivers.id', '=', 'rides.driver_id')
            ->orderBy('rides.created_at', 'desc')
            ->limit(5)
            ->select([
                // Ride info
                'rides.id as ride_id',
                'rides.ride_request_id',
                'rides.status as ride_status',
                'rides.start_lat',
                'rides.start_lng',
                'rides.end_lat',
                'rides.end_lng',
                'rides.distance_km',
                'rides.price',
                'rides.duration_minutes',
                'rides.code',
                'rides.created_at',

                // Passenger info
                'passengers.id as passenger_id',
                'passengers.name as passenger_name',
                'passengers.phone as passenger_phone',

                // Driver info
                'drivers.id as driver_id',
                'drivers.name as driver_name',
                'drivers.phone as driver_phone',
            ])
            ->get();
    }

    //Plateform Setup
    //vehicle_types
   public function getAllVehicleTypes()
    {
        return DB::table('vehicle_types')
            ->select([
                'id',
                'name',
                'image',
                'is_active',
                'base_fare',
                'per_km',
                'per_minute',
                'minimum_fare',
                'cost_increase_percentage',
            ])
            ->get()
            ->map(function ($vehicleType) {
                if ($vehicleType->image) {
                    $vehicleType->image = asset('storage/' . $vehicleType->image);
                }
                return $vehicleType;
            });
    }


    public function toggleVehicleStatus(int $vehicleTypeId): ?bool
    {
        $vehicleType = DB::table('vehicle_types')
            ->where('id', $vehicleTypeId)
            ->first();

        if (!$vehicleType) {
            return null;
        }

        $newStatus = !$vehicleType->is_active;

        DB::table('vehicle_types')
            ->where('id', $vehicleTypeId)
            ->update([
                'is_active' => $newStatus
            ]);

        return $newStatus;
    }


    public function deleteByIds(array $ids): int
    {
        return DB::table('vehicle_types')
            ->whereIn('id', $ids)
            ->delete();
    }


    public function search(string $search)
    {
        return DB::table('vehicle_types')
            ->where(function ($query) use ($search) {
                // البحث بالـ ID إذا كان رقم
                if (is_numeric($search)) {
                    $query->orWhere('id', (int) $search);
                }

                // البحث بالاسم
                $query->orWhere('name', 'LIKE', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->get();
    }

        //vehicle_Makes
    public function createVehicleMake(array $data)
    {
        return DB::table('vehicle_makes')->insertGetId([
            'name'       => $data['name'],
            'type'       => $data['type'] ?? null,
            'is_active'  => $data['is_active'] ?? true,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);
    }

    public function searchVehicleMakes(string $search)
{
    return DB::table('vehicle_makes')
        ->where(function ($query) use ($search) {

            if (is_numeric($search)) {
                $query->orWhere('id', (int) $search);
            }

            $query->orWhere('name', 'LIKE', "%{$search}%");

            $query->orWhere('type', 'LIKE', "%{$search}%");
        })
        ->orderBy('id', 'desc')
        ->get();
}

    public function getAllVehicleMakes()
{
    return DB::table('vehicle_makes')
        ->select([
            'id',
            'name',
            'type',
            'is_active',
            'created_at',
            'updated_at',
        ])
        ->orderBy('id', 'desc')
        ->get();
}

public function toggleVehicleMakeStatus(int $vehicleMakeId): ?bool
{
    $vehicleMake = DB::table('vehicle_makes')
        ->where('id', $vehicleMakeId)
        ->first();

    if (!$vehicleMake) {
        return null;
    }

    $newStatus = !$vehicleMake->is_active;

    DB::table('vehicle_makes')
        ->where('id', $vehicleMakeId)
        ->update([
            'is_active' => $newStatus,
            'updated_at' => now(),
        ]);

    return $newStatus;
}

public function deleteVehicleMakesByIds(array $ids): int
{
    return DB::table('vehicle_makes')
        ->whereIn('id', $ids)
        ->delete();
}
public function getVehicleMakesByType(string $type)
{
    return DB::table('vehicle_makes')
        ->where('type', $type)
        ->orderBy('id', 'desc')
        ->get();
}

}






