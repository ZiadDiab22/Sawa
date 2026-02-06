<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'ride_id',
        'type',
        'reason',
        'amount',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ride():BelongsTo
    {
        return $this->belongsTo(Ride::class);
    }
}
