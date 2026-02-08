<?php

namespace App\Events;

use App\Models\Ride;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RideCancelledByDriver implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Ride $ride) {}

    public function broadcastOn(): PrivateChannel
    {
        \Log::info('RideCancelledByDriver fired', [
            'ride_id' => $this->ride->id,
            'user_id' => $this->ride->user_id,
        ]);

        return new PrivateChannel('user.' . $this->ride->user_id);
    }

    public function broadcastAs(): string
    {
        return 'ride.cancelled_by_driver';
    }

    public function broadcastWith(): array
    {
        return [
            'ride_id' => $this->ride->id,
            'message' => 'Driver cancelled the ride',
        ];
    }
}
