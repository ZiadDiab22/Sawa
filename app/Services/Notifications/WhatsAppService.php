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
        ])->post('https://raselsms.com/api/v2/messages/send', [

            'channel' => 'official_whatsapp_otp',

            'messageType' => 'otp',

            'to' => $phone,

            'options' => [
                'priority' => 'high'
            ],

            'template' => [
                'id' => '69b7f49018e150aca6099ecb',

                'key' => 'm_auth_template',

                'language' => 'ar',

                'variablesIndexed' => [
                    (string)$otp
                ]
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


// namespace App\Services\Notifications;

// use Illuminate\Support\Facades\Http;

// class WhatsAppService
// {
//     public function sendOtp($phone, $otp)
//     {
//         $response = Http::withHeaders([
//             'X-API-Key' => env('RASEL_API_KEY'),
//             'Content-Type' => 'application/json',
//         ])->post('https://raselsms.com/api/v2/messages/send', [

//             'to' => $phone,

//             'channel' => 'whatsapp',

//             'messageType' => 'free_text',

//             'content' => [
//                 'text' => "رمز التحقق الخاص بك هو: {$otp}"
//             ],

//             'options' => [
//                 'priority' => 'high'
//             ]

//         ]);

//         if (!$response->successful()) {
//             throw new \Exception(
//                 'WhatsApp OTP failed: ' . $response->body()
//             );
//         }

//         return $response->json();
//     }
