<?php

namespace App\Events;

use App\Models\Ride;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        protected float $lat,
        protected float $lng,
        protected Ride $ride
    ) {}

    public function broadcastOn(): Channel
    {
        \Log::info('DriverLocationUpdated fired', [
            'lat' => $this->lat,
            'lng' => $this->lng,
            'ride_id' => $this->ride->id,
            'user_id' => $this->ride->user_id,
            'driver_id' => $this->ride->driver_id,
        ]);

        return new PrivateChannel('user.' . $this->ride->user_id);
    }

    public function broadcastAs(): string
    {
        return 'driver.location.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng,
            'ride_id' => $this->ride->id,
            'user_id' => $this->ride->user_id,
            'driver_id' => $this->ride->driver_id,
        ];
    }
}
