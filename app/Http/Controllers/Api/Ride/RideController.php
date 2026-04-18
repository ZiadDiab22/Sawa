<?php

namespace App\Http\Controllers\Api\Ride;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ride\CompleteRideRequest;
use App\Services\NotificationService;
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
        $ride->load('user');

        $Notification = NotificationService::send(
            $ride->user->id,
            'ride_started',
            'بدأت الرحلة',
            'السائق في طريقه إليك، الرحلة بدأت الآن.',
            ['ride_id' => (string) $ride->id]
        );
        return response()->json([
            'status' => true,
            'data' => $ride->makeHidden('code'),
             'notification' => [
                'type' => 'ride_started',
                'title' => 'بدأت الرحلة',
                'body' => 'السائق في طريقه إليك، الرحلة بدأت الآن.',
                'firebase_result' => $Notification
            ]
        ]);
    }

    public function complete(CompleteRideRequest $request, RideService $service)
    {
        $ride = $service->complete(
            $request->validated(),
            Auth::id()
        );
        $ride->load('user');
        $Notification = NotificationService::send(
            $ride->user->id,
            'ride_completed',
            'انتهت الرحلة',
            'تم الوصول إلى الوجهة بنجاح، شكراً لاستخدامك التطبيق.',
            ['ride_id' => (string) $ride->id]
        );
        return response()->json([
            'status' => true,
            'data' => $ride,
            'notification' => [
                'type' => 'ride_completed',
                'title' => 'انتهت الرحلة',
                'body' => 'تم الوصول إلى الوجهة بنجاح، شكراً لاستخدامك التطبيق.',
                'firebase_result' => $Notification
            ]

        ]);
    }

    public function userCancel(int $id, RideService $service)
    {
        $ride = $service->userCancel($id, Auth::id());

    $ride->load('driver');

    $Notification = NotificationService::send(
    $ride->user->id,
 // السائق
            'ride_cancelled_by_user',
            'تم إلغاء الرحلة',
            'قام الراكب بإلغاء الرحلة.',
            [
                'ride_id' => (string) $ride->id
            ]
    );

    return response()->json([
        'status' => true,
        'data' => $ride,
        'notification' => [
                'type' => 'ride_cancelled_by_user',
                'ride_id' => $ride->id,
                'title' => 'تم إلغاء الرحلة',
                'body' => 'قام الراكب بإلغاء الرحلة.',
                'firebase_result' => $Notification
            ]
    ]);
    }

    public function driverCancel(int $id, RideService $service)
    {
        $ride = $service->driverCancel($id, Auth::id());
        $ride->load('user');

        $Notification = NotificationService::send(
        $ride->user->id,
        'ride_cancelled_by_driver',
        'تم إلغاء الرحلة',
        'قام السائق بإلغاء الرحلة.',
        ['ride_id' => (string) $ride->id]
    );

    return response()->json([
        'status' => true,
        'data' => $ride->makeHidden('code'),
        'notification' => [
            'type' => 'ride_cancelled_by_driver',
            'title' => 'تم إلغاء الرحلة',
            'body' => 'قام السائق بإلغاء الرحلة.',
            'firebase_result' => $Notification
        ]
    ]);
    }
}
