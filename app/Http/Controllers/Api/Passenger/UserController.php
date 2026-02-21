<?php

namespace App\Http\Controllers\Api\Passenger;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UserLoginRequest;
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
        $this->registerService->register($request->validated());

        $status = $this->authService->sendOtp( $request->phone,
        $request->channel ?? 'email');

        return response()->json([
            'status' => $status,
            'message' => 'OTP has been sent successfully',
        ]);
    }



    public function login(UserLoginRequest $request)
    {
        $status = $this->authService->sendOtp( $request->phone,
        $request->channel ?? 'email');

        return response()->json([
            'status' => true,
            'message' => 'OTP has been sent successfully',
        ]);
    }
}
