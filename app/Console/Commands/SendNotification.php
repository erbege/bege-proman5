<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\GenericNotification;
use App\Services\NotificationHelper;
use Illuminate\Console\Command;

class SendNotification extends Command
{
    protected $signature = 'notify:send 
                            {--user= : Single user ID}
                            {--users= : Comma-separated user IDs}
                            {--role= : Role name}
                            {--all : Send to all users}
                            {--title= : Notification title}
                            {--message= : Notification message}
                            {--type=general : Notification type}';

    protected $description = 'Send notifications to users with various targeting options';

    public function handle()
    {
        $title = $this->option('title') ?? 'Test Notification';
        $message = $this->option('message') ?? 'This is a test notification from PROMAN5';
        $type = $this->option('type') ?? 'general';

        $notification = new GenericNotification($title, $message, $type);

        // Send to single user
        if ($userId = $this->option('user')) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }

            NotificationHelper::sendToUser($user, $notification);
            $this->info("Notification sent to: {$user->email}");
            return 0;
        }

        // Send to multiple users
        if ($userIds = $this->option('users')) {
            $ids = explode(',', $userIds);
            $users = User::whereIn('id', $ids)->get();

            if ($users->isEmpty()) {
                $this->error("No users found with IDs: {$userIds}");
                return 1;
            }

            NotificationHelper::sendToUsers($users, $notification);
            $this->info("Notification sent to {$users->count()} users:");
            $users->each(fn($u) => $this->line("  - {$u->email}"));
            return 0;
        }

        // Send to users with role
        if ($role = $this->option('role')) {
            $users = User::role($role)->get();

            if ($users->isEmpty()) {
                $this->error("No users found with role: {$role}");
                return 1;
            }

            NotificationHelper::sendToRole($role, $notification);
            $this->info("Notification sent to {$users->count()} users with role '{$role}':");
            $users->each(fn($u) => $this->line("  - {$u->email}"));
            return 0;
        }

        // Send to all users
        if ($this->option('all')) {
            $users = User::all();

            if ($users->isEmpty()) {
                $this->error("No users found in database.");
                return 1;
            }

            NotificationHelper::sendToAll($notification);
            $this->info("Notification sent to all {$users->count()} users.");
            return 0;
        }

        // Show usage help if no target specified
        $this->warn("Please specify a target:");
        $this->line("  --user=ID          Send to single user");
        $this->line("  --users=1,2,3      Send to multiple users");
        $this->line("  --role=admin       Send to users with role");
        $this->line("  --all              Send to all users");
        $this->line("");
        $this->line("Optional:");
        $this->line("  --title=\"Title\"    Notification title");
        $this->line("  --message=\"Msg\"    Notification message");
        $this->line("  --type=general     Notification type");

        return 1;
    }
}
