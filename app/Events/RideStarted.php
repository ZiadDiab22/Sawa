<?php

namespace App\Events;

use App\Models\Ride;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RideStarted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Ride $ride) {}

    public function broadcastOn(): PrivateChannel
    {
        \Log::info('RideStarted fired', [
            'ride_id' => $this->ride->id,
            'user_id' => $this->ride->user_id,
        ]);

        return new PrivateChannel('user.' . $this->ride->user_id);
    }

    public function broadcastAs(): string
    {
        return 'ride.started';
    }

    public function broadcastWith(): array
    {
        return [
            'ride_id' => $this->ride->id,
        ];
    }
}
