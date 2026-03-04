<?php

namespace App\Http\Controllers\Api\Ride;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ride\CalculateRideRequest;
use App\Repositories\Ride\RideRequestRepository;
use App\Services\NotificationService;
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


        $user = $rideRequest->user;

    $Notification= NotificationService::sendToUser(
    $user,
    'ride_created', // نوع الإشعار
    'طلبك تم تسجيله', // عنوان الإشعار
    'تم استلام طلبك بنجاح وسيتم التواصل معك حال وجود سائق متاح.', // نص الإشعار
    ['ride_request_id' => (string) $rideRequest->id] // بيانات إضافية
);

        $broadcastService->sendToNearbyDrivers($rideRequest);

        return response()->json([
        'status' => true,
        'data' => $rideRequest,
        'notification' => [
            'type' => 'ride_created',
            'title' => 'طلبك تم تسجيله',
            'body'  => 'تم استلام طلبك بنجاح وسيتم التواصل معك حال وجود سائق متاح.',
            'firebase_result' => $Notification
        ]
    ], 201);
    }
    //    //



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

        $response['ride']->makeHidden('code');
        return response()->json([
        'status' => true,
        'data' => $response
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
