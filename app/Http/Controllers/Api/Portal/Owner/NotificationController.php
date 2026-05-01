<?php

namespace App\Http\Controllers\Api\Portal\Owner;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * Get notifications for the owner.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 15);

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->successResponse(
            'Notifications retrieved successfully.',
            $notifications
        );
    }

    /**
     * Get recent unread notifications.
     */
    public function recent(): JsonResponse
    {
        $user = Auth::user();
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => class_basename($notification->type),
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                    'created_at_human' => $notification->created_at->diffForHumans(),
                ];
            });

        $unreadCount = $user->unreadNotifications()->count();

        return $this->successResponse(
            'Recent notifications retrieved.',
            [
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ]
        );
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(string $id): JsonResponse
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return $this->errorResponse('Notification not found', 404);
        }

        $notification->markAsRead();

        return $this->successResponse('Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return $this->successResponse('All notifications marked as read.');
    }

    /**
     * Delete a notification.
     */
    public function destroy(string $id): JsonResponse
    {
        $deleted = Auth::user()
            ->notifications()
            ->where('id', $id)
            ->delete();

        if (!$deleted) {
            return $this->errorResponse('Notification not found', 404);
        }

        return $this->successResponse('Notification deleted.');
    }
}
