<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Services\User\Driver\DriverDashboardService;
use Illuminate\Http\Request;

class DriverDashboardController extends Controller
{
    public function __construct(
        private DriverDashboardService $service
    ) {}

    public function index(Request $request)
    {
        $driverId = $request->user()->id;

        return response()->json([
            'status' => true,
            'data'   => $this->service->handle($driverId),
        ]);
    }
}
