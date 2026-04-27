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
                $po->items()->create([
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Initialize Approval Process
            $this->approvalService->submit($po);

            // Sync with multiple PRs via Pivot
            $prIds = $data['purchase_request_ids'] ?? $data['pr_ids'] ?? [];
            if (!empty($prIds)) {
                $po->purchaseRequests()->sync($prIds);
                
                // Mark PRs as in-process (will be completed after PO approval/GR)
                PurchaseRequest::whereIn('id', $prIds)->update(['status' => 'approved']);
            }

            // Notify project team members
            $this->notifyTeam($po, $project, $creatorId);

            return $po;
        });
    }

    /**
     * Notify team members about PO creation.
     */
    private function notifyTeam(PurchaseOrder $po, Project $project, int $creatorId): void
    {
        $po->load('supplier');
        $teamMembers = $project->team()
            ->where('users.id', '!=', $creatorId)
            ->get();

        if ($teamMembers->isNotEmpty()) {
            Notification::send(
                $teamMembers,
                new PurchaseOrderCreatedNotification($po)
            );
        }
    }
}
