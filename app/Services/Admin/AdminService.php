<?php

namespace App\Services\Admin;

use App\Models\DriverDocument;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use App\Repositories\Admin\AdminRepository;

class AdminService
{
  protected UserRepository $userRepository;

   protected AdminRepository $AdminRepository;



  public function __construct(UserRepository $userRepository,AdminRepository $AdminRepository)
  {
    $this->userRepository = $userRepository;
    $this->AdminRepository = $AdminRepository;

  }

  public function login(array $data)
  {
    $user = $this->userRepository->findByEmail($data['email']);

    if (!$user || !Hash::check($data['password'], $user->password)) {
      throw new \Exception('Invalid Cardential');
    }

    if ($user->blocked) {
      throw new \Exception('This account is blocked');
    }

    $hasRole = $user->roles()->where('role_id', 4)->exists();

    if (!$hasRole) {
      throw new \Exception('this api for admins only');
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return [
      'user' => $user,
      'token' => $token,
    ];
  }

  public function updateProfile($userId, array $data)
  {
    $updateData = $data;

    if (isset($data['password'])) {
      $updateData['password'] = Hash::make($data['password']);
    }

    return $this->userRepository->update($userId, $updateData);
  }
//test
    public function getApprovedDriversCount(): int
    {
        return $this->AdminRepository->countApprovedDrivers();
    }

    public function getPendingDriversCount(): int
    {
        return $this->AdminRepository->countPendingDrivers();
    }

    public function getPassengersCount(): int
    {
        return $this->AdminRepository->countPassengers();
    }

    public function getTotalRidesCount(): int
    {
        return $this->AdminRepository->countAllRides();
    }

    public function getCompletedRidesCount(): int
    {
        return $this->AdminRepository->countCompletedRides();
    }

    public function getLastFiveRides()
    {
        return $this->AdminRepository->getLastFiveRidesWithPassengerAndDriver();
    }
//vehicle_types
    public function getAllVehicleTypes()
    {
        return $this->AdminRepository->getAllVehicleTypes();
    }

    public function toggleVehicleTypeStatus(int $vehicleTypeId): ?bool
    {
        return $this->AdminRepository ->toggleVehicleStatus($vehicleTypeId);
    }

     public function deleteVehicleTypes(array $ids): int
    {
        return $this->AdminRepository->deleteByIds($ids);
    }

    public function searchVehicleTypes(string $search)
    {
        return $this->AdminRepository->search($search);
    }

    public function createVehicleMake(array $data): int
    {
        return $this->AdminRepository->createVehicleMake($data);
    }

    public function searchVehicleMakes(string $search)
{
    return $this->AdminRepository->searchVehicleMakes($search);
}

    public function getAllVehicleMakes()
{
    return $this->AdminRepository->getAllVehicleMakes();
}
public function toggleVehicleMakeStatus(int $vehicleMakeId): ?bool
{
    return $this->AdminRepository
        ->toggleVehicleMakeStatus($vehicleMakeId);
}
public function deleteVehicleMakes(array $ids): int
{
    return $this->AdminRepository
        ->deleteVehicleMakesByIds($ids);
}

public function getVehicleMakesByType(string $type)
{
    return $this->AdminRepository
        ->getVehicleMakesByType($type);
}


//CancellationReason

    public function list()
    {
        return $this->AdminRepository->getAll();
    }

    public function create(array $data)
    {
        $this->validate($data);

        return DB::transaction(function () use ($data) {
            return $this->AdminRepository->create($data);
        });
    }

    public function update(int $id, array $data)
    {
        $this->validate($data, $id);

        return DB::transaction(function () use ($id, $data) {
            return $this->AdminRepository->update($id, $data);
        });
    }

 public function bulkDelete(array $ids): int
{
    validator(['ids' => $ids], [
        'ids'   => 'required|array|min:1',
        'ids.*' => 'integer|exists:cancellation_reasons,id',
    ])->validate();

    return DB::transaction(function () use ($ids) {
        return $this->AdminRepository->deleteReasonByIds($ids);
    });
}


    public function toggleStatus(int $id)
    {
        return $this->AdminRepository->toggleStatus($id);
    }

    protected function validate(array $data, ?int $id = null): void
    {
        validator($data, [
            'reason'    => 'required|string|max:255',
            'user_type' => 'required|in:passenger,driver,system',
            'is_active' => 'sometimes|boolean',
        ])->validate();


    }public function search(?string $search)
{
    $search = trim($search);

    if ($search === '') {
        return $this->AdminRepository->getAll();
    }

    return $this->AdminRepository->SearchCancellationReason($search);
}

//Driver Management
//DriversList

    public function listDrivers()
    {
        $drivers = $this->AdminRepository->getDriversList();

        return $drivers->through(function ($driver) {
            return [
                'id' => $driver->id,
                'driver' => [
                    'name'  => $driver->user->name,
                    'email' => $driver->user->email,
                    'phone' => $driver->user->phone,
                ],
                'vehicle' => [
                    'make'         => $driver->vehicleMake?->name,
                    'model'        => $driver->vehicle_model,
                    'plate_number' => $driver->vehicle_plate_number,
                    'year'         => $driver->vehicle_year,
                ],
                'rides' => [
                    'live'       => (int) $driver->live_rides,
                    'completed'  => (int) $driver->completed_rides,
                    'cancelled'  => (int) $driver->cancelled_rides,
                    'rejected'   => (int) $driver->rejected_rides,
                ],
                'driver_status'    => $driver->status,
                'documents_status' => $driver->documents_status,
            ];
        });
    }


    public function approveDriver(int $driverId): void
{
    $approved = $this->AdminRepository->approveDriver($driverId);

    if (!$approved) {
        throw new \Exception('Driver not found or already approved');
    }
}


public function suspendDriver(int $driverId): void
{
    $suspended = $this->AdminRepository->suspendDriver($driverId);

    if (!$suspended) {
        throw new \Exception('Driver not found or already suspended');
    }
}

public function listActiveDrivers()
{
    $drivers = $this->AdminRepository->getActiveDriversList();

    return $drivers->through(function ($driver) {
        return [
            'id' => $driver->id,
            'driver' => [
                'name'  => $driver->user->name,
                'email' => $driver->user->email,
                'phone' => $driver->user->phone,
            ],
            'vehicle' => [
                'make'         => $driver->vehicleMake?->name,
                'model'        => $driver->vehicle_model,
                'plate_number' => $driver->vehicle_plate_number,
                'year'         => $driver->vehicle_year,
            ],
            'rides' => [
                'live'       => (int) $driver->live_rides,
                'completed'  => (int) $driver->completed_rides,
                'cancelled'  => (int) $driver->cancelled_rides,
                'rejected'   => (int) $driver->rejected_rides,
            ],
            'driver_status'    => $driver->status,
            'documents_status' => $driver->documents_status,
        ];
    });
}

public function listInactiveDrivers()
{
    $drivers = $this->AdminRepository->getInactiveApprovedDrivers();

    return $drivers->through(function ($driver) {
        return [
            'id' => $driver->id,
            'driver' => [
                'name'  => $driver->user->name,
                'email' => $driver->user->email,
                'phone' => $driver->user->phone,
            ],
            'vehicle' => [
                'make'         => $driver->vehicleMake?->name,
                'model'        => $driver->vehicle_model,
                'plate_number' => $driver->vehicle_plate_number,
                'year'         => $driver->vehicle_year,
            ],
            'rides' => [
                'live'      => (int) $driver->live_rides,
                'completed' => (int) $driver->completed_rides,
                'cancelled' => (int) $driver->cancelled_rides,
                'rejected'  => (int) $driver->rejected_rides,
            ],
            'driver_status'    => $driver->status,        // approved
            'is_status'        => $driver->is_status,     // inactive
            'documents_status' => $driver->documents_status,
        ];
    });
}

public function listPendingDrivers()
{
    $drivers = $this->AdminRepository->getPendingDrivers();

    return $drivers->through(function ($driver) {
        return [
            'id' => $driver->id,
            'driver' => [
                'name'  => $driver->user->name,
                'email' => $driver->user->email,
                'phone' => $driver->user->phone,
            ],
            'vehicle' => [
                'make'         => $driver->vehicleMake?->name,
                'model'        => $driver->vehicle_model,
                'plate_number' => $driver->vehicle_plate_number,
                'year'         => $driver->vehicle_year,
            ],
            'rides' => [
                'live'      => (int) $driver->live_rides,
                'completed' => (int) $driver->completed_rides,
                'cancelled' => (int) $driver->cancelled_rides,
                'rejected'  => (int) $driver->rejected_rides,
            ],
            'driver_status'    => $driver->status,          // pending
            'documents_status' => $driver->documents_status // pending
        ];
    });
}

    public function toggleReceivingRequests(int $driverId, bool $status)
    {
        // شرط أمان منطقي
        $driver = $this->AdminRepository->updateCanReceiveRequests(
            $driverId,
            $status
        );

        return $driver;
    }

    public function searchDrivers(string $search)
{
    $drivers = $this->AdminRepository->searchDrivers($search);

    return $drivers->through(function ($driver) {
        return [
            'id' => $driver->id,
            'driver' => [
                'name'  => $driver->user->name,
                'email' => $driver->user->email,
                'phone' => $driver->user->phone,
            ],
            'vehicle' => [
                'type'         => $driver->vehicleType?->name,
                'make'         => $driver->vehicleMake?->name,
                'model'        => $driver->vehicle_model,
                'plate_number' => $driver->vehicle_plate_number,
                'year'         => $driver->vehicle_year,
            ],
            'status' => [
                'driver_status'    => $driver->status,
                'is_status'        => $driver->is_status,
            ],
        ];
    });
}

public function getDocumentsByDriverId(int $driverId): array
{
    $docs = $this->AdminRepository->findByDriverProfileId($driverId);

    return $docs->map(function ($doc) {
        return [
            'id' => $doc->id,
            'type' => $doc->type,
            'file_path' => $doc->file_path
                ? array_map(fn ($f) => asset('storage/' . $f), $doc->file_path)
                : [],
            'expires_at' => $doc->expires_at,
            'status' => $doc->status,
            'created_at' => $doc->created_at,
        ];
    })->toArray();
}

public function approveDocumentByAdmin(int $id): array
{
    $doc = $this->AdminRepository->updateStatus($id, 'approved');

    return $this->formatDocumentResponse($doc);
}

public function rejectDocumentByAdmin(int $id): array
{
    $doc = $this->AdminRepository->updateStatus($id, 'rejected');

    return $this->formatDocumentResponse($doc);
}

private function formatDocumentResponse(DriverDocument $doc): array
{
    return [
        'id' => $doc->id,
        'driver_id' => $doc->driver_id,
        'type' => $doc->type,
        'file_path' => $doc->file_path
            ? array_map(fn ($f) => asset('storage/' . $f), $doc->file_path)
            : [],
        'expires_at' => $doc->expires_at,
        'status' => $doc->status,
        'created_at' => $doc->created_at,
        'updated_at' => $doc->updated_at,
    ];
}


}
