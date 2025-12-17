<?php

namespace App\Models;

use App\Models\VehicleMake;
use Illuminate\Database\Eloquent\Model;

class DriverProfile extends Model
{

    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'user_id',
        'vehicle_type_id',
        'vehicle_make_id',
        'vehicle_model',
        'vehicle_year',
        'vehicle_color',
        'vehicle_plate_number',
        'vehicle_document',
        'license_document',
        'insurance_document',
        'vehicle_images',
        'status',
        'is_status',
    ];

    protected $casts = [
        'vehicle_images' => 'array',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function city()
    // {
    //     return $this->belongsTo(City::class);
    // }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    // public function documents()
    // {
    //     return $this->hasMany(DriverDocument::class, 'driver_id');
    // }

    public function commissions()
    {
        return $this->hasMany(DriverCommission::class, 'driver_id');
    }

    public function locations()
    {
        return $this->hasMany(DriverLocation::class, 'driver_id');
    }

    public function vehicleMake()
{
    return $this->belongsTo(VehicleMake::class);
}

}
