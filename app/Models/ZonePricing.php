<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZonePricing extends Model
{
    protected $table = 'zone_pricings';

    protected $fillable = [
        'zone_id',
        'base_fare',
        'per_km',
        'per_minute',
        'minimum_fare'
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
