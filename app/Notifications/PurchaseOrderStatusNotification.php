<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected PurchaseOrder $po;
    protected string $status;

    /**
     * Create a new notification instance.
     */
    public function __construct(PurchaseOrder $po, string $status)
    {
        $this->po = $po;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', FcmChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $statusText = match ($this->status) {
            'approved' => 'disetujui sepenuhnya',
            'level_approved' => 'disetujui di satu level',
            'rejected' => 'ditolak',
            default => $this->status,
        };
        
        return [
            'type' => 'purchase_order_status',
            'title' => 'Status Purchase Order Diperbarui',
            'message' => "Purchase Order #{$this->po->po_number} telah {$statusText}.",
            'po_id' => $this->po->id,
            'status' => $this->status,
            'project_name' => $this->po->project->name,
            'url' => route('projects.po.show', ['project' => $this->po->project_id, 'po' => $this->po->id]),
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
            'approved' => 'disetujui sepenuhnya',
            'level_approved' => 'disetujui di satu level',
            'rejected' => 'ditolak',
            default => $this->status,
        };

        return [
            'title' => 'Status PO Diperbarui',
            'body' => "PO #{$this->po->po_number} telah {$statusText}.",
            'data' => [
                'type' => 'purchase_order_status',
                'po_id' => (string) $this->po->id,
                'project_id' => (string) $this->po->project_id,
            ],
        ];
    }
}
