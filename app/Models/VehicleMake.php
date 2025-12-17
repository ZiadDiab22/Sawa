<?php

namespace App\Models;

use App\Models\DriverProfile;
use Illuminate\Database\Eloquent\Model;

class VehicleMake extends Model
{
    protected $fillable = ['name'];

    public function driverProfiles()
    {
        return $this->hasMany(DriverProfile::class);
    }
}
