<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
  public function findByPhone(string $phone): ?User
  {
    return User::where('phone', $phone)->first();
  }

  public function create(array $data): User
  {
    $user = User::create($data);

    $user->roles()->attach(1);

    return $user;
  }

  public function updateOtp(User $user, string $otp): bool
  {
    $user->otp = $otp;
    $user->otp_expire_at = now()->addMinutes(5);
    return $user->save();
  }

  public function clearOtp(User $user): bool
  {
    $user->otp = null;
    $user->otp_expire_at = null;
    return $user->save();
  }
}
