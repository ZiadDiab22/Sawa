<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UserRegisterRequest;
use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\Auth\UserService;
use App\Services\User\Driver\DriverService;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    protected UserService $registerService;
    protected AuthService $authService;
    protected DriverService $driverService;

    public function __construct(UserService $registerService, AuthService $authService, DriverService $driverService)
    {
        $this->registerService = $registerService;
        $this->authService = $authService;
        $this->driverService = $driverService;
    }

    public function register(UserRegisterRequest $request)
    {
        $result = $this->driverService->register($request->validated());

        return response()->json([
            'status' => true,
            'data' => $result
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
        ]);

        $user = User::where('phone', $request->phone)->first();
        $hasRole = $user->roles()->where('role_id', 2)->exists();

        if (!$hasRole) {
            return response()->json([
                'status' => false,
                'message' => 'this api for accepted drivers only',
            ], 403);
        }

        $status = $this->authService->sendOtp($request->phone);

        return response()->json([
            'status' => $status,
            'message' => 'Message will be sent to your phone number',
        ]);
    }

    public function toggleStatus()
    {
        $driver = $this->driverService->toggleStatus();

        return response()->json([
            'message' => 'Done Successfully',
            'status' => $driver->is_status,
        ]);
    }
}
