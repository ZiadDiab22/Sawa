<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverCommission extends Model
{
    protected $fillable = ['driver_id', 'percentage', 'fixed_fee'];

    public function driver()
    {
        return $this->belongsTo(DriverProfile::class, 'driver_id');
    }
}
