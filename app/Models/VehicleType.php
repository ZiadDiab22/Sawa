<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'base_fare',
        'per_km',
        'per_minute',
        'minimum_fare'
    ];

    public function drivers()
    {
        return $this->hasMany(DriverProfile::class);
    }
}
