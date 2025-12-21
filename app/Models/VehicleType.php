<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'image',
        'base_fare',
        'per_km',
        'per_minute',
        'minimum_fare',
        'cost_increase_percentage',
        'is_active'
    ];

    public function drivers()
    {
        return $this->hasMany(DriverProfile::class);
    }
}
