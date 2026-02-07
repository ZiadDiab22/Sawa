<?php

namespace App\Events;

use App\Models\RideRequest;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\Log;

class RideRequestCancelled implements ShouldBroadcastNow
{
    public function __construct(
        public RideRequest $rideRequest,
        public int $driverId
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        Log::info('Broadcasting  cancelled ride request', [
            'driver_id' => $this->driverId,
            'ride_request_id' => $this->rideRequest->id,
        ]);

        return new PrivateChannel("drivers.{$this->driverId}");
    }

    public function broadcastAs(): string
    {
        return 'ride.request.cancelled';
    }

    public function broadcastWith(): array
    {
        return [
            'ride_request_id' =>  $this->rideRequest->id
        ];
    }
}
