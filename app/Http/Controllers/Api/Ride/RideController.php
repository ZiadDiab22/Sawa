<?php

namespace App\Http\Controllers\Api\Ride;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ride\CompleteRideRequest;
use App\Services\Ride\RideService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RideController extends Controller
{
    public function start(Request $request, RideService $service)
    {
        $request->validate([
            'ride_id' => ['required', 'integer', 'exists:rides,id'],
        ]);

        $ride = $service->start(
            $request->ride_id,
            Auth::id()
        );

        return response()->json([
            'status' => true,
            'data' => $ride->makeHidden('code'),
        ]);
    }

    public function complete(CompleteRideRequest $request, RideService $service)
    {
        $ride = $service->complete(
            $request->validated(),
            Auth::id()
        );

        return response()->json([
            'status' => true,
            'data' => $ride,
        ]);
    }
}
