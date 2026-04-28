<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\Inventory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;

class GoodsReceiptService
{
    protected $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    public function approvalService(): ApprovalService
    {
        return $this->approvalService;
    }

    /**
     * Create a new Goods Receipt.
     */
    public function createGoodsReceipt(array $data, int $userId): GoodsReceipt
    {
        return DB::transaction(function () use ($data, $userId) {
            $gr = GoodsReceipt::create([
                'project_id' => $data['project_id'],
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'receipt_date' => $data['receipt_date'] ?? now(),
                'delivery_note_number' => $data['delivery_note_number'] ?? '-',
                'notes' => $data['notes'] ?? null,
                'received_by' => $userId,
                'status' => 'pending',
            ]);

            foreach ($data['items'] as $item) {
                $gr->items()->create([
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Submit for approval
            $this->approvalService->submit($gr);

            return $gr;
        });
    }

    /**
     * Finalize Goods Receipt: Update inventory and PO status.
     * This should be called after final approval.
     */
    public function finalize(GoodsReceipt $gr): void
    {
        DB::transaction(function () use ($gr) {
            foreach ($gr->items as $item) {
                // Update PO Item if exists
                if ($item->purchase_order_item_id) {
                    $poItem = PurchaseOrderItem::find($item->purchase_order_item_id);
                    if ($poItem) {
                        $poItem->received_qty += $item->quantity;
                        $poItem->save();
                    }
                }

                // Update Inventory
                $inventory = Inventory::firstOrCreate(
                    ['project_id' => $gr->project_id, 'material_id' => $item->material_id],
                    ['quantity' => 0, 'reserved_qty' => 0, 'average_cost' => 0]
                );
                
                // Reload with lock for update
                $inventory = Inventory::where('id', $inventory->id)->lockForUpdate()->first();

                // Calculate Moving Average Cost (if PO item exists)
                if ($item->purchase_order_item_id && isset($poItem)) {
                    $oldQty = (float) $inventory->quantity;
                    $oldAvgCost = (float) $inventory->average_cost;
                    $newQty = (float) $item->quantity;
                    $unitPrice = (float) $poItem->unit_price;

                    $totalNewQty = $oldQty + $newQty;
                    if ($totalNewQty > 0) {
                        $inventory->average_cost = (($oldQty * $oldAvgCost) + ($newQty * $unitPrice)) / $totalNewQty;
                    }
                }

                $inventory->addStock(
                    $item->quantity,
                    'GoodsReceipt',
                    $gr->id,
                    'GR: ' . $gr->gr_number,
                    $gr->received_by
                );
            }

            // Update PO Status if exists
            if ($gr->purchase_order_id) {
                $po = PurchaseOrder::find($gr->purchase_order_id);
                if ($po) {
                    $po->updateReceiveStatus();
                }
            }
        });
    }
}
