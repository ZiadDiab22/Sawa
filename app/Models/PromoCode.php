<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code',
        'discount_type',
        'amount',
        'max_uses',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'amount'     => 'float',
    ];
}
