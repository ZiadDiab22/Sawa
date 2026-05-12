<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    public function sendOtp($phone, $otp)
    {
        $response = Http::withHeaders([
            'X-API-Key' => env('RASEL_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post(env('RASEL_BASE_URL') . '/api/v2/messages/send', [

            'to' => $phone,

            'channel' => 'whatsapp',

            'messageType' => 'free_text',

            'content' => [
                'text' => "رمز التحقق الخاص بك هو: {$otp}"
            ],

            'options' => [
                'priority' => 'high'
            ]

        ]);

        if (!$response->successful()) {
            throw new \Exception(
                'WhatsApp OTP failed: ' . $response->body()
            );
        }

        return $response->json();
    }
}
