<?php

namespace App\Http\Resources\Ride;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class RideInfoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'ride_request_id' => $this->ride_request_id,
            'driver_id' => $this->driver_id,
            'driver_name' => $this->driver_name,
            'user_id' => $this->user_id,
            'user_name' => $this->passenger_name,

            'start_lat' => $this->start_lat,
            'start_lng' => $this->start_lng,
            'end_lat' => $this->end_lat,
            'end_lng' => $this->end_lng,

            'distance_km' => $this->distance_km,
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
            'status' => $this->status,
            'code' => $this->code,
            'cancellation_reason_id' => $this->cancellation_reason_id,

            'date' => Carbon::parse($this->created_at)->format('Y-m-d'),
            'time' => Carbon::parse($this->created_at)->format('H:i'),

            'vehicle_model' => $this->vehicle_model,
            'vehicle_year' => $this->vehicle_year,
            'vehicle_color' => $this->vehicle_color,
            'vehicle_plate_number' => $this->vehicle_plate_number,
        ];
    }

}
