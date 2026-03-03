<?php

namespace App\Services;

use App\Models\Notification;
use App\Services\FirebaseNotificationService;

class NotificationService
{
    public static function send($user, $title, $body, $type, $data = [])
    {
        Notification::create([
            'user_id' => $user->id,
            'title'   => $title,
            'body'    => $body,
            'type'    => $type,
        ]);

        if ($user->fcm_token) {
            app(FirebaseNotificationService::class)
                ->send($user->fcm_token, $title, $body);
        }
    }
}
