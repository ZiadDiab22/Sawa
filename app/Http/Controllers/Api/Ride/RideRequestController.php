<?php

namespace App\Http\Controllers\Api\Ride;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ride\CalculateRideRequest;
use App\Repositories\Ride\RideRequestRepository;
use App\Services\Ride\DistanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RideRequestController extends Controller
{
    public function store(
        CalculateRideRequest $request,
        DistanceService $service,
        RideRequestRepository $repository
    ) {
        $estimate = $service->estimate($request->validated());

        $ride = $repository->create([
            ...$request->validated(),
            'user_id'         => Auth::id(),
            'vehicle_type_id' => 1,
            'distance_km' => $estimate['rideDistance'],
            'price'       => $estimate['price'],
            'duration_minutes' => $estimate['duration'],
        ]);

        return response()->json([
            'status' => true,
            'data' => $ride,
        ], 201);
    }

    public function skip(Request $request, DistanceService $service)
    {
        $request->validate([
            'ride_request_id' => ['required', 'integer', 'exists:ride_requests,id']
        ]);

        $response = $service->skip(
            $request->ride_request_id,
            Auth::id()
        );

        return response()->json([
            'status' => true,
            'data' => $response,
        ], 201);
    }
}
