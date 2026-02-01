<?php

namespace App\Repositories\Driver;

use App\Models\DriverLocation;
use Illuminate\Support\Facades\DB;
use \Illuminate\Database\Eloquent\Collection;

class DriverLocationRepository
{
    public function all(): Collection
    {
        return DriverLocation::get();
    }

    public function show(int $id): Collection
    {
        return DriverLocation::query()->where('driver_id', $id)->get();
    }

    public function createOrUpdate(int $driverId, float $lat, float $lng)
    {
        return DriverLocation::query()->updateOrCreate(
            ['driver_id' => $driverId],
            ['lat' => $lat, 'lng' => $lng]
        );
    }

    public function nearbyActiveDrivers(
        float $lat,
        float $lng,
        float $radiusKm = 4
    )
    {
        $drivers = DB::table('driver_locations as dl')
            ->join('driver_profiles as p', 'p.user_id', '=', 'dl.driver_id')
            ->select('dl.driver_id')
            ->selectRaw('
            6371 * 2 * ASIN(
                SQRT(
                    POWER(SIN(RADIANS(dl.lat - ?) / 2), 2) +
                    COS(RADIANS(?)) * COS(RADIANS(dl.lat)) *
                    POWER(SIN(RADIANS(dl.lng - ?) / 2), 2)
                )
            ) AS distance
        ', [$lat, $lat, $lng])
            ->where('p.is_status', 'active')
            ->where('p.has_ride', false)
            ->having('distance', '<=', $radiusKm)
            ->get();

        return $drivers;
    }
}
