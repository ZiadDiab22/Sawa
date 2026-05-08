<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = env('ISHAAR_BASE_URL');
        $this->apiKey = env('ISHAAR_API_KEY');
    }

    public function sendOtp(string $phone, string $otp): bool
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Accept-Language' => 'ar',
            'x-api-key' => $this->apiKey,
        ])->asMultipart()->post($this->baseUrl . '/message-auth', [
            [
                'name' => 'phone',
                'contents' => $phone,
            ],
            [
                'name' => 'code',
                'contents' => $otp,
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to send WhatsApp OTP');
        }

        return true;
    }
}
