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
    $Notification = NotificationService::send(
        $user->id,
        'ride_created',
        'طلبك تم تسجيله',
        'تم استلام طلبك بنجاح وسيتم البحث عن سائق.',
        ['ride_request_id' => (string) $rideRequest->id]
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
    $userId = $response['ride']->user_id;

    $Notification = NotificationService::send(
        $userId,
        'ride_accepted',
        'تم قبول طلبك',
        'تم قبول طلبك من قبل أحد السائقين.',
        ['ride_id' => (string) $response['ride']->id]
    );
        $response['ride']->makeHidden('code');
        return response()->json([
        'status' => true,
        'data' => $response,
        'notification' => [
            'type' => 'ride_Accept',
            'title' => '  تم قبول طلبك ',
            'body'  => 'تم قبول طلبك بنجاح وسيتم التواصل معك من قبل السائق   .',
            'firebase_result' => $Notification
        ]
], 201);
    }


    public function cancel(Request $request, int $id, RideRequestService $service,RideBroadcastService $broadcastService)
    {
        $rideRequest = $service->cancel($id, Auth::id());

        $broadcastService->sendCancelToNearbyDrivers($rideRequest);
    NotificationService::send(
        $rideRequest->user_id,
        'ride_request_cancelled',
        'تم إلغاء الطلب',
        'تم إلغاء طلب الرحلة الخاص بك.',
        ['ride_request_id' => (string) $rideRequest->id]
    );
        return response()->json([
            'status' => true,
            'data' => $rideRequest,
            'notification' => [
            'type' => 'ride_request_cancelled',
            'title' => 'تم إلغاء الطلب',
            'body'  => 'تم إلغاء طلب الرحلة الخاص بك.',
            'firebase_result' => $Notification
        ]
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
