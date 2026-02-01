<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\Admin\AdminService;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function login(Request $request,AuthService $authService)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
            'phone' => 'required|string',
        ]);

        try {
            $result = $this->adminService->login($data);

            $status = $authService->sendOtp($request->phone);

            return response()->json([
                'status' => $status,
                'message' => 'OTP will be sent to your email address',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }
    //add

    public function logout()
{
    $this->adminService->logout();

    return response()->json([
        'message' => 'Logged out successfully'
    ]);
}



    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = Auth::user();

        $validated = $request->validated();

        $updated = $this->adminService->updateProfile($user->id, $validated);

        if (!$updated) {
            return response()->json(['message' => 'something went error'], 400);
        }

        return response()->json([
            // 'status' => true,
            'message' => 'update successful',
            'user' => $updated,
        ]);
    }
//test

  public function dashboard()
    {
        return response()->json(
            $this->adminService->getDashboardData()
        );
    }

    //vehicle_types

    public function allVehicleTypes()
    {
        return response()->json([
            'vehicle_types' => $this->adminService->getAllVehicleTypes()
        ]);
    }

    public function toggleVehicleTypeStatus($id)
    {
        $newStatus = $this->adminService->toggleVehicleTypeStatus($id);

        if ($newStatus === null) {
            return response()->json([
                'message' => 'Vehicle type not found'
            ], 404);
        }

        return response()->json([
            'vehicle_type_id' => $id,
            'is_active' => $newStatus
        ]);
    }

    public function deleteVehicleTypes(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:vehicle_types,id',
        ]);

        $deletedCount = $this->adminService
            ->deleteVehicleTypes($request->ids);

        return response()->json([
            'deleted_count' => $deletedCount
        ]);
    }
    public function searchVehicleTypes(Request $request)
{
    $request->validate([
        'search' => 'required|string'
    ]);

    $results = $this->adminService
        ->searchVehicleTypes($request->search);

    return response()->json([
        'count' => $results->count(),
        'data'  => $results
    ]);
}

    public function storeVehicleMake(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|unique:vehicle_makes,name',
            'type'      => 'required|string',
            'is_active' => 'required|boolean',
        ]);

        $vehicleMakeId = $this->adminService
            ->createVehicleMake($validated);

        return response()->json([
            'message' => 'Vehicle make created successfully',
            'data' => [
                'id' => $vehicleMakeId,
                'name' => $validated['name'],
                'type' => $validated['type'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]
        ], 201);
    }

    public function searchVehicleMakes(Request $request)
{
    $request->validate([
        'search' => 'required|string'
    ]);

    $results = $this->adminService
        ->searchVehicleMakes($request->search);

    return response()->json([
        'count' => $results->count(),
        'data'  => $results
    ]);
}

    public function getAllVehicleMakes()
{
    $vehicleMakes = $this->adminService->getAllVehicleMakes();

    return response()->json([
        'count' => $vehicleMakes->count(),
        'data'  => $vehicleMakes
    ]);
}

public function toggleVehicleMakeStatus($id)
{
    $newStatus = $this->adminService
        ->toggleVehicleMakeStatus((int) $id);

    if ($newStatus === null) {
        return response()->json([
            'message' => 'Vehicle make not found'
        ], 404);
    }

    return response()->json([
        'vehicle_make_id' => (int) $id,
        'is_active' => $newStatus
    ]);
}

public function deleteVehicleMakes(Request $request)
{
    $request->validate([
        'ids'   => 'required|array|min:1',
        'ids.*' => 'integer|exists:vehicle_makes,id',
    ]);

    $deletedCount = $this->adminService
        ->deleteVehicleMakes($request->ids);

    return response()->json([
        'deleted_count' => $deletedCount
    ]);
}
public function filterVehicleMakesByType(Request $request)
{
    $request->validate([
        'type' => 'required|string'
    ]);

    $vehicleMakes = $this->adminService
        ->getVehicleMakesByType($request->type);

    return response()->json([
        'count' => $vehicleMakes->count(),
        'data'  => $vehicleMakes
    ]);
}

//CancellationReason


public function index()
    {
        return response()->json([
            'data' => $this->adminService->list()
        ]);
    }

    public function store(Request $request)
    {
            $reason = $this->adminService->create($request->all());

            return response()->json([
                'message' => 'Cancellation reason created successfully',
                'data' => $reason
            ], 201);
    }

    public function update(Request $request, int $id)
    {
        try {
            $reason = $this->adminService->update($id, $request->all());

            return response()->json([
                'message' => 'Cancellation reason updated',
                'data' => $reason
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Record not found'
            ], 404);

        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    public function bulkDestroy(Request $request)
{
    try {
        $deletedCount = $this->adminService->bulkDelete($request->ids);

        return response()->json([
            'message' => "{$deletedCount} records deleted successfully"
        ]);

    } catch (ValidationException $e) {
        return response()->json([
            'errors' => $e->errors()
        ], 422);

    } catch (\Throwable $e) {
        report($e);

        return response()->json([
            'message' => 'Something went wrong'
        ], 500);
    }
}


    public function toggleStatus(int $id)
    {
        try {
            $reason = $this->adminService->toggleStatus($id);

            return response()->json([
                'message' => 'Status updated',
                'data' => $reason
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Record not found'
            ], 404);
        }
    }

    public function SearchCancellationReason(Request $request)
{
    return response()->json([
        'data' => $this->adminService->search($request->query('search'))
    ]);
}

//Driver Management
//DriversList

    public function listDrivers()
    {
        return response()->json(
            $this->adminService->listDrivers()
        );
    }

    public function approveDriver($id)
{
    try {
        $this->adminService->approveDriver((int) $id);

        return response()->json([
            'message' => 'Driver approved successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => $e->getMessage()
        ], 400);
    }
}

public function suspendDriver($id)
{
    try {
        $this->adminService->suspendDriver((int) $id);

        return response()->json([
            'message' => 'Driver suspended successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => $e->getMessage()
        ], 400);
    }
}

public function listActiveDrivers()
{
    return response()->json(
        $this->adminService->listActiveDrivers()
    );
}

public function listInactiveDrivers()
{
    return response()->json(
        $this->adminService->listInactiveDrivers()
    );
}

public function listPendingDrivers()
{
    return response()->json(
        $this->adminService->listPendingDrivers()
    );
}

 public function toggleReceivingRequests(Request $request, int $driverId)
    {
        $validated = $request->validate([
            'can_receive_requests' => ['required', 'boolean'],
        ]);

        $driver = $this->adminService->toggleReceivingRequests(
            $driverId,
            $validated['can_receive_requests']
        );

        return response()->json([
            'message' => 'Driver receiving requests status updated successfully',
            'data' => [
                'driver_id' => $driver->id,
                'can_receive_requests' => $driver->can_receive_requests,
            ],
        ]);
    }


    public function searchDrivers(Request $request)
{
    $request->validate([
        'search' => 'required|string'
    ]);

    return response()->json([
        'data' => $this->adminService
            ->searchDrivers($request->search)
    ]);
}


public function documentsByDriver(int $driverId)
{
    return response()->json([
        'driver_id' => $driverId,
        'documents' => $this->adminService
            ->getDocumentsByDriverId($driverId)
    ]);
}

public function approveDocument(int $id)
{
    $doc = $this->adminService
        ->approveDocumentByAdmin($id);

    return response()->json([
        'message' => 'Document approved successfully',
        'data' => $doc
    ]);
}

public function rejectDocument(int $id)
{
    $doc = $this->adminService
        ->rejectDocumentByAdmin($id);

    return response()->json([
        'message' => 'Document rejected successfully',
        'data' => $doc
    ]);
}


public function toggleBannedDriver(int $id)
{
    $driver = $this->adminService->toggleBannedDriver($id);

    return response()->json([
        'message' => 'Driver ban status updated',
        'data' => [
            'driver_id' => $driver->id,
            'is_status' => $driver->is_status,
            'can_receive_requests' => $driver->can_receive_requests,
        ]
    ]);
}

    public function showDriver(int $driverProfileId)
    {
        return response()->json([
            'message' => 'Driver details retrieved successfully',
            'data' => $this->adminService->driverDetails($driverProfileId)
        ]);
    }

     public function driverRides(int $driverProfileId, AdminService $service)
    {
        return response()->json([
            'status' => true,
            'data'   => $service->driverRides($driverProfileId),
        ]);
    }
}
