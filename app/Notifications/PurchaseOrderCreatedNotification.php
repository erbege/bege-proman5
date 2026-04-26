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
            'type' => 'purchase_order_created',
            'title' => 'Purchase Order Baru',
            'message' => "PO #{$this->purchaseOrder->code} telah dibuat",
            'po_id' => $this->purchaseOrder->id,
            'po_code' => $this->purchaseOrder->code,
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
     * Get the FCM representation of the notification.
     */
    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'Purchase Order Baru',
            'body' => "PO #{$this->purchaseOrder->code} telah dibuat untuk {$this->purchaseOrder->supplier?->name}",
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
