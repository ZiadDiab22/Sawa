<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        try {
            $serviceAccountPath = storage_path('app/firebase/firebase.json');

            if (!file_exists($serviceAccountPath)) {
                \Log::error('Firebase Service Account file not found at: ' . $serviceAccountPath);
                return;
            }

            $factory = (new Factory)
                ->withServiceAccount($serviceAccountPath);

            $this->messaging = $factory->createMessaging();

        } catch (\Throwable $e) {
            \Log::error('Firebase initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * إرسال إشعار لمستخدم
     *
     * @param string $token
     * @param string $title
     * @param string $body
     * @param array $data
     * @return mixed
     */
    public function send(string $token, string $title, string $body, array $data = [])
    {
        if (!$this->messaging) {
            \Log::warning('Firebase Messaging service not initialized. Notification skipped.');
            return false;
        }

        try {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $result = $this->messaging->send($message);

            \Log::info('Firebase send result:', [
                'token' => $token,
                'title' => $title,
                'body'  => $body,
                'data'  => $data,
                'result'=> $result,
            ]);

            return $result;

        } catch (\Throwable $e) {
            \Log::error('Firebase error:', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'token'   => $token,
                'title'   => $title,
                'body'    => $body,
            ]);

            return false;
        }
    }
}
