<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverLocation extends Model
{
    protected $fillable = [
        'driver_id',
        'lat',
        'lng'
    ];

    public function driver():BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
