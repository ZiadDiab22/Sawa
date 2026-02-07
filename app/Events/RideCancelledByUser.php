<?php

namespace App\Events;

use App\Models\Ride;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RideCancelledByUser implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Ride $ride) {}

    public function broadcastOn(): PrivateChannel
    {
        \Log::info('RideCancelledByUser fired', [
            'ride_id' => $this->ride->id,
            'driver_id' => $this->ride->driver_id,
        ]);

        return new PrivateChannel("drivers.{$this->ride->driver_id}");
    }

    public function broadcastAs(): string
    {
        return 'ride.cancelled_by_user';
    }

    public function broadcastWith(): array
    {
        return [
            'ride_id' => $this->ride->id,
            'message' => 'Passenger cancelled the ride',
        ];
    }
}
