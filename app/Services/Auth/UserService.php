<?php
namespace App\Services\Auth;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Mail;

class UserService
{
  protected UserRepository $userRepository;

  public function __construct(UserRepository $userRepository)
  {
    $this->userRepository = $userRepository;
  }

  public function register(array $data)
  {
    return ['user' => $this->userRepository->create($data)];
  }

 public function sendOtp(string $phone, string $channel = 'email'): bool
{
    $user = $this->userRepository->findByPhone($phone);
    if (!$user) throw new \Exception('User not found');

    $otp = rand(100000, 999999);
    $this->userRepository->updateOtp($user, $otp);

    if ($channel === 'email') {
        Mail::to($user->email)
            ->send(new \App\Mail\OtpMail($otp, $user->name));
            
    } elseif ($channel === 'whatsapp') {
        app(\App\Services\Notifications\WhatsAppService::class)
            ->sendOtp($user->phone, $otp);
    } else {
        throw new \Exception('Invalid channel', 400);
    }

    return true;
}


  public function verifyOtp(string $phone, string $otp): string
  {
    $user = $this->userRepository->findByPhone($phone);
    if (!$user) throw new \Exception('User not found');

    if ($user->otp !== $otp || now()->gt($user->otp_expire_at))
      throw new \Exception('Invalid or expired OTP');

    $this->userRepository->clearOtp($user);
    return $user->createToken('auth_token')->plainTextToken;
  }

  public function logout($user): bool
  {
    $user->tokens()->delete();
    return true;
  }
}
