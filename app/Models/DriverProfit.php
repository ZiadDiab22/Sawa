<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverProfit extends Model
{
    protected $fillable = [
        'user_id',
        'ride_id',
        'amount',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }
}
