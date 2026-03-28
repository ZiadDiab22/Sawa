<?php

namespace App\Models;

use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DriverDocument extends Model
{
    protected $fillable = [
    'user_id',
    'driver_id',
    'type',
    'file_path',
    'expires_at',
    'status'
    ];

    protected $casts = [
        // 'file_path' => 'array',
        'expires_at' => 'date'
    ];

    public function driver()
    {
        return $this->belongsTo(DriverProfile::class, 'driver_id');
    }

    public function getFilePathAttribute($value)
{
    if (!$value) {
        return null;
    }

    $files = json_decode($value, true);

    if (is_array($files)) {
        return array_map(fn($file) => asset('storage/'.$file), $files);
    }

    return asset('storage/'.$value);
}

public function user()
{
    return $this->belongsTo(User::class);
}
}




