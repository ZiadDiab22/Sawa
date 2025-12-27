<?php

namespace App\Repositories\Ride;

use App\Models\RideRequest;

class RideRequestRepository
{
  public function create(array $data): RideRequest
  {
    return RideRequest::create($data);
  }
}
