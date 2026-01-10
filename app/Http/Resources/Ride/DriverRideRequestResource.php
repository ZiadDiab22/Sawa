<?php

namespace App\Http\Resources\Ride;

use Illuminate\Http\Resources\Json\JsonResource;

class DriverRideRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'ride_request_id'   => $this->ride_request_id,
            'driver_id'         => $this->driver_id,
            'user_id'           => $this->rideRequest->user_id,
            'user_name'         => $this->rideRequest->user->name,
            'status'            => $this->status,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,

            'pickup_lat'        => $this->rideRequest->pickup_lat,
            'pickup_lng'        => $this->rideRequest->pickup_lng,
            'drop_lat'          => $this->rideRequest->drop_lat,
            'drop_lng'          => $this->rideRequest->drop_lng,
            'distance_km'       => $this->rideRequest->distance_km,
            'price'             => $this->rideRequest->price,
            'duration_minutes' => $this->rideRequest->duration_minutes,

            'vehicle_type_id'   => $this->rideRequest->vehicle_type_id,
            'vehicle_type_name' => optional($this->rideRequest->vehicleType)->name,
        ];
    }
}
