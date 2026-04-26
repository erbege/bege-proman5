<?php

namespace App\Notifications;

use App\Models\Project;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ScheduleChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Project $project;
    protected string $changeType;
    protected ?string $details;

    public function __construct(Project $project, string $changeType = 'updated', ?string $details = null)
    {
        $this->project = $project;
        $this->changeType = $changeType;
        $this->details = $details;
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
        return [
            'type' => 'schedule_changed',
            'title' => 'Jadwal Proyek Diperbarui',
            'message' => "Jadwal proyek {$this->project->name} telah diperbarui",
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'change_type' => $this->changeType,
            'details' => $this->details,
            'url' => route('projects.schedule.index', $this->project->id),
        ];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'Jadwal Proyek Diperbarui',
            'body' => "Jadwal proyek {$this->project->name} telah diperbarui",
            'data' => [
                'type' => 'schedule_changed',
                'project_id' => (string) $this->project->id,
                'url' => route('projects.schedule.index', $this->project->id),
            ],
        ];
    }
}
