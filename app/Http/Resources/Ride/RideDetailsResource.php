<?php

namespace App\Http\Resources\Ride;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class RideDetailsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,

            'date' => Carbon::parse($this->created_at)->format('Y-m-d'),
            'time' => Carbon::parse($this->created_at)->format('H:i'),

            'start_lat' => $this->start_lat,
            'start_lng' => $this->start_lng,
            'end_lat' => $this->end_lat,
            'end_lng' => $this->end_lng,

            'status' => $this->status,
            'distance_km' => $this->distance_km,
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
            'passengers' => $this->passengers,

            'driver' => [
                'id' => $this->driver->id,
                'name' => $this->driver->name,

                'location' => [
                    'lat' => $this->driver->location?->lat,
                    'lng' => $this->driver->location?->lng,
                ],

                'vehicle' => [
                    'model' => $this->driverProfile->vehicle_model ?? null,
                    'year' => $this->driverProfile->vehicle_year ?? null,
                    'color' => $this->driverProfile->vehicle_color ?? null,
                    'plate' => $this->driverProfile->vehicle_plate_number ?? null,
                ],
            ],

            'passenger' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'phone' => $this->user->phone,
                'email' => $this->user->email,
                'profile_image' => $this->user->profile_image,
                'gender' => $this->user->gender,
            ],

            'driver_rating' => [
                'rating'  => $this->driverRating?->rating,
                'comment' => $this->driverRating?->comment,
            ],
            'passenger_rating' => [
                'rating'  => $this->passengerRating?->rating,
                'comment' => $this->passengerRating?->comment,
            ],

            'financials' => [
                'driver_profit' => $this->driverProfit?->amount,
                'company_commission' => $this->companyCommission?->amount,
            ],

            'status_history' => $this->statusHistory->map(function ($history) {
                return [
                    'id' => $history->id,
                    'old_status' => $history->old_status,
                    'new_status' => $history->new_status,
                    'changed_by_type' => $history->changed_by_type,
                    'changed_by_id' => $history->changed_by_id,
                    'date' => Carbon::parse($history->created_at)->format('Y-m-d'),
                    'time' => Carbon::parse($history->created_at)->format('H:i'),
                ];
            }),
        ];
    }
}
