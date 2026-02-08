<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    protected $fillable = [
        'ride_request_id',
        'driver_id',
        'user_id',
        'start_lat',
        'start_lng',
        'end_lat',
        'end_lng',
        'distance_km',
        'duration_minutes',
        'price',
        'status',
        'code',
        'promo_code_id',
        'cancellation_reason_id',
        'passengers'
    ];

    protected $casts = [
        'start_lat' => 'float',
        'start_lng' => 'float',
        'end_lat' => 'float',
        'end_lng' => 'float',
        'distance_km' => 'float',
        'duration_minutes' => 'integer',
    ];

    public function request()
    {
        return $this->belongsTo(RideRequest::class, 'ride_request_id');
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function cancellationReason()
    {
        return $this->belongsTo(CancellationReason::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(RideStatusHistory::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function driverProfit()
    {
        return $this->hasOne(DriverProfit::class, 'ride_id');
    }

    public function companyCommission()
    {
        return $this->hasOne(CompanyCommission::class, 'ride_id');
    }

    public function driverProfile()
    {
        return $this->hasOne(DriverProfile::class, 'user_id', 'driver_id');
    }

    public function driverRating()
    {
        return $this->hasOne(DriverRating::class);
    }

    public function passengerRating()
    {
        return $this->hasOne(PassengerRating::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function driverRatings()
    {
        return $this->hasMany(DriverRating::class, 'driver_id', 'driver_id');
    }

    public function passengerRatings()
    {
        return $this->hasMany(PassengerRating::class, 'user_id', 'user_id');
    }
}
