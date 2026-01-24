<?php

namespace App\Repositories\Admin;

use App\Models\DriverProfile;
use App\Models\DriverDocument;
use App\Models\CancellationReason;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;


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

//CancellationReason

    public function getAll()
    {
        return CancellationReason::latest()->get();
    }

    public function findById(int $id): CancellationReason
    {
        return CancellationReason::findOrFail($id);
    }

    public function create(array $data): CancellationReason
    {
        return CancellationReason::create($data);
    }

    public function update(int $id, array $data): CancellationReason
    {
        $reason = $this->findById($id);
        $reason->update($data);

        return $reason;
    }

    public function deleteReasonByIds(array $ids): int
{
    return CancellationReason::whereIn('id', $ids)->delete();
}


    public function toggleStatus(int $id): CancellationReason
    {
        $reason = $this->findById($id);
        $reason->is_active = ! $reason->is_active;
        $reason->save();

        return $reason;
    }


    public function SearchCancellationReason(?string $search)
{
    return CancellationReason::when($search, function ($query) use ($search) {

        $query->where(function ($q) use ($search) {

            if (is_numeric($search)) {
                $q->orWhere('id', (int) $search);
            }

            $q->orWhere('reason', 'LIKE', "%{$search}%");

            $q->orWhere('user_type', 'LIKE', "%{$search}%");
        });

    })
    ->orderByDesc('id')
    ->get();
}

//Driver Management
//DriversList

    public function getDriversList() : LengthAwarePaginator
    {
        return DriverProfile::query()
            ->with([
                'user:id,name,email,phone',
                'vehicleMake:id,name',
            ])
            ->select('driver_profiles.*')
            ->selectSub(
                DB::table('rides')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('rides.driver_id', 'driver_profiles.user_id')
                    ->whereIn('rides.status', ['driver_on_way', 'on_going']),
                'live_rides'
            )
            ->selectSub(
                DB::table('rides')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('rides.driver_id', 'driver_profiles.user_id')
                    ->where('rides.status', 'completed'),
                'completed_rides'
            )
            ->selectSub(
                DB::table('rides')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('rides.driver_id', 'driver_profiles.user_id')
                    ->where('rides.status', 'cancelled'),
                'cancelled_rides'
            )
            ->selectSub(
                DB::table('ride_request_responses')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('ride_request_responses.driver_id', 'driver_profiles.user_id')
                    ->where('ride_request_responses.status', 'skipped'),
                'rejected_rides'
            )
            ->selectSub(
                DB::table('driver_documents')
                    ->selectRaw("
                        CASE
                            WHEN COUNT(*) = SUM(status = 'approved') THEN 'approved'
                            ELSE 'pending'
                        END
                    ")
                    ->whereColumn('driver_documents.driver_id', 'driver_profiles.id'),
                'documents_status'
            )
            ->paginate(10);
    }


    public function approveDriver(int $driverId): bool
{
    $driver = DB::table('driver_profiles')
        ->where('id', $driverId)
        ->where('status', 'pending')
        ->first();

    if (!$driver) {
        return false;
    }

    DB::table('driver_profiles')
        ->where('id', $driverId)
        ->update([
            'status' => 'approved',
            'updated_at' => now(),
        ]);

    return true;
}


public function suspendDriver(int $driverId): bool
{
    $driver = DB::table('driver_profiles')
        ->where('id', $driverId)
        ->whereIn('status', ['pending', 'approved'])
        ->first();

    if (!$driver) {
        return false;
    }

    DB::table('driver_profiles')
        ->where('id', $driverId)
        ->update([
            'status' => 'suspended',
            'updated_at' => now(),
        ]);

    return true;
}
public function getActiveDriversList()
{
    return DriverProfile::query()
        ->where('driver_profiles.status', 'approved')
        ->where('driver_profiles.is_status', 'active')

        ->whereRaw("
            (
                SELECT COUNT(*)
                FROM driver_documents
                WHERE driver_documents.driver_id = driver_profiles.id
            ) > 0
        ")
        ->whereRaw("
            (
                SELECT COUNT(*)
                FROM driver_documents
                WHERE driver_documents.driver_id = driver_profiles.id
                AND driver_documents.status = 'approved'
            ) =
            (
                SELECT COUNT(*)
                FROM driver_documents
                WHERE driver_documents.driver_id = driver_profiles.id
            )
        ")

        ->with([
            'user:id,name,email,phone',
            'vehicleMake:id,name',
        ])
        ->select('driver_profiles.*')

        ->selectSub(
            DB::table('rides')
                ->selectRaw('COUNT(*)')
                ->whereColumn('rides.driver_id', 'driver_profiles.user_id')
                ->whereIn('rides.status', ['driver_on_way', 'on_going']),
            'live_rides'
        )
        ->selectSub(
            DB::table('rides')
                ->selectRaw('COUNT(*)')
                ->whereColumn('rides.driver_id', 'driver_profiles.user_id')
                ->where('rides.status', 'completed'),
            'completed_rides'
        )
        ->selectSub(
            DB::table('rides')
                ->selectRaw('COUNT(*)')
                ->whereColumn('rides.driver_id', 'driver_profiles.user_id')
                ->where('rides.status', 'cancelled'),
            'cancelled_rides'
        )
        ->selectSub(
            DB::table('ride_request_responses')
                ->selectRaw('COUNT(*)')
                ->whereColumn('ride_request_responses.driver_id', 'driver_profiles.user_id')
                ->where('ride_request_responses.status', 'skipped'),
            'rejected_rides'
        )
        ->selectSub(
            DB::table('driver_documents')
                ->selectRaw("
                    CASE
                        WHEN COUNT(*) = SUM(status = 'approved') THEN 'approved'
                        ELSE 'pending'
                    END
                ")
                ->whereColumn('driver_documents.driver_id', 'driver_profiles.id'),
            'documents_status'
        )
        ->paginate(10);
}

public function getInactiveApprovedDrivers()
{
    return DriverProfile::query()
        ->where('driver_profiles.status', 'approved')
        ->where('driver_profiles.is_status', 'inactive')

        // شرط المستندات كلها approved
        ->whereRaw("
            (
                SELECT COUNT(*)
                FROM driver_documents
                WHERE driver_documents.driver_id = driver_profiles.id
            ) > 0
        ")
        ->whereRaw("
            (
                SELECT COUNT(*)
                FROM driver_documents
                WHERE driver_documents.driver_id = driver_profiles.id
                AND driver_documents.status = 'approved'
            ) =
            (
                SELECT COUNT(*)
                FROM driver_documents
                WHERE driver_documents.driver_id = driver_profiles.id
            )
        ")

        ->with([
            'user:id,name,email,phone',
            'vehicleMake:id,name',
        ])
        ->select('driver_profiles.*')

        ->selectSub(
            DB::table('rides')
                ->selectRaw('COUNT(*)')
                ->whereColumn('rides.driver_id', 'driver_profiles.user_id')
                ->whereIn('rides.status', ['driver_on_way', 'on_going']),
            'live_rides'
        )
        ->selectSub(
            DB::table('rides')
                ->selectRaw('COUNT(*)')
                ->whereColumn('rides.driver_id', 'driver_profiles.user_id')
                ->where('rides.status', 'completed'),
            'completed_rides'
        )
        ->selectSub(
            DB::table('rides')
                ->selectRaw('COUNT(*)')
                ->whereColumn('rides.driver_id', 'driver_profiles.user_id')
                ->where('rides.status', 'cancelled'),
            'cancelled_rides'
        )
        ->selectSub(
            DB::table('ride_request_responses')
                ->selectRaw('COUNT(*)')
                ->whereColumn('ride_request_responses.driver_id', 'driver_profiles.user_id')
                ->where('ride_request_responses.status', 'skipped'),
            'rejected_rides'
        )
        ->selectSub(
            DB::table('driver_documents')
                ->selectRaw("
                    CASE
                        WHEN COUNT(*) = SUM(status = 'approved') THEN 'approved'
                        ELSE 'pending'
                    END
                ")
                ->whereColumn('driver_documents.driver_id', 'driver_profiles.id'),
            'documents_status'
        )
        ->paginate(10);
}

public function getPendingDrivers()
{
    return DriverProfile::query()
        ->where('driver_profiles.status', 'pending')

        // شرط أن يكون عنده مستندات
        ->whereRaw("
            (
                SELECT COUNT(*)
                FROM driver_documents
                WHERE driver_documents.driver_id = driver_profiles.id
            ) > 0
        ")

        // شرط أن NOT كل المستندات approved
        ->whereRaw("
            (
                SELECT COUNT(*)
                FROM driver_documents
                WHERE driver_documents.driver_id = driver_profiles.id
                AND driver_documents.status = 'approved'
            ) <
            (
                SELECT COUNT(*)
                FROM driver_documents
                WHERE driver_documents.driver_id = driver_profiles.id
            )
        ")

        ->with([
            'user:id,name,email,phone',
            'vehicleMake:id,name',
        ])
        ->select('driver_profiles.*')

        ->selectSub(
            DB::table('rides')
                ->selectRaw('COUNT(*)')
                ->whereColumn('rides.driver_id', 'driver_profiles.user_id')
                ->whereIn('rides.status', ['driver_on_way', 'on_going']),
            'live_rides'
        )
        ->selectSub(
            DB::table('rides')
                ->selectRaw('COUNT(*)')
                ->whereColumn('rides.driver_id', 'driver_profiles.user_id')
                ->where('rides.status', 'completed'),
            'completed_rides'
        )
        ->selectSub(
            DB::table('rides')
                ->selectRaw('COUNT(*)')
                ->whereColumn('rides.driver_id', 'driver_profiles.user_id')
                ->where('rides.status', 'cancelled'),
            'cancelled_rides'
        )
        ->selectSub(
            DB::table('ride_request_responses')
                ->selectRaw('COUNT(*)')
                ->whereColumn('ride_request_responses.driver_id', 'driver_profiles.user_id')
                ->where('ride_request_responses.status', 'skipped'),
            'rejected_rides'
        )
        ->selectSub(
            DB::table('driver_documents')
                ->selectRaw("
                    CASE
                        WHEN COUNT(*) = SUM(status = 'approved') THEN 'approved'
                        ELSE 'pending'
                    END
                ")
                ->whereColumn('driver_documents.driver_id', 'driver_profiles.id'),
            'documents_status'
        )
        ->paginate(10);
}

    public function updateCanReceiveRequests(int $driverId, bool $status): DriverProfile
    {
        $driver = DriverProfile::findOrFail($driverId);

        $driver->update([
            'can_receive_requests' => $status,
        ]);

        return $driver;
    }

    public function searchDrivers(string $search)
{
    return DriverProfile::query()
        ->with(['user', 'vehicleMake', 'vehicleType'])
        ->where(function ($q) use ($search) {

            // search by driver id
            if (is_numeric($search)) {
                $q->orWhere('driver_profiles.id', $search);
            }

            // search by driver name
            $q->orWhereHas('user', function ($uq) use ($search) {
                $uq->where('name', 'like', "%{$search}%");
            });

            // search by vehicle make
            $q->orWhereHas('vehicleMake', function ($vq) use ($search) {
                $vq->where('name', 'like', "%{$search}%");
            });

            // search by vehicle type
            $q->orWhereHas('vehicleType', function ($tq) use ($search) {
                $tq->where('name', 'like', "%{$search}%");
            });

        })
        ->paginate(10);
}

public function findByDriverProfileId(int $driverId)
{
    return DriverDocument::where('driver_id', $driverId)
        ->orderBy('created_at', 'desc')
        ->get();
}

public function updateStatus(int $id, string $status): DriverDocument
{
    $doc = DriverDocument::findOrFail($id);
    $doc->status = $status;
    $doc->save();

    return $doc;
}


}











