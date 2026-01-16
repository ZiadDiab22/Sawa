<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PassengerRating extends Model
{
    protected $fillable = [
        'ride_id',
        'driver_id',
        'user_id',
        'passenger_id',
        'rating',
        'comment',
    ];

    public function ride(): BelongsTo
    {
        return $this->belongsTo(Ride::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    protected $appends = ['driver_name'];

    public function getDriverNameAttribute()
    {
        return $this->driver?->name;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
