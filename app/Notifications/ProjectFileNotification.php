<?php

namespace App\Notifications;

use App\Models\ProjectFile;
use App\Models\User;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProjectFileNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ProjectFile $file;
    protected string $action;
    protected ?User $performer;
    protected ?string $customMessage;

    /**
     * @param ProjectFile $file
     * @param string $action (uploaded, updated, commented, status_changed)
     * @param User|null $performer
     * @param string|null $customMessage
     */
    public function __construct(ProjectFile $file, string $action, ?User $performer = null, ?string $customMessage = null)
    {
        $this->file = $file;
        $this->action = $action;
        $this->performer = $performer;
        $this->customMessage = $customMessage;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', FcmChannel::class];
    }

    /**
     * Get the array representation of the notification for database.
     */
    public function toArray(object $notifiable): array
    {
        $title = $this->getNotificationTitle();
        $message = $this->customMessage ?: $this->getNotificationMessage($notifiable);

        return [
            'type' => 'project_file_' . $this->action,
            'title' => $title,
            'message' => $message,
            'file_id' => $this->file->id,
            'file_name' => $this->file->name,
            'project_id' => $this->file->project_id,
            'project_name' => $this->file->project->name,
            'performer_name' => $this->performer?->name,
            'url' => route('projects.files.show', [
                'project' => $this->file->project_id,
                'file' => $this->file->id,
            ]),
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
        return [
            'title' => $this->getNotificationTitle(),
            'body' => $this->customMessage ?: $this->getNotificationMessage($notifiable),
            'data' => [
                'type' => 'project_file_' . $this->action,
                'file_id' => (string) $this->file->id,
                'project_id' => (string) $this->file->project_id,
                'url' => route('projects.files.show', [
                    'project' => $this->file->project_id,
                    'file' => $this->file->id,
                ]),
            ],
        ];
    }

    protected function getNotificationTitle(): string
    {
        return match ($this->action) {
            'uploaded' => 'File Proyek Baru',
            'updated' => 'Versi File Baru',
            'commented' => 'Komentar File Baru',
            'status_changed' => 'Status File Diperbarui',
            default => 'Notifikasi File Proyek',
        };
    }

    protected function getNotificationMessage(object $notifiable): string
    {
        $performerName = $this->performer?->name ?: 'Seseorang';
        $receiverName = $notifiable->name ?: 'Anda';
        
        $prefix = "{$performerName}";

        return match ($this->action) {
            'uploaded' => "{$prefix} mengunggah file baru: {$this->file->name}",
            'updated' => "{$prefix} mengunggah versi baru untuk: {$this->file->name}",
            'commented' => "{$prefix} memberikan komentar pada: {$this->file->name}",
            'status_changed' => "{$prefix} mengubah status file {$this->file->name} menjadi " . strtoupper($this->file->status),
            default => "{$prefix} melakukan aktivitas pada file {$this->file->name}",
        };
    }
}
