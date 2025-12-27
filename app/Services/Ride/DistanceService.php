<?php

namespace App\Services\Ride;

use App\Models\RideRequestResponse;
use Illuminate\Support\Facades\DB;

class DistanceService
{

  public function calculateKm(
    float $lat1,
    float $lng1,
    float $lat2,
    float $lng2
  ): float {
    $earthRadius = 6371;

    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);

    $a = sin($dLat / 2) ** 2 +
      cos(deg2rad($lat1)) *
      cos(deg2rad($lat2)) *
      sin($dLng / 2) ** 2;

    return round($earthRadius * 2 * asin(sqrt($a)), 2);
  }

  public function estimate(array $data): array
  {
    $centerLat = $this->get('city_center_latitude');
    $centerLng = $this->get('city_center_longitude');
    $radiusKm  = $this->get('service_radius_km');

    $pickupDistance = $this->calculateKm(
      $centerLat,
      $centerLng,
      $data['pickup_lat'],
      $data['pickup_lng']
    );

    if ($pickupDistance > $radiusKm) {
      throw new \DomainException('Pickup location is outside service zone');
    }

    $rideDistance = $this->calculateKm(
      $data['pickup_lat'],
      $data['pickup_lng'],
      $data['drop_lat'],
      $data['drop_lng']
    );

    $price = $this->calculate(
      $rideDistance,
      $data['passengers']
    );

    $duration = $this->estimateDuration($rideDistance);

    return compact('rideDistance', 'price', 'duration');
  }

  private function estimateDuration(float $distanceKm): int
  {
    return (int) ceil(($distanceKm / 20) * 60);
  }

  public function calculate(float $distanceKm, int $passengers): int
  {
    $basePrice = match (true) {
      $distanceKm <= 2 => 100_000,
      $distanceKm <= 4 => 150_000,
      $distanceKm <= 5 => 200_000,
      $distanceKm <= 7 => 250_000,
      default => 250_000 + (($distanceKm - 7) * 70_000),
    };

    if ($distanceKm <= 7 && $passengers > 1) {
      $extraPassengers = $passengers - 1;
      $basePrice += $basePrice * (0.5 * $extraPassengers);
    }

    return (int) round($basePrice);
  }

  public function get(string $key): ?float
  {
    return DB::table('settings')
      ->where('key', $key)
      ->value('value');
  }

  public function skip(int $rideRequestId, int $driverId)
  {
    return RideRequestResponse::updateOrCreate(
      [
        'ride_request_id' => $rideRequestId,
        'driver_id'       => $driverId,
      ],
      [
        'status' => 'skipped',
      ]
    );
  }
}
