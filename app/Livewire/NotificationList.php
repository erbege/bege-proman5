<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationList extends Component
{
    use WithPagination;

    public string $filter = 'all'; // all, unread

    public function setFilter(string $filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function markAsRead($notificationId)
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function deleteNotification($notificationId)
    {
        Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->delete();
    }

    public function deleteAll()
    {
        Auth::user()->notifications()->delete();
    }

    public function render()
    {
        $query = Auth::user()->notifications();

        if ($this->filter === 'unread') {
            $query = Auth::user()->unreadNotifications();
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);
        $unreadCount = Auth::user()->unreadNotifications()->count();

        return view('livewire.notification-list', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}
