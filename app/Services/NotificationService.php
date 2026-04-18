<?php

namespace App\Services;



class NotificationService
{
    public static function send(
        int $userId,
        string $type,
        string $title,
        string $body,
        array $data = []
    ) {
        $user = \App\Models\User::find($userId);

        if (!$user || !$user->fcm_token) {
            \Log::warning('No FCM token', ['user_id' => $userId]);
            return false;
        }

        $firebase = new FirebaseNotificationService();

        return $firebase->send(
            $user->fcm_token,
            $title,
            $body,
            array_merge($data, ['type' => $type])
        );
    }
}
