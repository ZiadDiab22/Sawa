<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UserRegisterRequest;
use App\Services\Auth\AuthService;
use App\Services\Auth\UserService;
use App\Services\User\Driver\DriverService;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    protected UserService $registerService;
    protected AuthService $authService;
    protected DriverService $driverService;

    public function __construct(UserService $registerService, AuthService $authService,DriverService $driverService)
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
}
