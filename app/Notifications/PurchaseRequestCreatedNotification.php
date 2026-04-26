<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PurchaseRequestCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected PurchaseRequest $purchaseRequest;

    public function __construct(PurchaseRequest $purchaseRequest)
    {
        $this->purchaseRequest = $purchaseRequest;
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
        $priorityLabel = match ($this->purchaseRequest->priority) {
            'urgent' => '🔴 Urgent',
            'high' => '🟠 High',
            'normal' => '🟢 Normal',
            'low' => '🔵 Low',
            default => $this->purchaseRequest->priority,
        };

        return [
            'type' => 'purchase_request_created',
            'title' => 'Purchase Request Baru',
            'message' => "PR baru memerlukan approval - {$this->purchaseRequest->project->name} ({$priorityLabel})",
            'purchase_request_id' => $this->purchaseRequest->id,
            'purchase_request_code' => $this->purchaseRequest->code,
            'project_id' => $this->purchaseRequest->project_id,
            'project_name' => $this->purchaseRequest->project->name,
            'priority' => $this->purchaseRequest->priority,
            'requester' => $this->purchaseRequest->requestedBy->name ?? 'Unknown',
            'items_count' => $this->purchaseRequest->items()->count(),
            'url' => route('projects.pr.show', [$this->purchaseRequest->project_id, $this->purchaseRequest->id]),
        ];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'Purchase Request Baru',
            'body' => "PR baru memerlukan approval - {$this->purchaseRequest->project->name}",
            'data' => [
                'type' => 'purchase_request_created',
                'purchase_request_id' => (string) $this->purchaseRequest->id,
                'url' => route('projects.pr.show', [$this->purchaseRequest->project_id, $this->purchaseRequest->id]),
            ],
        ];
    }
}
