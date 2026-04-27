<?php

namespace App\Models;

use App\Models\DriverDocument;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'blocked',
        'gender',
        'profile_image',
        'fcm_token'
    ];

    public function roles():BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function driverProfile()
    {
        return $this->hasOne(DriverProfile::class);
    }

    public function location()
    {
        return $this->hasOne(DriverLocation::class, 'driver_id');
    }

//     public function driverDocuments()
// {
//     return $this->hasMany(DriverDocument::class);
// }

public function documents()
{
    return $this->hasMany(DriverDocument::class, 'user_id');
}
}
