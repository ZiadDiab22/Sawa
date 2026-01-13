<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Services\User\Driver\DriverDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function show(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        return response()->json([
            'status' => true,
            'data' => $this->service->stats(
                Auth::id(),
                $request->date
            )
        ]);
    }
}
