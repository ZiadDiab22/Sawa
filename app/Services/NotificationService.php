<?php

namespace App\Services;

use App\Models\User;



class NotificationService
{
    public static function sendToUser(User $user, string $type, string $title, string $body, array $data = [])
    {
        if (!$user || empty($user->fcm_token)) {
            \Log::warning('User has no FCM token', ['user_id' => $user?->id]);
            return false;
        }

        try {
            $firebaseService = new FirebaseNotificationService();

            $result = $firebaseService->send(
                $user->fcm_token,
                $title,
                $body,
                array_merge($data, ['type' => $type])
            );

            \Log::info('Firebase send result:', [
                'user_id' => $user->id,
                'type' => $type,
                'result' => $result,
            ]);

            return $result;

        } catch (\Throwable $e) {
            \Log::error('Failed to send notification: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'type' => $type
            ]);
            return false;
        }
    }
}
