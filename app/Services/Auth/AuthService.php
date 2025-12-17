<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\UserRepository;

class AuthService
{
  protected UserRepository $userRepository;

  public function __construct(UserRepository $userRepository)
  {
    $this->userRepository = $userRepository;
  }

  public function sendOtp(string $phone): bool
  {
    $user = $this->userRepository->findByPhone($phone);

    if (!$user) {
      throw new \Exception('User not found');
    }

    $otp = rand(100000, 999999);
    $this->userRepository->updateOtp($user, $otp);

    // هنا يمكنك إرسال OTP عبر SMS أو أي خدمة خارجية
    // SmsService::send($phone, "Your OTP is: $otp");

    return true;
  }

  public function verifyOtp(string $phone, string $otp): string
  {
    $user = $this->userRepository->findByPhone($phone);

    if (!$user) {
      throw new \Exception('User not found');
    }

    if ($user->otp !== $otp || now()->gt($user->otp_expire_at)) {
      throw new \Exception('Invalid or expired OTP');
    }

    $this->userRepository->clearOtp($user);
    $user->is_verified = true;
    $user->save();

    $token = $user->createToken('auth_token')->plainTextToken;

    return $token;
  }

  public function logout($user): bool
  {
    $user->tokens()->delete();
    return true;
  }

  public function login($request)
  {
    $user = User::where('phone', $request['phone'])->first();
    $hasRole = $user->roles()->where('role_id', 1)->exists();

    if (!$hasRole) {
      throw new \Exception('this api for users ( passengers ) only');
    }

    return $user->createToken('auth_token')->plainTextToken;
  }
}
