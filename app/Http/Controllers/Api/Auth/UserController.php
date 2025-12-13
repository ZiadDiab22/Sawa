<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UserRegisterRequest;
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
}
