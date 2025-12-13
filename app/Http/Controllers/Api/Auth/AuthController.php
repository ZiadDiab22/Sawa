<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Auth\AuthService;
use App\Services\Auth\UserService;

class AuthController extends Controller
{
    protected UserService $registerService;
    protected AuthService $authService;

    public function __construct(UserService $registerService, AuthService $authService)
    {
        $this->registerService = $registerService;
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
        ]);

        $status = $this->authService->sendOtp($request->phone);

        return response()->json([
            'status' => $status,
            'message' => 'Message will be sent to your phone number',
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
            'otp'   => 'required|string',
        ]);

        $token = $this->authService->verifyOtp($request->phone, $request->otp);

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $this->authService->logout($user);

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }

}
