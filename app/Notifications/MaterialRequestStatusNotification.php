<?php

namespace App\Notifications;

use App\Models\MaterialRequest;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MaterialRequestStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected MaterialRequest $materialRequest;
    protected string $status;

    public function __construct(MaterialRequest $materialRequest, string $status)
    {
        $this->materialRequest = $materialRequest;
        $this->status = $status;
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
        $statusText = match ($this->status) {
            'approved' => 'disetujui',
            'level_approved' => 'disetujui di satu level',
            'rejected' => 'ditolak',
            'fulfilled' => 'telah dipenuhi',
            'pending' => 'menunggu persetujuan',
            default => $this->status,
        };

        $senderName = auth()->user()->name ?? 'Seseorang';
        $receiverName = $notifiable->name ?: 'Anda';

        return [
            'type' => 'material_request_status',
            'title' => 'Status Material Request',
            'message' => "{$senderName} mengubah status MR #{$this->materialRequest->code} menjadi {$statusText}",
            'mr_id' => $this->materialRequest->id,
            'mr_code' => $this->materialRequest->code,
            'project_id' => $this->materialRequest->project_id,
            'status' => $this->status,
            'url' => route('projects.mr.show', [
                'project' => $this->materialRequest->project_id,
                'mr' => $this->materialRequest->id,
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
        $statusText = match ($this->status) {
            'approved' => 'disetujui',
            'level_approved' => 'disetujui di satu level',
            'rejected' => 'ditolak',
            'fulfilled' => 'telah dipenuhi',
            default => $this->status,
        };

        $senderName = auth()->user()->name ?? 'Seseorang';
        $receiverName = $notifiable->name ?: 'Anda';

        return [
            'title' => 'Status Material Request',
            'body' => "{$senderName} mengubah status MR #{$this->materialRequest->code} menjadi {$statusText}",
            'data' => [
                'type' => 'material_request_status',
                'mr_id' => (string) $this->materialRequest->id,
                'project_id' => (string) $this->materialRequest->project_id,
                'url' => route('projects.mr.show', [
                    'project' => $this->materialRequest->project_id,
                    'mr' => $this->materialRequest->id,
                ]),
            ],
        ];
    }
}
