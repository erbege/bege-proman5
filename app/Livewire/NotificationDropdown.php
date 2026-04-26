<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationDropdown extends Component
{
    public bool $isOpen = false;
    public int $unreadCount = 0;

    protected $listeners = [
        'refreshNotifications' => 'loadNotifications',
        'echo:notifications,NewNotification' => 'loadNotifications',
    ];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        if (Auth::check()) {
            $this->unreadCount = Auth::user()->unreadNotifications()->count();
        }
    }

    public function toggleDropdown()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function getNotificationsProperty()
    {
        if (!Auth::check()) {
            return collect();
        }

        return Auth::user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }

    public function markAsRead($notificationId)
    {
        if (!Auth::check()) {
            return;
        }

        $notification = Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

    public function markAllAsRead()
    {
        if (!Auth::check()) {
            return;
        }

        Auth::user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function deleteNotification($notificationId)
    {
        if (!Auth::check()) {
            return;
        }

        Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->delete();

        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.notification-dropdown');
    }
}
