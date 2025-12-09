<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name',
        'country_name',
        'timezone',
        'currency'
    ];

    public function zones()
    {
        return $this->hasMany(Zone::class);
    }

    public function driverProfiles()
    {
        return $this->hasMany(DriverProfile::class);
    }
}
