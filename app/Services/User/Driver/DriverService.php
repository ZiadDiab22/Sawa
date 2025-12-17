<?php

namespace App\Services\User\Driver;

use App\Repositories\UserRepository;

class DriverService
{
  protected UserRepository $userRepository;

  public function __construct(UserRepository $userRepository)
  {
    $this->userRepository = $userRepository;
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
}
