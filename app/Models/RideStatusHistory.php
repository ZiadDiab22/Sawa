<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideStatusHistory extends Model
{
    protected $fillable = [
        'ride_id',
        'old_status',
        'new_status',
        'changed_by_type',
        'changed_by_id',
    ];

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_id');
    }
}
