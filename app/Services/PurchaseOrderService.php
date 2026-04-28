<?php

namespace App\Services;

use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;
use App\Notifications\PurchaseOrderCreatedNotification;
use Illuminate\Support\Facades\Notification;
use App\Services\ApprovalService;

class PurchaseOrderService
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
     * Create a new Purchase Order from validated request data.
     *
     * @param array $data
     * @param Project $project
     * @param int $creatorId
     * @return PurchaseOrder
     */
    public function createPurchaseOrder(array $data, Project $project, int $creatorId): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $project, $creatorId) {
            // Calculate totals
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $tax = $data['tax_amount'] ?? 0;
            $discount = $data['discount_amount'] ?? 0;
            $total = $subtotal + $tax - $discount;

            // Create PO
            $po = PurchaseOrder::create([
                'project_id' => $project->id,
                'supplier_id' => $data['supplier_id'],
                'purchase_request_id' => null, // Deprecated, using pivot table
                'order_date' => $data['order_date'],
                'expected_delivery' => $data['expected_delivery'],
                'status' => 'draft', // Initial status
                'payment_terms' => $data['payment_terms'],
                'notes' => $data['notes'],
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'created_by' => $creatorId,
            ]);

            // Create Items
            foreach ($data['items'] as $item) {
                $poItem = $po->items()->create([
                    'material_id' => $item['material_id'],
                    'purchase_request_item_id' => $item['purchase_request_item_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'notes' => $item['notes'] ?? null,
                ]);

                // Update PR item ordered quantity if linked
                if ($poItem->purchase_request_item_id) {
                    $prItem = \App\Models\PurchaseRequestItem::find($poItem->purchase_request_item_id);
                    if ($prItem) {
                        $prItem->increment('ordered_quantity', $poItem->quantity);
                        
                        // Collect PR IDs if not already provided
                        if (!isset($prIds)) $prIds = [];
                        if (!in_array($prItem->purchase_request_id, $prIds)) {
                            $prIds[] = $prItem->purchase_request_id;
                        }
                    }
                }
            }

            // Initialize Approval Process
            $this->approvalService->submit($po);

            // Sync with multiple PRs via Pivot
            $prIds = array_unique(array_merge($prIds ?? [], $data['purchase_request_ids'] ?? $data['pr_ids'] ?? []));
            if (!empty($prIds)) {
                $po->purchaseRequests()->sync($prIds);
                
                // Mark PRs as in-process (will be completed after PO approval/GR)
                PurchaseRequest::whereIn('id', $prIds)->update(['status' => 'approved']);
            }

            return $po;
        });
    }


}
