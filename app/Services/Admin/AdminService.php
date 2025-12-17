<?php

namespace App\Services\Admin;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class AdminService
{
  protected UserRepository $userRepository;

  public function __construct(UserRepository $userRepository)
  {
    $this->userRepository = $userRepository;
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
}
