<?php

namespace App\Services\Notifications;

use Twilio\Rest\Client;

class WhatsAppService
{
    protected Client $client;
    protected string $from;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );

        $this->from = config('services.twilio.whatsapp_from');
    }

    public function sendOtp(string $phone, string $otp): void
    {
        $this->client->messages->create(
            "whatsapp:+$phone",
            [
                "from" => $this->from,
                "body" => "رمز التحقق الخاص بك هو: $otp"
            ]
        );
    }
}
