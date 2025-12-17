<?php

namespace App\Repositories;

use App\Models\DriverProfile;

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
}
