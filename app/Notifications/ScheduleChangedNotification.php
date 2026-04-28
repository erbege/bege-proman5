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

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', FcmChannel::class];
    }

    /**
     * Get the array representation of the notification for database.
     */
    public function toArray(object $notifiable): array
    {
        $senderName = auth()->user()->name ?? 'Seseorang';
        $receiverName = $notifiable->name ?: 'Anda';

        return [
            'type' => 'schedule_changed',
            'title' => 'Jadwal Proyek Diperbarui',
            'message' => "{$senderName} memperbarui jadwal proyek {$this->project->name}",
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'change_type' => $this->changeType,
            'details' => $this->details,
            'url' => route('projects.schedule.index', $this->project->id),
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): array
    {
        return [
            'data' => $this->toArray($notifiable),
        ];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm(object $notifiable): array
    {
        $senderName = auth()->user()->name ?? 'Seseorang';
        $receiverName = $notifiable->name ?: 'Anda';

        return [
            'title' => 'Jadwal Proyek Diperbarui',
            'body' => "{$senderName} memperbarui jadwal proyek {$this->project->name}",
            'data' => [
                'type' => 'schedule_changed',
                'project_id' => (string) $this->project->id,
                'url' => route('projects.schedule.index', $this->project->id),
            ],
        ];
    }
}
