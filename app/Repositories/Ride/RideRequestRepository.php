<?php

namespace App\Repositories\Ride;

use App\Models\Ride;
use App\Models\RideRequest;

class RideRequestRepository
{
  public function create(array $data): RideRequest
  {
    return RideRequest::create($data);
  }

  public function createFromRequest(
    RideRequest $request,
    int $driverId,
    string $code
  ): Ride {
    $ride = Ride::create([
      'ride_request_id' => $request->id,
      'driver_id'       => $driverId,
      'user_id'         => $request->user_id,

      'start_lat' => $request->pickup_lat,
      'start_lng' => $request->pickup_lng,
      'end_lat'   => $request->drop_lat,
      'end_lng'   => $request->drop_lng,

      'distance_km'      => $request->distance_km,
      'price'            => $request->price,
      'duration_minutes' => $request->duration_minutes,

      'status' => 'driver_on_way',
      'code'   => $code,
    ]);

    $ride->load('user:id,name,phone');

    $ride->setAttribute('user_name', $ride->user->name);
    $ride->setAttribute('user_phone', $ride->user->phone);

    unset($ride->user);

    return $ride;
  }
}
