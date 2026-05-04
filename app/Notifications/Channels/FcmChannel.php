<?php

namespace App\Notifications\Channels;

use App\Services\FcmNotificationService;
use Illuminate\Notifications\Notification;

class FcmChannel
{
    /**
     * Send the given notification via FCM.
     * Lazily resolves the FCM service and gracefully handles failures so
     * notification channels do not cause HTTP 500 in tests/environments
     * where FCM or its dependencies are unavailable.
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

        try {
            $fcmService = app()->make(\App\Services\FcmNotificationService::class);
        } catch (\Throwable $e) {
            // If FCM service cannot be resolved (misconfigured in testing), log and skip
            \Log::warning('[FCM] Skipping send - FcmNotificationService unavailable', ['error' => $e->getMessage()]);
            return;
        }

        try {
            $fcmService->sendToUser(
                $notifiable,
                $fcmData['title'] ?? 'Notifikasi',
                $fcmData['body'] ?? '',
                $fcmData['data'] ?? [],
                $fcmData['icon'] ?? null
            );
        } catch (\Throwable $e) {
            // Log failure but do not interrupt the request lifecycle
            \Log::error('[FCM] Failed to send notification', ['error' => $e->getMessage()]);
        }
    }
}
