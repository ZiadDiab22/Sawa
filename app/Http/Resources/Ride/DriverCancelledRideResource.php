<?php

namespace App\Http\Resources\Ride;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverCancelledRideResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'ride_id'           => $this->id,
            'ride_request_id'   => $this->ride_request_id,
            'driver_id'         => $this->driver_id,
            'user_id'           => $this->user_id,
            'user_name'         => $this->user->name,

            'start_lat'         => $this->start_lat,
            'start_lng'         => $this->start_lng,
            'end_lat'           => $this->end_lat,
            'end_lng'           => $this->end_lng,
            'distance_km'       => $this->distance_km,
            'price'             => $this->price,
            'duration_minutes' => $this->duration_minutes,
            'status'            => $this->status,

            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,

            'cancelled_by'      => optional($this->statusHistory->first())->changed_by_type,
        ];
    }
}
