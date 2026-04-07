<?php

namespace App\Events;

use App\Models\RideRequest;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\Log;

class NewRideRequestCreated implements ShouldBroadcastNow
{
    public function __construct(
        public RideRequest $rideRequest,
        public int $driverId
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        Log::info('Broadcasting ride request', [
            'driverId' => $this->Id,
            'ride_request_id' => $this->rideRequest->id,
        ]);

        return new PrivateChannel("drivers.{$this->Id}");
    }

    public function broadcastAs(): string
    {
        return 'ride.request.created';
    }

    public function broadcastWith(): array
    {
        return [
            'ride_request_id' =>  $this->rideRequest->id
        ];
    }
}
