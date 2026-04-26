<?php

namespace App\Notifications;

use App\Models\Project;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProjectAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Project $project;
    protected string $role;

    public function __construct(Project $project, string $role)
    {
        $this->project = $project;
        $this->role = $role;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    /**
     * Get the array representation of the notification for database.
     */
    public function toArray(object $notifiable): array
    {
        $roleLabel = ucwords(str_replace(['-', '_'], ' ', $this->role));

        return [
            'type' => 'project_assignment',
            'title' => 'Penugasan Proyek Baru',
            'message' => "Anda ditugaskan sebagai {$roleLabel} di proyek {$this->project->name}",
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'project_code' => $this->project->code,
            'role' => $this->role,
            'url' => route('projects.show', $this->project->id),
        ];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm(object $notifiable): array
    {
        $roleLabel = ucwords(str_replace(['-', '_'], ' ', $this->role));

        return [
            'title' => 'Penugasan Proyek Baru',
            'body' => "Anda ditugaskan sebagai {$roleLabel} di proyek {$this->project->name}",
            'data' => [
                'type' => 'project_assignment',
                'project_id' => (string) $this->project->id,
                'url' => route('projects.show', $this->project->id),
            ],
        ];
    }
}
