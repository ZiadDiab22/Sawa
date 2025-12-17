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

        $status = $this->authService->sendOtp($request->phone);

        return response()->json([
            'status' => $status,
            'message' => 'Message will be sent to your phone number',
        ]);
    }

    public function login(UserLoginRequest $request)
    {
        $token = $this->authService->login($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Logged in Successfully',
            'token' => $token
        ]);
    }
}
