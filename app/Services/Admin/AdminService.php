<?php

namespace App\Services\Admin;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use App\Repositories\Admin\AdminRepository;

class AdminService
{
  protected UserRepository $userRepository;

   protected AdminRepository $AdminRepository;



  public function __construct(UserRepository $userRepository,AdminRepository $AdminRepository)
  {
    $this->userRepository = $userRepository;
    $this->AdminRepository = $AdminRepository;

  }

  public function login(array $data)
  {
    $user = $this->userRepository->findByEmail($data['email']);

    if (!$user || !Hash::check($data['password'], $user->password)) {
      throw new \Exception('Invalid Cardential');
    }

    if ($user->blocked) {
      throw new \Exception('This account is blocked');
    }

    $hasRole = $user->roles()->where('role_id', 4)->exists();

    if (!$hasRole) {
      throw new \Exception('this api for admins only');
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return [
      'user' => $user,
      'token' => $token,
    ];
  }

  public function updateProfile($userId, array $data)
  {
    $updateData = $data;

    if (isset($data['password'])) {
      $updateData['password'] = Hash::make($data['password']);
    }

    return $this->userRepository->update($userId, $updateData);
  }
//test
    public function getApprovedDriversCount(): int
    {
        return $this->AdminRepository->countApprovedDrivers();
    }

    public function getPendingDriversCount(): int
    {
        return $this->AdminRepository->countPendingDrivers();
    }

    public function getPassengersCount(): int
    {
        return $this->AdminRepository->countPassengers();
    }

    public function getTotalRidesCount(): int
    {
        return $this->AdminRepository->countAllRides();
    }

    public function getCompletedRidesCount(): int
    {
        return $this->AdminRepository->countCompletedRides();
    }

    public function getLastFiveRides()
    {
        return $this->AdminRepository->getLastFiveRidesWithPassengerAndDriver();
    }
//vehicle_types
    public function getAllVehicleTypes()
    {
        return $this->AdminRepository->getAllVehicleTypes();
    }

    public function toggleVehicleTypeStatus(int $vehicleTypeId): ?bool
    {
        return $this->AdminRepository ->toggleVehicleStatus($vehicleTypeId);
    }

     public function deleteVehicleTypes(array $ids): int
    {
        return $this->AdminRepository->deleteByIds($ids);
    }

    public function searchVehicleTypes(string $search)
    {
        return $this->AdminRepository->search($search);
    }

    public function createVehicleMake(array $data): int
    {
        return $this->AdminRepository->createVehicleMake($data);
    }

    public function searchVehicleMakes(string $search)
{
    return $this->AdminRepository->searchVehicleMakes($search);
}

    public function getAllVehicleMakes()
{
    return $this->AdminRepository->getAllVehicleMakes();
}
public function toggleVehicleMakeStatus(int $vehicleMakeId): ?bool
{
    return $this->AdminRepository
        ->toggleVehicleMakeStatus($vehicleMakeId);
}
public function deleteVehicleMakes(array $ids): int
{
    return $this->AdminRepository
        ->deleteVehicleMakesByIds($ids);
}

public function getVehicleMakesByType(string $type)
{
    return $this->AdminRepository
        ->getVehicleMakesByType($type);
}
}
