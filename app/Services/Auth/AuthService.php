<?php
namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Mail;
use App\Services\Notifications\WhatsAppService;
// use Illuminate\Support\Facades\Hash;


class AuthService
{
  protected UserRepository $userRepository;

  public function __construct(UserRepository $userRepository)
  {
    $this->userRepository = $userRepository;
  }

  
   public function sendOtp(string $phone, string $channel = 'email'): bool
{
    $user = $this->userRepository->findByPhone($phone);
    if (!$user) throw new \Exception('Invalid credentials', 401);

    $otp = rand(100000, 999999);
    $this->userRepository->updateOtp($user, $otp);

    if ($channel === 'email') {
        Mail::to($user->email)
            ->send(new \App\Mail\OtpMail($otp, $user->name));
    } elseif ($channel === 'whatsapp') {
        app(WhatsAppService::class)->sendOtp($user->phone, $otp);
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
    $user->is_verified = true;
    $user->save();

    return $user->createToken('auth_token')->plainTextToken;
  }

  public function logout($user): bool
  {
    $user->tokens()->delete();
    return true;
  }

  public function login($request)
  {
    $user = User::where('phone', $request['phone'])->first();
    if (!$user->roles()->where('role_id', 1)->exists())
      throw new \Exception('this api for users ( passengers ) only');

    return $user->createToken('auth_token')->plainTextToken;
  }
}
