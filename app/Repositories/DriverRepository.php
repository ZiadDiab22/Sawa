<?php

namespace App\Repositories;

use App\Events\DriverActivated;
use App\Models\DriverProfile;
use App\Models\UserRole;
use Illuminate\Support\Facades\Auth;

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
        DriverProfile::where('user_id', $id)->update([
            'status' => $status
        ]);
    }

    public function toggleStatus(DriverProfile $driver)
    {
        if ($driver->is_status === 'active') $driver->is_status = 'inactive';
        else {
            $driver->is_status = 'active';
            broadcast(new DriverActivated(Auth::id()));
        }

        $driver->save();
        return $driver;
    }

    public function accept(DriverProfile $driver)
    {
        UserRole::firstOrCreate([
            'user_id' => $driver->user_id,
            'role_id' => 2
        ]);

        $driver->status = 'approved';
        $driver->save();
        return $driver;
    }

    public function decrementWallet(int $driverId, float $amount): void
    {
        DriverProfile::where('user_id', $driverId)
            ->decrement('wallet', $amount);
    }

    public function incrementWallet(int $driverId, float $amount): void
    {
        DriverProfile::where('user_id', $driverId)
            ->increment('wallet', $amount);
    }


    public function getVehicleInfoByUserId(int $userId)
{
    return DriverProfile::where('user_id', $userId)
    ->with(['vehicleMake', 'vehicleType', 'user'])
    ->firstOrFail();
}


// إضافة
public function getDriverWithDocumentsByUserId(int $userId)
{
    return DriverProfile::where('user_id', $userId)
        ->with(['documents'])
        ->firstOrFail();
}

 public function getWalletByUserId(int $userId): float
    {
        return (float) DriverProfile::where('user_id', $userId)
            ->value('wallet') ?? 0;
    }
}
