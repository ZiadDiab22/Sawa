<?php

namespace App\Services\Ride;

use App\Events\NewRideRequestCreated;
use App\Events\RideRequestCancelled;
use App\Models\User;
use App\Repositories\Driver\DriverLocationRepository;
use App\Services\NotificationService;

class RideBroadcastService
{
  public function __construct(
    private DriverLocationRepository $drivers
  ) {}

  public function sendToNearbyDrivers($rideRequest)
  {
    $drivers = $this->drivers->nearbyActiveDrivers(
      $rideRequest->pickup_lat,
      $rideRequest->pickup_lng
    );

    \Log::info('Nearby drivers count', [
        'count' => count($drivers)
    ]);

    foreach ($drivers as $driverLocation) {

        // 🔥 جلب User الحقيقي
        $driver = User::find($driverLocation->id);

        if (!$driver) {
            continue;
        }

        // 🔔 Firebase Notification
        NotificationService::sendToUser(
            $driver,
            'new_ride_request',
            'طلب رحلة جديد',
            'لديك طلب رحلة جديد بالقرب منك.',
            [
                'ride_request_id' => (string) $rideRequest->id,
                'price' => (string) $rideRequest->price,
                'distance_km' => (string) $rideRequest->distance_km,
            ]
        );

      broadcast(
        new NewRideRequestCreated($rideRequest, $driver->driver_id)
      );
    }
  }

    public function sendCancelToNearbyDrivers($rideRequest): void
    {
        $drivers = $this->drivers->nearbyActiveDrivers(
            $rideRequest->pickup_lat,
            $rideRequest->pickup_lng
        );

        foreach ($drivers as $driver) {
            broadcast(
                new RideRequestCancelled($rideRequest, $driver->driver_id)
            );
        }
    }
}
