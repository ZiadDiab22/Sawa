<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverDocument extends Model
{
    protected $fillable = [
        'driver_id',
        'type',
        'file_path',
        'expires_at',
        'status'
    ];

    protected $casts = [
        'expires_at' => 'date',
    ];

    public function driver()
    {
        return $this->belongsTo(DriverProfile::class, 'driver_id');
    }
}
