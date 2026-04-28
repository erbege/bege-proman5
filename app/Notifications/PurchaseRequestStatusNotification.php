<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PurchaseRequestStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected PurchaseRequest $purchaseRequest;
    protected string $status;
    protected ?string $remarks;

    public function __construct(PurchaseRequest $purchaseRequest, string $status, ?string $remarks = null)
    {
        $this->purchaseRequest = $purchaseRequest;
        $this->status = $status;
        $this->remarks = $remarks;
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
            'pending' => 'menunggu persetujuan',
            default => $this->status,
        };

        $senderName = auth()->user()->name ?? 'Seseorang';
        $receiverName = $notifiable->name ?: 'Anda';

        return [
            'type' => 'purchase_request_status',
            'title' => 'Status Purchase Request',
            'message' => "{$senderName} mengubah status PR #{$this->purchaseRequest->pr_number} menjadi {$statusText}",
            'pr_id' => $this->purchaseRequest->id,
            'pr_code' => $this->purchaseRequest->pr_number,
            'project_id' => $this->purchaseRequest->project_id,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'url' => route('projects.pr.show', [
                'project' => $this->purchaseRequest->project_id,
                'pr' => $this->purchaseRequest->id,
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
            'rejected' => 'ditolak',
            default => $this->status,
        };

        $senderName = auth()->user()->name ?? 'Seseorang';
        $receiverName = $notifiable->name ?: 'Anda';

        return [
            'title' => 'Status Purchase Request',
            'body' => "{$senderName} mengubah status PR #{$this->purchaseRequest->pr_number} menjadi {$statusText}",
            'data' => [
                'type' => 'purchase_request_status',
                'pr_id' => (string) $this->purchaseRequest->id,
                'project_id' => (string) $this->purchaseRequest->project_id,
                'url' => route('projects.pr.show', [
                    'project' => $this->purchaseRequest->project_id,
                    'pr' => $this->purchaseRequest->id,
                ]),
            ],
        ];
    }
}
