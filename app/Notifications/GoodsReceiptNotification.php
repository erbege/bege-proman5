<?php

namespace App\Notifications;

use App\Models\GoodsReceipt;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class GoodsReceiptNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected GoodsReceipt $goodsReceipt;

    public function __construct(GoodsReceipt $goodsReceipt)
    {
        $this->goodsReceipt = $goodsReceipt;
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
        $senderName = $this->goodsReceipt->receivedBy->name ?? 'Seseorang';
        $receiverName = $notifiable->name ?: 'Anda';

        return [
            'type' => 'goods_receipt',
            'title' => 'Penerimaan Barang Baru',
            'message' => "{$senderName} menerima barang untuk proyek {$this->goodsReceipt->project->name} (GR #{$this->goodsReceipt->gr_number})",
            'gr_id' => $this->goodsReceipt->id,
            'gr_number' => $this->goodsReceipt->gr_number,
            'project_id' => $this->goodsReceipt->project_id,
            'project_name' => $this->goodsReceipt->project->name,
            'po_code' => $this->goodsReceipt->purchaseOrder?->code,
            'delivery_note' => $this->goodsReceipt->delivery_note_number,
            'received_by' => $this->goodsReceipt->receivedBy?->name,
            'url' => route('projects.inventory.gr.show', [
                'project' => $this->goodsReceipt->project_id,
                'gr' => $this->goodsReceipt->id,
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
        $senderName = $this->goodsReceipt->receivedBy->name ?? 'Seseorang';
        $receiverName = $notifiable->name ?: 'Anda';

        return [
            'title' => 'Penerimaan Barang Baru',
            'body' => "{$senderName} menerima GR #{$this->goodsReceipt->gr_number} di {$this->goodsReceipt->project->name}",
            'data' => [
                'type' => 'goods_receipt',
                'gr_id' => (string) $this->goodsReceipt->id,
                'project_id' => (string) $this->goodsReceipt->project_id,
                'url' => route('projects.inventory.gr.show', [
                    'project' => $this->goodsReceipt->project_id,
                    'gr' => $this->goodsReceipt->id,
                ]),
            ],
        ];
    }
}
