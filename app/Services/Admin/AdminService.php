<?php

namespace App\Services\Admin;

use Exception;
use Carbon\Carbon;
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
            throw new \Exception('Invalid credentials', 401);
        }

        if ($user->blocked) {
            throw new \Exception('This account is blocked', 403);
        }

        $hasRole = $user->roles()->where('role_id', 4)->exists();
        if (!$hasRole) {
            throw new \Exception('This api for admins only', 403);
        }

        return [
            'user'  => $user,
//            'token' => $user->createToken('api-token')->plainTextToken,
        ];

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


//add


public function logout(): void
{
    $user = auth()->user();

    if (!$user) {
        throw new Exception('Unauthenticated');
    }

    $user->currentAccessToken()->delete();
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

  public function getDashboardData(): array
    {
        return $this->AdminRepository->getDashboardStats();
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
                 'document_ids' => $driver->documents
                ->pluck('id')
                ->values(),
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


    public function approveDriver(int $driverId)
{
     $driver = $this->AdminRepository->approveDriver($driverId);

    if (!$driver) {
        throw new \Exception('Driver not found or already approved');
    }

    return $driver;
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

public function toggleBannedDriver(int $driverId)
{
    return $this->AdminRepository->toggleBannedDriver($driverId);
}


    public function driverDetails(int $driverProfileId): array
    {
        $driver = $this->AdminRepository->getDriverProfile($driverProfileId);
        $user = $driver->user;

        $rideStats = $this->AdminRepository->rideStats($user->id);
        $earnings  = $this->AdminRepository->earnings($user->id);

        $docs = $driver->documents;

        $documentApproved = $docs
            ->whereIn('type', ['license', 'driver_id'])
            ->every(fn($d) => $d->status === 'approved');

        $vehicleDocApproved = $docs
            ->where('type', 'insurance')
            ->every(fn($d) => $d->status === 'approved');

        return [
            'driver' => [
                'id' => $driver->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_image' => $user->profile_image
                    ? asset('storage/'.$user->profile_image)
                    : null,
                'can_receive_requests' => $driver->can_receive_requests,
                'joining_date' => $user->created_at,
            ],

            'ride_information' => $rideStats,

            'earnings' => $earnings,

            'verification_status' => [
                'document_verification' => $documentApproved,
                'vehicle_document_verification' => $vehicleDocApproved,
                'is_driver_active' => $driver->is_status === 'active',
            ],

            'vehicle_information' => [
                'make' => $driver->vehicleMake?->name,
                'model' => $driver->vehicle_model,
                'vehicle_number' => $driver->vehicle_plate_number,
                'year' => $driver->vehicle_year,
                'color' => $driver->vehicle_color,
            ],
        ];
    }

public function driverRides(int $driverProfileId, ?string $status = null): array
{
    $rides = $this->AdminRepository->getDriverRides($driverProfileId, $status);

    return [
        'data' => collect($rides->items())->map(function ($ride) {

            return [
                'reservation_code' => $ride->reservation_code,

                'driver' => [
                    'name' => $ride->driver_name,
                    'avatar' => $ride->driver_avatar
                        ? asset('storage/' . $ride->driver_avatar)
                        : null,
                ],

                'rider' => [
                    'name' => $ride->rider_name,
                ],

                'service' => $ride->service ?? 'Taxi',

                'vehicle' => [
                    'type'   => $ride->vehicle_type,
                    'make'   => $ride->vehicle_make,
                    'model'  => $ride->vehicle_model,
                    'number' => $ride->vehicle_plate_number,
                ],

                'pickup_location' => [
                    'lat' => (float) $ride->pickup_lat,
                    'lng' => (float) $ride->pickup_lng,
                ],

                'destination' => [
                    'lat' => (float) $ride->drop_lat,
                    'lng' => (float) $ride->drop_lng,
                ],

                'ride_fare' => number_format((float) $ride->price, 2) . ' USD',

                'booking_date' => $ride->booking_date,

                'status' => ucfirst(str_replace('_', ' ', $ride->status)),

                'payment' => [
                    'method' => 'cash', // حالياً ثابتة
                ],
            ];
        }),

        'pagination' => [
            'current_page' => $rides->currentPage(),
            'last_page' => $rides->lastPage(),
            'per_page' => $rides->perPage(),
            'total' => $rides->total(),
        ],
    ];
}

public function riderRides(int $riderId, ?string $status = null): array
{
    $rides = $this->AdminRepository->getRiderRides($riderId, $status);

    return [
        'data' => collect($rides->items())->map(function ($ride) {

            return [
                'reservation_code' => $ride->reservation_code,

                'driver' => [
                    'name' => $ride->driver_name,
                    'avatar' => $ride->driver_avatar
                        ? asset('storage/' . $ride->driver_avatar)
                        : null,
                ],

                'rider' => [
                    'name' => $ride->rider_name,
                ],

                'service' => $ride->service ?? 'Taxi',

                'vehicle' => [
                    'type'   => $ride->vehicle_type,
                    'make'   => $ride->vehicle_make,
                    'model'  => $ride->vehicle_model,
                    'number' => $ride->vehicle_plate_number,
                ],

                'pickup_location' => [
                    'lat' => (float) $ride->pickup_lat,
                    'lng' => (float) $ride->pickup_lng,
                ],

                'destination' => [
                    'lat' => (float) $ride->drop_lat,
                    'lng' => (float) $ride->drop_lng,
                ],

                'ride_fare' => number_format((float) $ride->price, 2) . ' USD',

                'booking_date' => $ride->booking_date,

                'status' => ucfirst(str_replace('_', ' ', $ride->status)),

                'payment' => [
                    'method' => 'cash',
                ],
            ];
        }),

        'pagination' => [
            'current_page' => $rides->currentPage(),
            'last_page' => $rides->lastPage(),
            'per_page' => $rides->perPage(),
            'total' => $rides->total(),
        ],
    ];
}
    //infinacial

    public function getWalletDashboard(int $driverId, int $perPage = 10): array
{
    $walletBalance = $this->AdminRepository->getDriverWallet($driverId);

    $totals = $this->AdminRepository->getTotals($driverId);

    $transactions = $this->AdminRepository->getTransactions($driverId, $perPage);

    $transactions->getCollection()->transform(function ($t) {
    return [
        'id' => $t->id,
        'booking_id' => $t->ride?->code ?? $t->ride_id,
        'credit' => $t->type === 'credit' ? (float) $t->amount : 0,
        'debit'  => $t->type === 'debit' ? (float) $t->amount : 0,
        'type' => ucfirst($t->type),
        'description' => match ($t->reason) {
            'ride_commission' => 'Ride commission',
            'wallet_charge' => 'Wallet charge',
            'cancellation_penalty' => 'Cancellation penalty',
            'manual_adjustment' => 'Manual adjustment',
        },
        'date' => $t->created_at->format('d M Y - h:i A'),
    ];
});
    return [
        'wallet_balance' => (float) $walletBalance,
        'total_credit' => (float) $totals['total_credit'],
        'total_debit' => (float) $totals['total_debit'],
        'transactions' => $transactions,
    ];
}

    //Rider Managment

    public function ridersList(int $perPage = 100): array
    {
        $riders = $this->AdminRepository->getRiders($perPage);

        return [
            'data' => collect($riders->items())->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'profile_image' => $user->profile_image
                    ? asset('storage/' . $user->profile_image)
                    : null,
                    'email' => $user->email,
                    'phone' => $user->phone,

                    'registered_on' => [
                    'date' => Carbon::parse($user->created_at)->format('d M Y'),
                    'time' => Carbon::parse($user->created_at)->format('h:i A'),
                    ],


                    'status' => $user->blocked ? 'inactive' : 'active',
                ];
            }),

            'meta' => [
                'current_page' => $riders->currentPage(),
                'per_page' => $riders->perPage(),
                'total' => $riders->total(),
                'last_page' => $riders->lastPage(),
            ],
        ];
    }


public function searchRiders(string $search)
{
        return $this->AdminRepository->searchRiders(1, $search);

}

    public function deleteRider(array $ids): array
    {
        return $this->AdminRepository->deleteUsersByIds($ids);
    }
    public function toggleRiderBlockStatus(int $userId): array
    {
        return $this->AdminRepository->toggleRiderBlock($userId);
    }


    public function activeRidersList(int $perPage = 100): array
{
    $riders = $this->AdminRepository->getActiveRiders($perPage);

    return [
        'data' => collect($riders->items())->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'profile_image' => $user->profile_image
                    ? asset('storage/' . $user->profile_image)
                    : null,
                'email' => $user->email,
                'phone' => $user->phone,

                'registered_on' => [
                    'date' => Carbon::parse($user->created_at)->format('d M Y'),
                    'time' => Carbon::parse($user->created_at)->format('h:i A'),
                ],

                'status' => 'active',
            ];
        }),

        'meta' => [
            'current_page' => $riders->currentPage(),
            'per_page' => $riders->perPage(),
            'total' => $riders->total(),
            'last_page' => $riders->lastPage(),
        ],
    ];
}


public function InactiveRidersList(int $perPage = 100): array
{
    $riders = $this->AdminRepository->getInActiveRiders($perPage);

    return [
        'data' => collect($riders->items())->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'profile_image' => $user->profile_image
                    ? asset('storage/' . $user->profile_image)
                    : null,
                'email' => $user->email,
                'phone' => $user->phone,

                'registered_on' => [
                    'date' => Carbon::parse($user->created_at)->format('d M Y'),
                    'time' => Carbon::parse($user->created_at)->format('h:i A'),
                ],

                'status' => 'inactive',
            ];
        }),

        'meta' => [
            'current_page' => $riders->currentPage(),
            'per_page' => $riders->perPage(),
            'total' => $riders->total(),
            'last_page' => $riders->lastPage(),
        ],
    ];
}


public function getRiderProfile(int $riderId): array
    {
        $rider = $this->AdminRepository->findRiderById($riderId);
        $stats = $this->AdminRepository->getRiderRideStats($riderId);

        return [
            'id' => $rider->id,
            'name' => $rider->name,
            'profile_image' => $rider->profile_image
                ? asset('storage/' . $rider->profile_image)
                : null,

            'personal_information' => [
                'email' => $rider->email,
                'phone' => $rider->phone,
                'gender' => $rider->gender ?? 'unknown',
                'joining_date' => Carbon::parse($rider->created_at)->format('Y-m-d'),
            ],

            'ride_information' => [
                'live_rides'      => $stats['live_rides'],
                'completed_rides' => $stats['completed_rides'],
                'cancelled_rides' => $stats['cancelled_rides'],
                'total_rides'     => $stats['total_rides'],
            ],
        ];
    }

}
