<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Project;
use App\Notifications\ProjectAssignmentNotification;
use Illuminate\Console\Command;

class TestNotification extends Command
{
    protected $signature = 'test:notification {user_id?}';
    protected $description = 'Send a test notification to a user';

    public function handle()
    {
        $userId = $this->argument('user_id') ?? 1;
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        $project = Project::first();
        if (!$project) {
            $this->error("No project found in database.");
            return 1;
        }

        \App\Services\NotificationHelper::sendToUser(
            $user, 
            new ProjectAssignmentNotification($project, 'test-role')
        );

        $this->info("Notification sent to: {$user->email}");
        $this->info("User now has {$user->unreadNotifications()->count()} unread notifications.");

        return 0;
    }
}
