<?php

namespace App\Notifications;

use App\Models\MaterialRequest;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MaterialRequestCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected MaterialRequest $materialRequest;

    public function __construct(MaterialRequest $materialRequest)
    {
        $this->materialRequest = $materialRequest;
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
            'type' => 'material_request_created',
            'title' => 'Material Request Baru',
            'message' => "MR baru ({$this->materialRequest->code}) memerlukan approval - {$this->materialRequest->project->name}",
            'material_request_id' => $this->materialRequest->id,
            'material_request_code' => $this->materialRequest->code,
            'project_id' => $this->materialRequest->project_id,
            'project_name' => $this->materialRequest->project->name,
            'requester' => $this->materialRequest->user->name ?? 'Unknown',
            'items_count' => $this->materialRequest->items()->count(),
            'url' => route('projects.mr.show', [$this->materialRequest->project_id, $this->materialRequest->id]),
        ];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'Material Request Baru',
            'body' => "MR baru ({$this->materialRequest->code}) memerlukan approval",
            'data' => [
                'type' => 'material_request_created',
                'material_request_id' => (string) $this->materialRequest->id,
                'url' => route('projects.mr.show', [$this->materialRequest->project_id, $this->materialRequest->id]),
            ],
        ];
    }
}
