<?php

namespace App\Repositories\Ride;

use App\Models\Ride;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RideRepository
{
    public function findForDriver(int $rideId, int $driverId)
    {
        return Ride::where('id', $rideId)
            ->where('driver_id', $driverId)
            ->lockForUpdate()
            ->first();
    }

    public function findForUser(int $id, int $userId): ?Ride
    {
        return Ride::where('id', $id)
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->first();
    }

    public function updateStatus(Ride $ride, string $status): void
    {
        $ride->update(['status' => $status]);
    }

    public function countCompletedByDriverForDate(int $driverId, Carbon $date): int
    {
        return Ride::query()
            ->where('driver_id', $driverId)
            ->where('status', 'completed')
            ->whereDate('created_at', $date)
            ->count();
    }

    public function dailyRides(int $driverId, string $date)
    {
        return Ride::query()
            ->where('driver_id', $driverId)
            ->whereDate('created_at', $date)
            ->whereIn('status', ['completed', 'cancelled'])
            ->with(['profit'])
            ->get()
            ->map(function ($ride) {
                return [
                    'ride_id' => $ride->id,
                    'status' => $ride->status,
                    'profit' => (float)optional($ride->profit)->amount ?? 0,
                    'created_at' => $ride->created_at,
                ];
            });
    }

    public function ridesBetween(int $driverId, $from, $to)
    {
        $query = Ride::query()
            ->where('driver_id', $driverId)
            ->whereIn('status', ['completed', 'cancelled'])
            ->orderBy('created_at', 'desc');

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        return $query->with(['driverProfit'])
            ->get()
            ->map(fn($ride) => [
                'ride_id' => $ride->id,
                'status' => $ride->status,
                'profit' => (float)optional($ride->driverProfit)->amount ?? 0,
                'created_at' => $ride->created_at,
            ]);
    }

    public function ridesBetweenDates(int $driverId, $from, $to)
    {
        $query = DB::table('rides as r')
            ->join('users as d', 'd.id', 'r.driver_id')
            ->join('users as u', 'u.id', 'r.user_id')
            ->leftJoin('driver_profits as p', 'p.ride_id', 'r.id')
            ->where('r.driver_id', $driverId)
            ->whereIn('r.status', ['completed', 'cancelled'])
            ->orderBy('r.created_at', 'desc');

        if ($from && $to) {
            $query->whereBetween('r.created_at', [$from, $to]);
        }
        return $query->select(
            'r.id as ride_id',
            'r.user_id as user_id',
            'u.name as user_name',
            'r.driver_id as driver_id',
            'd.name as driver_name',
            'r.distance_km',
            'r.duration_minutes',
            'r.start_lat',
            'r.start_lng',
            'r.end_lat',
            'r.end_lng',
            'r.price',
            'r.status',
            'r.code',
            DB::raw('CAST(COALESCE(p.amount, 0) AS DOUBLE) as profit'),
            DB::raw('DATE(r.created_at) as date'),
            DB::raw('TIME(r.created_at) as time')
        );
    }

    public function ridesWithCommisions($from = null, $to = null)
    {
        $query = DB::table('rides as r')
            ->join('users as d', 'd.id', '=', 'r.driver_id')
            ->join('users as u', 'u.id', '=', 'r.user_id')
            ->leftJoin('company_commissions as c', 'c.ride_id', '=', 'r.id')
            ->whereIn('r.status', ['completed', 'cancelled'])
            ->orderBy('r.created_at', 'desc');

        if ($from && $to) {
            $query->whereBetween('r.created_at', [$from, $to]);
        }

        return $query
            ->select(
                'r.id as ride_id',
                'r.user_id as user_id',
                'u.name as user_name',
                'r.driver_id as driver_id',
                'd.name as driver_name',
                'r.distance_km',
                'r.duration_minutes',
                'r.start_lat',
                'r.start_lng',
                'r.end_lat',
                'r.end_lng',
                'r.price',
                'r.status',
                'r.code',
                DB::raw('CAST(COALESCE(c.amount, 0) AS DOUBLE) as profit'),
                DB::raw('DATE(r.created_at) as date'),
                DB::raw('TIME(r.created_at) as time')
            );
    }


}
