<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\Log;

class DriverActivated implements ShouldBroadcastNow
{
    public int $driverId;

    public function __construct(int $driverId)
    {
        $this->driverId = $driverId;
    }

    public function broadcastOn()
    {
        Log::info('Broadcasting activate driver', [
            'driver_id' => $this->driverId
        ]);

        return new PrivateChannel("drivers.{$this->driverId}");
    }

    public function broadcastAs()
    {
        return 'driver.activated';
    }
}
