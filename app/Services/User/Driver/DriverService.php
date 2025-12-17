<?php

namespace App\Services\User\Driver;

use App\Models\User;
use App\Repositories\DriverRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;

class DriverService
{
  protected UserRepository $userRepository;
  protected DriverRepository $driverRepository;

  public function __construct(UserRepository $userRepository, DriverRepository $driverRepository)
  {
    $this->userRepository = $userRepository;
    $this->driverRepository = $driverRepository;
  }

  public function register(array $data)
  {
    $user = $this->userRepository->create($data);
    $token = $user->createToken('api_token')->plainTextToken;

    return [
      'user'  => $user,
      'token' => $token,
    ];
  }

  public function toggleStatus()
  {
    $driverId = Auth::id();

    $driver = $this->driverRepository->findProfileById($driverId);

    $driver = $this->driverRepository->toggleStatus($driver);

    return $driver;
  }

  public function accept($id)
  {
    $exists = User::where('id', $id)
      ->whereHas('driverProfile')
      ->exists();

    if (!$exists) throw new \Exception('User not found');

    $driver = $this->driverRepository->findProfileById($id);

    $driver = $this->driverRepository->accept($driver);

    return $driver;
  }
}
