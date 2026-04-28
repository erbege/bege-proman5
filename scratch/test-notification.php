<?php

use App\Models\User;
use App\Notifications\GenericNotification;

// Load user (change email if needed)
$user = User::first();

if (!$user) {
    echo "No user found.\n";
    exit;
}

echo "Sending notification to: " . $user->name . " (" . $user->email . ")\n";

// Send notification
$user->notify(new GenericNotification(
    'Uji Coba Realtime', 
    'Ini adalah notifikasi percobaan untuk Pusher dan Firebase - ' . now()->format('H:i:s'), 
    'test',
    route('dashboard')
));

echo "Notification triggered successfully.\n";
