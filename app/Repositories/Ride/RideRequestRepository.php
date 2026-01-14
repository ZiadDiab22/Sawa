<?php
namespace App\Repositories\Ride;
use App\Models\Ride;
use App\Models\RideRequest;
class RideRequestRepository
{
  public function create(array $data): RideRequest
  {
    return RideRequest::create($data);
  }

  public function createFromRequest(
    RideRequest $request,
    int $driverId,
    string $code
  ): Ride {
    $ride = Ride::create([
      'ride_request_id' => $request->id,
      'driver_id'       => $driverId,
      'user_id'         => $request->user_id,

      'start_lat' => $request->pickup_lat,
      'start_lng' => $request->pickup_lng,
      'end_lat'   => $request->drop_lat,
      'end_lng'   => $request->drop_lng,

      'distance_km'      => $request->distance_km,
      'price'            => $request->price,
      'duration_minutes' => $request->duration_minutes,

      'status' => 'driver_on_way',
      'code'   => $code,
    ]);

    $ride->load('user:id,name,phone');

    $ride->setAttribute('user_name', $ride->user->name);
    $ride->setAttribute('user_phone', $ride->user->phone);

    unset($ride->user);

    return $ride;
  }


  public function findForUser(int $id, int $userId): ?RideRequest
  {
    return RideRequest::where('id', $id)
      ->where('user_id', $userId)
      ->first();
  }

  public function updateStatus(RideRequest $rideRequest, string $status): void
  {
    $rideRequest->update(['status' => $status]);
  }

  public function getByUserId(int $userId): array
{
    return RideRequest::where('user_id', $userId)
        ->orderBy('id', 'DESC')
        ->get()
        ->toArray();
}

public function getById(int $rideRequestId): ?RideRequest
{
    return RideRequest::find($rideRequestId);
}

public function getCompletedRide(int $rideRequestId, int $userId): ?array
{
    $ride = Ride::with('rideRequest')
        ->where('ride_request_id', $rideRequestId)
        ->where('user_id', $userId)
        ->where('status', 'completed')
        ->first();

    if (!$ride) {
        return null;
    }

    return [
        'id'        => $ride->ride_request_id,
        'start_lat' => $ride->start_lat,
        'start_lng' => $ride->start_lng,
        'end_lat'   => $ride->end_lat,
        'end_lng'   => $ride->end_lng,
        'price'     => $ride->price,
    ];
}


}
