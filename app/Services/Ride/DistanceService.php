<?php

namespace App\Services\Ride;

use App\Events\RideAccepted;
use App\Models\DriverProfile;
use App\Models\RideRequest;
use App\Models\RideRequestResponse;
use App\Repositories\Ride\RideRequestRepository;
use App\Repositories\Ride\RideStatusHistoryRepository;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class DistanceService
{

    protected RideRequestRepository $repo;
    protected RideStatusHistoryRepository $histories;

    public function __construct(RideRequestRepository $repo, RideStatusHistoryRepository $histories)
    {
        $this->repo = $repo;
        $this->histories = $histories;
    }

    public function calculateKm(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float
    {
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
        $radiusKm = $this->get('service_radius_km');

        $pickupDistance = $this->calculateKm(
            $centerLat,
            $centerLng,
            $data['pickup_lat'],
            $data['pickup_lng']
        );

//    if ($pickupDistance > $radiusKm) {
//      throw new \DomainException('Pickup location is outside service zone');
//    }

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
        return (int)ceil(($distanceKm / 20) * 60);
    }

    public function calculate(float $distanceKm, int $passengers): int
    {
        $basePrice = match (true) {
            $distanceKm <= 2 => 150_000,
            $distanceKm <= 4 => 200_000,
            $distanceKm <= 5 => 250_000,
            $distanceKm <= 7 => 300_000,
            default => 300_000 + (($distanceKm - 7) * 100_000),
        };

        if ($distanceKm <= 7 && $passengers > 1) {
            $extraPassengers = $passengers - 1;
            $basePrice += $basePrice * (0.5 * $extraPassengers);
        }

        return (int)ceil($basePrice / 5000) * 5000;
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
                'driver_id' => $driverId,
            ],
            [
                'status' => 'skipped',
            ]
        );
    }

    public function accept(int $rideRequestId, int $driverId)
    {
        return DB::transaction(function () use ($rideRequestId, $driverId) {

            $request = RideRequest::lockForUpdate()->findOrFail($rideRequestId);

            if ($request->status === 'accepted') {
                throw new \Exception('Ride request already accepted');
            }

            $request->update(['status' => 'accepted']);


            $ride = $this->repo->createFromRequest(
                $request,
                $driverId,
                $this->generateCode()
            );

            $this->histories->create(
                $ride->id,
                null,
                'driver_on_way',
                'driver',
                $driverId
            );

            RideRequestResponse::updateOrCreate(
                [
                    'ride_request_id' => $rideRequestId,
                    'driver_id' => $driverId,
                ],
                [
                    'status' => 'accepted',
                ]
            );

            DriverProfile::query()->where('user_id', $driverId)->update(['has_ride' => true]);
            broadcast(new RideAccepted($ride))->toOthers();

            $user = $ride->user;

$notificationResult = NotificationService::sendToUser(
    $user,
    'ride_accepted',
    'السائق قبل رحلتك',
    'تم قبول رحلتك من قبل السائق، سيتم التواصل معك قريباً.',
    ['ride_request_id' => (string) $ride->ride_request_id]
);

$response = [
    'ride' => $ride,
    'notification' => [
        'type' => 'ride_accepted',
        'title' => 'السائق قبل رحلتك',
        'body'  => 'تم قبول رحلتك من قبل السائق، سيتم التواصل معك قريباً.',
        'firebase_result' => $notificationResult
    ]
];

return $response;
        });
    }

    private function generateCode(): string
    {
        return (string)random_int(1000, 9999);
    }

    //
    public function listUserRideRequests(int $userId): array
    {
        try {
            $rides = $this->repo->getByUserId($userId);

            if (empty($rides)) {
                throw new \DomainException('No ride requests found for this user');
            }

            return $rides;

        } catch (\DomainException $e) {
            throw new \Exception('Ride History Error: ' . $e->getMessage());
        } catch (\Throwable $e) {
            throw new \Exception('Unexpected Error while fetching ride history');
        }
    }

    public function listRideRequestById(int $rideRequestId, int $userId): RideRequest
    {
        try {
            $ride = $this->repo->getById($rideRequestId);

            if (!$ride) {
                throw new \DomainException('Ride request not found');
            }

            if ($ride->user_id !== $userId) {
                throw new \DomainException('Unauthorized - this ride request does not belong to you');
            }

            return $ride;

        } catch (\DomainException $e) {
            throw new \Exception('Ride Request Error: ' . $e->getMessage());
        } catch (\Throwable $e) {
            throw new \Exception('Unexpected Error while fetching ride request');
        }
    }

    public function showCompletedRideForUser(int $rideRequestId, int $userId): array
    {
        try {
            $ride = $this->repo->getCompletedRide($rideRequestId, $userId);

            if (!$ride) {
                throw new \DomainException('Ride not completed or not found for this user');
            }

            return $ride;

        } catch (\DomainException $e) {
            throw new \Exception('Completed Ride Error: ' . $e->getMessage());
        } catch (\Throwable $e) {
            throw new \Exception('Unexpected Error while fetching completed ride');
        }
    }
}
