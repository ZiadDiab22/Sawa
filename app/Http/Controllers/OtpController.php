<?php

namespace App\Http\Controllers;

use App\Services\OTPService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OtpController extends Controller
{
   public function send(Request $request, OTPService $otpService): JsonResponse
{
    $data = $request->validate([
        'phone' => ['required', 'string'],
    ]);

    $sent = $otpService->sendOtp($data['phone']);

    if (!$sent) {
        throw ValidationException::withMessages([
            'phone' => ['Failed to send OTP'],
        ]);
    }

    return response()->json([
        'message' => 'OTP sent successfully',
    ]);
}

public function verify(Request $request, OTPService $otpService): JsonResponse
{
    $data = $request->validate([
        'phone' => ['required', 'string'],
        'otp'   => ['required', 'string'],
    ]);

    try {
        $result = $otpService->verifyOtp(
            $data['phone'],
            $data['otp']
        );

        return response()->json($result);

    } catch (\Exception $e) {
        throw ValidationException::withMessages([
            'otp' => [$e->getMessage()],
        ]);
    }
}

}
