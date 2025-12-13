<?php

namespace App\Repositories;

use App\Models\DriverProfile;

class DriverRepository
{
    public function getByUserId(int $userId): DriverProfile
    {
        return DriverProfile::with('user')
            ->where('user_id', $userId)
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

}
