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
        return ['database', FcmChannel::class];
    }

    /**
     * Get the array representation of the notification for database.
     */
    public function toArray(object $notifiable): array
    {
        $statusText = match ($this->status) {
            'approved' => 'disetujui',
            'rejected' => 'ditolak',
            'fulfilled' => 'telah dipenuhi',
            'pending' => 'menunggu persetujuan',
            default => $this->status,
        };

        return [
            'type' => 'material_request_status',
            'title' => 'Status Material Request',
            'message' => "MR #{$this->materialRequest->code} telah {$statusText}",
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
     * Get the FCM representation of the notification.
     */
    public function toFcm(object $notifiable): array
    {
        $statusText = match ($this->status) {
            'approved' => 'disetujui',
            'rejected' => 'ditolak',
            'fulfilled' => 'telah dipenuhi',
            default => $this->status,
        };

        return [
            'title' => 'Status Material Request',
            'body' => "MR #{$this->materialRequest->code} telah {$statusText}",
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
