<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideRequest extends Model
{
    protected $fillable = [
        'user_id',
        'pickup_lat',
        'pickup_lng',
        'drop_lat',
        'drop_lng',
        'pickup_zone_id',
        'drop_zone_id',
        'vehicle_type_id',
        'status',
    ];

    protected $casts = [
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
        'drop_lat' => 'float',
        'drop_lng' => 'float',
    ];

    public function passenger()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pickupZone()
    {
        return $this->belongsTo(Zone::class, 'pickup_zone_id');
    }

    public function dropZone()
    {
        return $this->belongsTo(Zone::class, 'drop_zone_id');
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function responses()
    {
        return $this->hasMany(RideRequestResponse::class);
    }

    public function ride()
    {
        return $this->hasOne(Ride::class);
    }
}
