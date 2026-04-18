<?php

namespace App\Services;

// use Kreait\Firebase\Factory;
// use Kreait\Firebase\Messaging\CloudMessage;
// use Kreait\Firebase\Messaging\Notification;
class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = app('firebase.messaging');
    }

    public function send(string $token, string $title, string $body, array $data = [])
    {
        if (!$this->messaging) {
            \Log::error('Firebase not initialized');
            return false;
        }

        try {
            $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $token)
                ->withNotification(
                    \Kreait\Firebase\Messaging\Notification::create($title, $body)
                )
                ->withData($data);

            $this->messaging->send($message);

            return true;

        } catch (\Throwable $e) {
            \Log::error('Firebase error', [
                'message' => $e->getMessage(),
                'token' => $token
            ]);

            return false;
        }
    }
}
