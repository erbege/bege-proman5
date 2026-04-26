<?php

namespace App\Notifications\Channels;

use App\Services\FcmNotificationService;
use Illuminate\Notifications\Notification;

class FcmChannel
{
    protected FcmNotificationService $fcmService;

    public function __construct(FcmNotificationService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Send the given notification via FCM.
     */
    public function send($notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toFcm')) {
            return;
        }

        $fcmData = $notification->toFcm($notifiable);

        if (empty($fcmData)) {
            return;
        }

        $this->fcmService->sendToUser(
            $notifiable,
            $fcmData['title'] ?? 'Notifikasi',
            $fcmData['body'] ?? '',
            $fcmData['data'] ?? [],
            $fcmData['icon'] ?? null
        );
    }
}
