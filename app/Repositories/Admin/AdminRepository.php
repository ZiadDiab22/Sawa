<?php

namespace App\Repositories\Admin;

use App\Models\CancellationReason;
use App\Models\CompanyCommission;
use App\Models\DriverDocument;
use App\Models\DriverProfile;
use App\Models\DriverProfit;
use App\Models\Ride;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class AdminRepository
{
    //dashboard
    public function getDashboardStats(): array
    {
        $today = Carbon::today();

        $approvedDriversCount = DB::table('driver_profiles')
            ->where('status', 'approved')
            ->count();

        $pendingDriversCount = DB::table('driver_profiles')
            ->where('status', 'pending')
            ->count();

        $passengersCount = DB::table('users')
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('roles.name', 'user')
            ->count();

        $totalRidesCount = DB::table('ride_requests')->count();

        $completedRidesCount = DB::table('ride_requests')
            ->where('status', 'completed')
            ->count();

        // ========= PROFITS =========

        // أرباح السائقين (كاملة)
        $driversTotalProfit = DB::table('driver_profits')
            ->sum('amount');

        // أرباح الشركة (كاملة)
        $companyTotalRevenue = DB::table('company_commissions')
            ->sum('amount');

        // الأرباح الكلية (سائق + شركة)
        $totalPlatformRevenue = $driversTotalProfit + $companyTotalRevenue;

        // أرباح الشركة اليومية (نسبة الشركة فقط)
        $companyDailyRevenue = DB::table('company_commissions')
            ->whereDate('created_at', $today)
            ->sum('amount');

        // آخر 5 رحلات مكتملة
        $lastFiveRides = DB::table('rides')
            ->join('users as passengers', 'passengers.id', '=', 'rides.user_id')
            ->join('users as drivers', 'drivers.id', '=', 'rides.driver_id')
            ->where('rides.status', 'completed')
            ->orderBy('rides.created_at', 'desc')
            ->limit(5)
            ->select([
                'rides.id as ride_id',
                'rides.price',
                'rides.created_at',
                'passengers.name as passenger_name',
                'drivers.name as driver_name',
            ])
            ->get();

        return [
            'approved_drivers_count' => $approvedDriversCount,
            'pending_drivers_count'  => $pendingDriversCount,
            'passengers_count'       => $passengersCount,
            'total_rides_count'      => $totalRidesCount,
            'completed_rides_count'  => $completedRidesCount,

            'total_platform_revenue' => (float) $totalPlatformRevenue,
            'company_total_revenue'  => (float) $companyTotalRevenue,
            'company_daily_revenue'  => (float) $companyDailyRevenue,

            'last_five_rides'        => $lastFiveRides,
        ];
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
        $drivers = DriverProfile::query()
            ->with([
                'user:id,name,email,phone',
                'vehicleMake:id,name',
                'documents:id,driver_id,type,status'

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
            $drivers->getCollection()->transform(function ($driver) {

        if ($driver->user && $driver->user->profile_image) {
            $driver->user->profile_image = asset('storage/' . $driver->user->profile_image);
        }

        return $driver;
    });

    return $drivers;
    }
public function getDriverVehicleInfo(int $driverId)
{
    $driver = DB::table('driver_profiles as dp')
        ->leftJoin('vehicle_types as vt', 'vt.id', '=', 'dp.vehicle_type_id')
        ->leftJoin('vehicle_makes as vm', 'vm.id', '=', 'dp.vehicle_make_id')
        ->select([
            'dp.user_id as driver_id',
            'dp.id as driver_profile_id',
            'vt.name as vehicle_type',
            'vm.name as vehicle_make',
            'dp.vehicle_model',
            'dp.vehicle_year',
            'dp.vehicle_color',
            'dp.vehicle_plate_number',
            'dp.vehicle_document',
            'dp.license_document',
            'dp.insurance_document',
            'dp.vehicle_images',
        ])
        ->where('dp.user_id', $driverId)
        ->first();

    if (!$driver) {
        return null;
    }

    // اصلاح روابط الصور
    $driver->vehicle_document = $driver->vehicle_document
        ? asset('storage/'.$driver->vehicle_document)
        : null;

    $driver->license_document = $driver->license_document
        ? asset('storage/'.$driver->license_document)
        : null;

    $driver->insurance_document = $driver->insurance_document
        ? asset('storage/'.$driver->insurance_document)
        : null;

    $images = json_decode($driver->vehicle_images, true) ?? [];

    $driver->vehicle_images = array_map(function ($img) {
        return asset('storage/'.$img);
    }, $images);

    return $driver;
}

public function approveDriver(int $driverId)
{
    return DB::transaction(function () use ($driverId) {

        $driver = DB::table('driver_profiles')
            ->where('user_id', $driverId)
            ->lockForUpdate()
            ->first();

        if (!$driver) {
            return null;
        }

        if (!in_array($driver->status, ['pending', 'suspended'])) {
            return null;
        }
        $documents = DB::table('driver_documents')
            ->where('driver_id', $driver->id)
            ->get();

        if ($documents->isEmpty()) {
            throw new \Exception('Driver documents not uploaded');
        }

        $notApproved = $documents->where('status', '!=', 'approved');

        if ($notApproved->count() > 0) {
            throw new \Exception('All driver documents must be approved first');
        }
        DB::table('driver_profiles')
            ->where('user_id', $driverId)
            ->update([
                'status' => 'approved',
                'is_status' => 'active',
                'can_receive_requests' => true,
                'updated_at' => now(),
            ]);

        $driverRoleId = DB::table('roles')
            ->where('name', 'driver')
            ->value('id');

        if (!$driverRoleId) {
            throw new \Exception('Driver role not found');
        }

        DB::table('user_roles')
            ->where('user_id', $driver->user_id)
            ->delete();

        DB::table('user_roles')->insert([
            'user_id' => $driver->user_id,
            'role_id' => $driverRoleId,
        ]);

        $driver = DB::table('driver_profiles')
            ->where('user_id', $driverId)
            ->first();

        // تحويل روابط الصور
        $driver->vehicle_document = $driver->vehicle_document
            ? asset('storage/' . $driver->vehicle_document)
            : null;

        $driver->license_document = $driver->license_document
            ? asset('storage/' . $driver->license_document)
            : null;

        $driver->insurance_document = $driver->insurance_document
            ? asset('storage/' . $driver->insurance_document)
            : null;

        $images = json_decode($driver->vehicle_images ?? '[]', true);
        if ($images) {
            $driver->vehicle_images = collect($images)
                ->map(fn($img) => asset('storage/' . $img))
                ->values();
        }

        return $driver;
    });
}
public function suspendDriver(int $userId)
{
    return DB::transaction(function () use ($userId) {

        $driver = DB::table('driver_profiles')
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved'])
            ->lockForUpdate()
            ->first();

        if (!$driver) {
            return null;
        }

        DB::table('driver_profiles')
            ->where('user_id', $userId)
            ->update([
                'status' => 'suspended',
                'is_status' => 'inactive',
                'can_receive_requests' => false,
                'updated_at' => now(),
            ]);

        DB::table('users')
            ->where('id', $userId)
            ->update([
                'blocked' => true,
                'updated_at' => now(),
            ]);

        $driver = DB::table('driver_profiles')
            ->where('user_id', $userId)
            ->first();

        // روابط الصور
        $driver->vehicle_document = $driver->vehicle_document
            ? asset('storage/'.$driver->vehicle_document)
            : null;

        $driver->license_document = $driver->license_document
            ? asset('storage/'.$driver->license_document)
            : null;

        $driver->insurance_document = $driver->insurance_document
            ? asset('storage/'.$driver->insurance_document)
            : null;

        $vehicleImages = json_decode($driver->vehicle_images, true) ?? [];

        $driver->vehicle_images = array_map(function ($img) {
            return asset('storage/'.$img);
        }, $vehicleImages);

        return $driver;
    });
}

public function getActiveDriversList()
{
    return DriverProfile::query()
        ->where('driver_profiles.status', 'approved')
        ->where('driver_profiles.is_status', 'active')

        ->whereHas('user', function ($q) {
        $q->where('blocked', false);
        })

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
        'updated_at' => now(),
    ]);

    return $driver->fresh();
}

    public function searchDrivers(string $search)
{
    return DriverProfile::query()
        ->with(['user', 'vehicleMake', 'vehicleType'])
        ->where(function ($q) use ($search) {

            // search by driver id
            if (is_numeric($search)) {
                $q->orWhere('driver_profiles.user_id', $search);
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

public function findByDriverProfileId(int $userId)
{
    return DriverDocument::where('user_id', $userId)
        ->orderBy('created_at', 'desc')
        ->get();
}
public function updateStatus(int $id, string $status): DriverDocument
{
    $doc = DriverDocument::with('user.driverProfile')->findOrFail($id);

    if (!$doc->user->driverProfile) {
        throw new \Exception('This user is not a driver');
    }

    $driverProfile = $doc->user->driverProfile;

    if (!$driverProfile) {
        throw new \Exception('User is not registered as driver');
    }

    if ($driverProfile->status !== 'approved') {
        throw new \Exception('Driver must be approved before approving documents');
    }

    $doc->status = $status;
    $doc->save();

    return $doc;
}

public function toggleBannedDriver(int $userId): DriverProfile
{
    $driver = DriverProfile::where('user_id', $userId)->firstOrFail();

    if ($driver->is_status === 'banned') {

        // فك الحظر
        $driver->update([
            'is_status' => 'inactive',
        ]);

    } else {

        // حظر
        $driver->update([
            'is_status' => 'banned',
            'can_receive_requests' => false,
        ]);
    }

    return $driver->fresh();
}

public function getDriverProfileByUser(int $userId): DriverProfile
{
    return DriverProfile::with([
        'user',
        'vehicleMake',
        'user.documents'
    ])
    ->where('user_id', $userId)
    ->firstOrFail();
}

//لهون وصلت

    public function rideStats(int $driverUserId): array
{
    $stats = Ride::where('user_id', $driverUserId)
        ->selectRaw("
            COUNT(*) as total_rides,
            SUM(CASE WHEN status IN ('driver_on_way','on_going') THEN 1 ELSE 0 END) as live_rides,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_rides,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_rides
        ")
        ->first();

    return [
        'live_rides' => (int) $stats->live_rides,
        'completed_rides' => (int) $stats->completed_rides,
        'cancelled_rides' => (int) $stats->cancelled_rides,
        'total_rides' => (int) $stats->total_rides,
    ];
}

public function earnings(int $driverUserId): array
{
    $today = Carbon::today();

    $driver = DriverProfit::where('user_id', $driverUserId)
        ->selectRaw("
            SUM(amount) as total,
            SUM(CASE WHEN DATE(created_at) = ? THEN amount ELSE 0 END) as today_total
        ", [$today])
        ->first();

    $admin = CompanyCommission::where('user_id', $driverUserId)
        ->selectRaw("
            SUM(amount) as total,
            SUM(CASE WHEN DATE(created_at) = ? THEN amount ELSE 0 END) as today_total
        ", [$today])
        ->first();

    return [
        'admin_commission' => (float) $admin->total,
        'driver_earnings' => (float) $driver->total,
        'total_earnings' => (float) ($driver->total + $admin->total),

        'today_admin_commission' => (float) $admin->today_total,
        'today_driver_earnings' => (float) $driver->today_total,

        'by_cash' => 0,
        'by_card' => 0,
    ];
}
    public function getDriverRides(int $driverUserId, ?string $status = null)
{
    $query = DB::table('driver_profiles as dp')
        ->join('users as d', 'd.id', '=', 'dp.user_id')
        ->join('rides as r', 'r.driver_id', '=', 'd.id')
        ->join('users as u', 'u.id', '=', 'r.user_id')
        ->join('ride_requests as rr', 'rr.id', '=', 'r.ride_request_id')
        ->leftJoin('vehicle_types as vt', 'vt.id', '=', 'dp.vehicle_type_id')
        ->leftJoin('vehicle_makes as vm', 'vm.id', '=', 'dp.vehicle_make_id')
        ->where('dp.id', $driverUserId);

    if ($status) {

        if ($status === 'live') {
            $query->whereIn('r.status', ['driver_on_way', 'on_going']);
        } else {
            $query->where('r.status', $status);
        }
    }

    return $query
        ->orderByDesc('r.created_at')
        ->select([
            'r.id as ride_id',
            'r.code as reservation_code',
            'r.status',

            // Driver
            'd.id as driver_id',
            'd.name as driver_name',
            'd.profile_image as driver_avatar',

            // Rider
            'u.id as rider_id',
            'u.name as rider_name',

            // Service
            'vt.name as service',

            // Vehicle
            'vt.name as vehicle_type',
            'vm.name as vehicle_make',
            'dp.vehicle_model',
            'dp.vehicle_plate_number',

            // Locations
            'rr.pickup_lat',
            'rr.pickup_lng',
            'rr.drop_lat',
            'rr.drop_lng',

            // Ride info
            'r.price',
            DB::raw('DATE(r.created_at) as booking_date'),
        ])
        ->paginate(10);
}

public function getRiderRides(int $riderId, ?string $status = null)
{
    $query = DB::table('users as u')
        ->join('rides as r', 'r.user_id', '=', 'u.id')
        ->join('users as d', 'd.id', '=', 'r.driver_id')
        ->join('driver_profiles as dp', 'dp.user_id', '=', 'd.id')
        ->join('ride_requests as rr', 'rr.id', '=', 'r.ride_request_id')
        ->leftJoin('vehicle_types as vt', 'vt.id', '=', 'dp.vehicle_type_id')
        ->leftJoin('vehicle_makes as vm', 'vm.id', '=', 'dp.vehicle_make_id')
        ->where('u.id', $riderId);

    if ($status) {

        if ($status === 'live') {
            $query->whereIn('r.status', ['driver_on_way', 'on_going']);
        } else {
            $query->where('r.status', $status);
        }
    }

    return $query
        ->orderByDesc('r.created_at')
        ->select([
            'r.id as ride_id',
            'r.code as reservation_code',
            'r.status',

            // Driver
            'd.id as driver_id',
            'd.name as driver_name',
            'd.profile_image as driver_avatar',

            // Rider
            'u.id as rider_id',
            'u.name as rider_name',

            // Service
            'vt.name as service',

            // Vehicle
            'vt.name as vehicle_type',
            'vm.name as vehicle_make',
            'dp.vehicle_model',
            'dp.vehicle_plate_number',

            // Locations
            'rr.pickup_lat',
            'rr.pickup_lng',
            'rr.drop_lat',
            'rr.drop_lng',

            // Ride info
            'r.price',
            DB::raw('DATE(r.created_at) as booking_date'),
        ])
        ->paginate(10);
}

//financial

    public function getDriverWallet(int $driverId): float
    {
    return (float) DriverProfile::query()
        ->where('user_id', $driverId)
        ->value('wallet');
    }

    public function getTransactions(int $driverId, int $perPage = 10): LengthAwarePaginator
    {
        return WalletTransaction::query()
            ->with('ride')
            ->where('user_id', $driverId)
            ->latest()
            ->paginate($perPage);
    }

    public function getTotals(int $driverId): array
    {
        return [
            'total_credit' => WalletTransaction::where('user_id', $driverId)
                ->where('type', 'credit')
                ->sum('amount'),

            'total_debit' => WalletTransaction::where('user_id', $driverId)
                ->where('type', 'debit')
                ->sum('amount'),
        ];
    }




    //Rider Managment
   public function getRiders(int $perPage = 100)
{
    return DB::table('users as u')
        ->join('user_roles as ur', 'ur.user_id', '=', 'u.id')
        ->where('ur.role_id', 1)
        ->select([
            'u.id',
            'u.name',
            'u.email',
            'u.phone',
            'u.profile_image',
            'u.blocked',
            'u.created_at',
        ])
        ->orderByDesc('u.created_at')
        ->paginate($perPage);
}

    public function searchRiders(int $roleId ,string $search)
{
    return User::query()
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->where('user_roles.role_id', $roleId)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('users.phone', 'like', "%{$search}%");
                });
            })
            ->select('users.*')
            ->orderByDesc('users.created_at')
            ->paginate(10);
}


public function deleteUsersByIds(array $ids): array
{
    $userIds = DB::table('user_roles')
        ->whereIn('user_id', $ids)
        ->where('role_id', 3)
        ->pluck('user_id')
        ->toArray();

    $protectedIds = DB::table('user_roles')
        ->whereIn('user_id', $ids)
        ->whereIn('role_id', [1, 2])
        ->pluck('user_id')
        ->unique()
        ->toArray();

    if (empty($userIds)) {
        return [
            'status' => 'forbidden',
            'deleted_count' => 0,
            'protected_ids' => $protectedIds,
        ];
    }

    DB::transaction(function () use ($userIds) {
        User::whereIn('id', $userIds)->delete();
    });

    return [
        'status' => 'success',
        'deleted_count' => count($userIds),
        'protected_ids' => $protectedIds,
    ];
}


public function toggleRiderBlock(int $userId): array
    {
        // تحقق إنو Rider
        $isRider = DB::table('user_roles')
            ->where('user_id', $userId)
            ->where('role_id', 1) // Rider
            ->exists();

        if (!$isRider) {
            return [
                'status' => 'forbidden'
            ];
        }

        $user = User::findOrFail($userId);

        $user->blocked = !$user->blocked;
        $user->save();

        return [
            'status'  => 'success',
            'blocked' => $user->blocked,
        ];
    }

    public function getActiveRiders(int $perPage = 100)
{
    return DB::table('users as u')
        ->join('user_roles as ur', 'ur.user_id', '=', 'u.id')
        ->where('ur.role_id', 1)
        ->where('u.blocked', false)
        ->select([
            'u.id',
            'u.name',
            'u.email',
            'u.phone',
            'u.profile_image',
            'u.created_at',
        ])
        ->orderByDesc('u.created_at')
        ->paginate($perPage);
}


public function getInActiveRiders(int $perPage = 100)
{
    return DB::table('users as u')
        ->join('user_roles as ur', 'ur.user_id', '=', 'u.id')
        ->where('ur.role_id', 1)
        ->where('u.blocked', true)
        ->select([
            'u.id',
            'u.name',
            'u.email',
            'u.phone',
            'u.profile_image',
            'u.created_at',
        ])
        ->orderByDesc('u.created_at')
        ->paginate($perPage);
}

  public function findRiderById(int $riderId): User
    {
        return User::query()
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->where('user_roles.role_id', 1) // Rider
            ->where('users.id', $riderId)
            ->select('users.*')
            ->firstOrFail();
    }

      public function getRiderRideStats(int $riderId): array
    {
        $stats = DB::table('rides')
            ->where('user_id', $riderId)
            ->selectRaw("
                COUNT(*) as total_rides,
                SUM(CASE WHEN status IN ('driver_on_way','on_going') THEN 1 ELSE 0 END) as live_rides,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_rides,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_rides
            ")
            ->first();

        return [
            'total_rides'     => (int) $stats->total_rides,
            'live_rides'      => (int) $stats->live_rides,
            'completed_rides' => (int) $stats->completed_rides,
            'cancelled_rides' => (int) $stats->cancelled_rides,
        ];
    }
}
