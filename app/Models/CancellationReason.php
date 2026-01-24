<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CancellationReason extends Model
{
     protected $fillable = [
        'reason',
        'user_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    public function rides()
    {
        return $this->hasMany(Ride::class);
    }
}
