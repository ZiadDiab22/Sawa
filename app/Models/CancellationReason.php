<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CancellationReason extends Model
{
    protected $fillable = [
        'type',
        'reason_text',
    ];

    public function rides()
    {
        return $this->hasMany(Ride::class);
    }
}
