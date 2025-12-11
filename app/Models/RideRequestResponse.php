<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideRequestResponse extends Model
{
    protected $fillable = [
        'ride_request_id',
        'driver_id',
        'status',
    ];

    public function rideRequest()
    {
        return $this->belongsTo(RideRequest::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
