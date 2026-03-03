<?php

namespace App\Http\Controllers\Api\Ride;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ride\CalculateRideRequest;
use App\Repositories\Ride\RideRequestRepository;
use App\Services\Ride\DistanceService;
use App\Services\Ride\RideBroadcastService;
use App\Services\Ride\RideRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RideRequestController extends Controller
{
    public function store(
        CalculateRideRequest $request,
        DistanceService $service,
        RideRequestRepository $repository,
        RideBroadcastService $broadcastService
    ) {
        $estimate = $service->estimate($request->validated());

        $rideRequest = $repository->create([
            ...$request->validated(),
            'user_id'         => Auth::id(),
            'vehicle_type_id' => 1,
            'distance_km' => $estimate['rideDistance'],
            'price'       => $estimate['price'],
            'duration_minutes' => $estimate['duration'],
        ]);

        try {
        $user = $rideRequest->user;
        if ($user && $user->fcm_token) { // تأكد أن المستخدم لديه FCM token
            $title = "طلبك تم تسجيله";
            $body  = "تم استلام طلبك بنجاح وسيتم التواصل معك حال وجود سائق متاح.";

            $firebaseService = new \App\Services\FirebaseNotificationService();
            $firebaseService->send($user->fcm_token, $title, $body);
        }
    } catch (\Exception $e) {
        \Log::error("Failed to send ride creation notification: ".$e->getMessage());
    }
        $broadcastService->sendToNearbyDrivers($rideRequest);

        return response()->json([
            'status' => true,
            'data' => $rideRequest,
        ], 201);
    }

    public function getPrice(
        CalculateRideRequest $request,
        DistanceService $service,
    ) {
        $estimate = $service->estimate($request->validated());

        return response()->json([
            'status' => true,
            'distance_km' => $estimate['rideDistance'],
            'price'       => $estimate['price'],
            'duration_minutes' => $estimate['duration'],
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

    public function accept(Request $request, DistanceService $service)
    {
        $request->validate([
            'ride_request_id' => ['required', 'integer', 'exists:ride_requests,id']
        ]);

        $response = $service->accept(
            $request->ride_request_id,
            Auth::id()
        );

        return response()->json([
            'status' => true,
            'data' => $response->makeHidden('code'),
        ], 201);
    }


    public function cancel(Request $request, int $id, RideRequestService $service,RideBroadcastService $broadcastService)
    {
        $rideRequest = $service->cancel($id, Auth::id());

        $broadcastService->sendCancelToNearbyDrivers($rideRequest);

        return response()->json([
            'status' => true,
            'data' => $rideRequest,
        ]);
    }
    public function history(Request $request, DistanceService $service)
{
    try {
        $rides = $service->listUserRideRequests(Auth::id());

        return response()->json([
            'status' => true,
            'data'   => $rides,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'message' => $e->getMessage(),
        ], 400);
    }
}


public function historyById(Request $request, DistanceService $service)
{
    $request->validate([
        'ride_request_id' => ['required', 'integer', 'exists:ride_requests,id']
    ]);

    try {
        $ride = $service->listRideRequestById(
            $request->ride_request_id,
            Auth::id()
        );

        return response()->json([
            'status' => true,
            'data'   => $ride,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'message' => $e->getMessage(),
        ], 400);
    }
}

public function completed(Request $request, DistanceService $service)
{
    $request->validate([
        'ride_request_id' => ['required', 'integer', 'exists:ride_requests,id']
    ]);

    try {
        $ride = $service->showCompletedRideForUser(
            $request->ride_request_id,
            Auth::id()
        );

        return response()->json([
            'status' => true,
            'message' => 'Your ride has been completed successfully',
            'data'   => $ride,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'message' => $e->getMessage(),
        ], 400);
    }
}


}
