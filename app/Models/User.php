<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
        'blocked',
        'gender'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function driverProfile()
    {
        return $this->hasOne(DriverProfile::class);
    }
}
