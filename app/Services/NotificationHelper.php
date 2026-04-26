<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Database\Eloquent\Collection;

class NotificationHelper
{
    /**
     * Admin roles that receive all notifications.
     */
    protected static array $adminRoles = ['super-admin', 'Superadmin', 'administrator'];

    /**
     * Get all admin users.
     */
    protected static function getAdmins(?int $excludeUserId = null): Collection
    {
        $query = User::role(self::$adminRoles);

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query->get();
    }

    /**
     * Merge users with admins, removing duplicates.
     */
    protected static function mergeWithAdmins($users, ?int $excludeUserId = null): Collection
    {
        $admins = self::getAdmins($excludeUserId);

        if ($users instanceof Collection) {
            return $users->merge($admins)->unique('id');
        }

        if (is_array($users)) {
            $usersCollection = collect($users);
            return $usersCollection->merge($admins)->unique('id');
        }

        return $admins;
    }

    /**
     * Send notification to a single user (+ admins).
     * 
     * @param User $user The target user
     * @param Notification $notification The notification instance
     * @param bool $includeAdmins Whether to also send to admin users
     * @return void
     */
    public static function sendToUser(User $user, Notification $notification, bool $includeAdmins = true): void
    {
        if ($includeAdmins) {
            $recipients = self::mergeWithAdmins(collect([$user]));
            NotificationFacade::send($recipients, $notification);
        } else {
            $user->notify($notification);
        }
    }

    /**
     * Send notification to multiple specific users (+ admins).
     * 
     * @param Collection|array $users Collection of User models or array of user IDs
     * @param Notification $notification The notification instance
     * @param bool $includeAdmins Whether to also send to admin users
     * @return void
     */
    public static function sendToUsers($users, Notification $notification, bool $includeAdmins = true): void
    {
        // If array of IDs, convert to User collection
        if (is_array($users) && !empty($users) && is_numeric($users[0])) {
            $users = User::whereIn('id', $users)->get();
        }

        if ($users instanceof Collection || is_array($users)) {
            if ($includeAdmins) {
                $recipients = self::mergeWithAdmins($users);
                NotificationFacade::send($recipients, $notification);
            } else {
                NotificationFacade::send($users, $notification);
            }
        }
    }

    /**
     * Send notification to all users with a specific role (+ admins).
     * 
     * @param string|array $roles Role name(s)
     * @param Notification $notification The notification instance
     * @param int|null $excludeUserId User ID to exclude (usually the actor)
     * @param bool $includeAdmins Whether to also send to admin users
     * @return void
     */
    public static function sendToRole($roles, Notification $notification, ?int $excludeUserId = null, bool $includeAdmins = true): void
    {
        $query = User::role($roles);

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        $users = $query->get();

        if ($includeAdmins) {
            $users = self::mergeWithAdmins($users, $excludeUserId);
        }

        if ($users->isNotEmpty()) {
            NotificationFacade::send($users, $notification);
        }
    }

    /**
     * Send notification to all users.
     * 
     * @param Notification $notification The notification instance
     * @param int|null $excludeUserId User ID to exclude (usually the actor)
     * @return void
     */
    public static function sendToAll(Notification $notification, ?int $excludeUserId = null): void
    {
        $query = User::query();

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        $users = $query->get();

        if ($users->isNotEmpty()) {
            NotificationFacade::send($users, $notification);
        }
    }

    /**
     * Send notification to project team members (+ admins).
     * 
     * @param \App\Models\Project $project The project
     * @param Notification $notification The notification instance
     * @param int|null $excludeUserId User ID to exclude (usually the actor)
     * @param bool $includeAdmins Whether to also send to admin users
     * @return void
     */
    public static function sendToProjectTeam($project, Notification $notification, ?int $excludeUserId = null, bool $includeAdmins = true): void
    {
        $query = $project->team();

        if ($excludeUserId) {
            $query->where('users.id', '!=', $excludeUserId);
        }

        $members = $query->get();

        if ($includeAdmins) {
            $members = self::mergeWithAdmins($members, $excludeUserId);
        }

        if ($members->isNotEmpty()) {
            NotificationFacade::send($members, $notification);
        }
    }

    /**
     * Send notification to users with specific permissions (+ admins).
     * 
     * @param string|array $permissions Permission name(s)
     * @param Notification $notification The notification instance
     * @param int|null $excludeUserId User ID to exclude
     * @param bool $includeAdmins Whether to also send to admin users
     * @return void
     */
    public static function sendToPermission($permissions, Notification $notification, ?int $excludeUserId = null, bool $includeAdmins = true): void
    {
        $query = User::permission($permissions);

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        $users = $query->get();

        if ($includeAdmins) {
            $users = self::mergeWithAdmins($users, $excludeUserId);
        }

        if ($users->isNotEmpty()) {
            NotificationFacade::send($users, $notification);
        }
    }

    /**
     * Send notification ONLY to admin users.
     * 
     * @param Notification $notification The notification instance
     * @param int|null $excludeUserId User ID to exclude
     * @return void
     */
    public static function sendToAdmins(Notification $notification, ?int $excludeUserId = null): void
    {
        $admins = self::getAdmins($excludeUserId);

        if ($admins->isNotEmpty()) {
            NotificationFacade::send($admins, $notification);
        }
    }
}
