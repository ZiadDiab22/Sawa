<?php

namespace App\Http\Controllers\Api\Passenger;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UserRegisterRequest;
use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\Auth\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $registerService;
    protected AuthService $authService;

    public function __construct(UserService $registerService, AuthService $authService)
    {
        $this->registerService = $registerService;
        $this->authService = $authService;
    }

    public function register(UserRegisterRequest $request)
    {
        $result = $this->registerService->register($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully',
            'data' => $result
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
        ]);

        $user = User::where('phone', $request->phone)->first();
        $hasRole = $user->roles()->where('role_id', 1)->exists();

        if (!$hasRole) {
            return response()->json([
                'status' => false,
                'message' => 'this api for users ( passengers ) only',
            ], 403);
        }

        $status = $this->authService->sendOtp($request->phone);

        return response()->json([
            'status' => $status,
            'message' => 'Message will be sent to your phone number',
        ]);
    }
}
