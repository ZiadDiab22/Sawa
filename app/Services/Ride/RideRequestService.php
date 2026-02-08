<?php

namespace App\Services\Ride;

use App\Repositories\Ride\RideRequestRepository;
use Illuminate\Support\Facades\DB;

class RideRequestService
{
  public function __construct(
    private RideRequestRepository $rideRequests,
  ) {}

  public function cancel(int $id, int $userId)
  {
    return DB::transaction(function () use ($id, $userId) {

      $rideRequest = $this->rideRequests->findForUser($id, $userId);

      if (!$rideRequest) {
        throw new \Exception('This request isnt for you or not exist');
      }

      if ($rideRequest->status === 'cancelled') {
        throw new \Exception('This Request already cancelled');
      }

      if ($rideRequest->status === 'accepted') {
        throw new \Exception('This Request cant be cancelled');
      }

      $this->rideRequests->updateStatus($rideRequest, 'cancelled');

      return $rideRequest->refresh();
    });
  }
}
