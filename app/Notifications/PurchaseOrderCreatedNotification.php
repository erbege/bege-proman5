<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PurchaseOrderCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected PurchaseOrder $purchaseOrder;

    public function __construct(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
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
        $senderName = $this->purchaseOrder->createdBy->name ?? 'Seseorang';
        $receiverName = $notifiable->name ?: 'Anda';

        return [
            'type' => 'purchase_order_created',
            'title' => 'Purchase Order Baru',
            'message' => "{$senderName} membuat PO #{$this->purchaseOrder->po_number} untuk {$this->purchaseOrder->supplier?->name}",
            'po_id' => $this->purchaseOrder->id,
            'po_code' => $this->purchaseOrder->po_number,
            'project_id' => $this->purchaseOrder->project_id,
            'supplier_name' => $this->purchaseOrder->supplier?->name,
            'total_amount' => $this->purchaseOrder->total_amount,
            'url' => route('projects.po.show', [
                'project' => $this->purchaseOrder->project_id,
                'po' => $this->purchaseOrder->id,
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
        $senderName = $this->purchaseOrder->createdBy->name ?? 'Seseorang';
        $receiverName = $notifiable->name ?: 'Anda';

        return [
            'title' => 'Purchase Order Baru',
            'body' => "{$senderName} membuat PO #{$this->purchaseOrder->po_number} untuk {$this->purchaseOrder->supplier?->name}",
            'data' => [
                'type' => 'purchase_order_created',
                'po_id' => (string) $this->purchaseOrder->id,
                'project_id' => (string) $this->purchaseOrder->project_id,
                'url' => route('projects.po.show', [
                    'project' => $this->purchaseOrder->project_id,
                    'po' => $this->purchaseOrder->id,
                ]),
            ],
        ];
    }
}
