<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FcmNotificationService
{
    protected Messaging $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    /**
     * Send notification to a specific user (all their devices).
     */
    public function sendToUser(
        User $user,
        string $title,
        string $body,
        array $data = [],
        ?string $icon = null
    ): array {
        $tokens = $user->fcmTokens()->pluck('token')->toArray();

        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No FCM tokens found for user'];
        }

        return $this->sendToTokens($tokens, $title, $body, $data, $icon);
    }

    /**
     * Send notification to multiple users.
     */
    public function sendToUsers(
        Collection $users,
        string $title,
        string $body,
        array $data = [],
        ?string $icon = null
    ): array {
        $tokens = [];

        foreach ($users as $user) {
            $userTokens = $user->fcmTokens()->pluck('token')->toArray();
            $tokens = array_merge($tokens, $userTokens);
        }

        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No FCM tokens found for users'];
        }

        return $this->sendToTokens(array_unique($tokens), $title, $body, $data, $icon);
    }

    /**
     * Send notification to a topic.
     */
    public function sendToTopic(
        string $topic,
        string $title,
        string $body,
        array $data = [],
        ?string $icon = null
    ): array {
        try {
            $notification = Notification::create($title, $body, $icon);

            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($message);

            return ['success' => true, 'message' => "Notification sent to topic: {$topic}"];
        } catch (MessagingException $e) {
            Log::error('[FCM] Failed to send to topic', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send notification to specific FCM tokens.
     */
    protected function sendToTokens(
        array $tokens,
        string $title,
        string $body,
        array $data = [],
        ?string $icon = null
    ): array {
        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No tokens provided'];
        }

        try {
            $notification = Notification::create($title, $body, $icon);

            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data);

            $report = $this->messaging->sendMulticast($message, $tokens);

            // Remove invalid tokens from database
            $this->cleanupInvalidTokens($report->invalidTokens());

            $successCount = $report->successes()->count();
            $failureCount = $report->failures()->count();

            Log::info('[FCM] Multicast sent', [
                'success_count' => $successCount,
                'failure_count' => $failureCount,
            ]);

            return [
                'success' => $successCount > 0,
                'message' => "Sent to {$successCount} devices, {$failureCount} failed",
                'success_count' => $successCount,
                'failure_count' => $failureCount,
            ];
        } catch (MessagingException $e) {
            Log::error('[FCM] Failed to send multicast', [
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Remove invalid tokens from database.
     */
    protected function cleanupInvalidTokens(array $invalidTokens): void
    {
        if (!empty($invalidTokens)) {
            \App\Models\FcmToken::whereIn('token', $invalidTokens)->delete();

            Log::info('[FCM] Cleaned up invalid tokens', [
                'count' => count($invalidTokens),
            ]);
        }
    }

    /**
     * Subscribe tokens to a topic.
     */
    public function subscribeToTopic(array $tokens, string $topic): bool
    {
        try {
            $this->messaging->subscribeToTopic($topic, $tokens);
            return true;
        } catch (MessagingException $e) {
            Log::error('[FCM] Failed to subscribe to topic', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Unsubscribe tokens from a topic.
     */
    public function unsubscribeFromTopic(array $tokens, string $topic): bool
    {
        try {
            $this->messaging->unsubscribeFromTopic($topic, $tokens);
            return true;
        } catch (MessagingException $e) {
            Log::error('[FCM] Failed to unsubscribe from topic', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
