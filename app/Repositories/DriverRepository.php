<?php

namespace App\Repositories;

use App\Models\DriverProfile;
use SebastianBergmann\CodeCoverage\Driver\Driver;

class DriverRepository
{

    public function create(array $data): DriverProfile
    {
        return DriverProfile::create($data);
    }

    public function updateByUserId(int $userId, array $data): DriverProfile
    {
        $profile = $this->findByUserId($userId);
        $profile->update($data);
        return $profile;
    }

    public function findByUserId(int $userId): DriverProfile
    {
        return DriverProfile::where('user_id', $userId)
            ->with(['vehicleMake', 'vehicleType'])
            ->firstOrFail();
    }

    public function updateOrCreate(int $userId, array $data): DriverProfile
    {
        return DriverProfile::updateOrCreate(
            ['user_id' => $userId],
            $data
        );
    }

    public function getByStatus(string $status)
    {
        return DriverProfile::with('user')
            ->where('status', $status)
            ->get();
    }

    public function findById(int $id): DriverProfile
    {
        return DriverProfile::findOrFail($id);
    }

    public function findProfileById(int $id)
    {
        return DriverProfile::where('user_id', $id)->first();
    }

    public function findByIdWithUser(int $id): DriverProfile
    {
        return DriverProfile::with('user')->findOrFail($id);
    }

    public function updateStatus(int $id, string $status): void
    {
        DriverProfile::where('id', $id)->update([
            'status' => $status
        ]);
    }

    public function toggleStatus(DriverProfile $driver)
    {
        $driver->is_status = $driver->is_status === 'active' ? 'inactive' : 'active';
        $driver->save();
        return $driver;
    }
}
