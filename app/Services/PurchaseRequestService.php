<?php

namespace App\Services;

use App\Models\PurchaseRequest;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class PurchaseRequestService
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
     * Create a new Purchase Request.
     */
    public function createPurchaseRequest(array $data, int $userId): PurchaseRequest
    {
        return DB::transaction(function () use ($data, $userId) {
            $pr = PurchaseRequest::create([
                'project_id' => $data['project_id'],
                'requested_by' => $userId,
                'request_date' => $data['request_date'] ?? now(),
                'required_date' => $data['required_date'] ?? null,
                'priority' => $data['priority'] ?? 'normal',
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
            ]);

            foreach ($data['items'] as $item) {
                $prItem = $pr->items()->create([
                    'material_id' => $item['material_id'],
                    'material_request_item_id' => $item['material_request_item_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'estimated_price' => $item['estimated_price'] ?? 0,
                    'notes' => $item['notes'] ?? null,
                ]);

                // If linked to MR, update the MR item's ordered quantity
                if ($prItem->material_request_item_id) {
                    $mrItem = \App\Models\MaterialRequestItem::find($prItem->material_request_item_id);
                    if ($mrItem) {
                        $mrItem->increment('ordered_quantity', $prItem->quantity);
                        $this->updateMRStatus($mrItem->material_request_id);
                    }
                }
            }

            // Submit for approval
            $this->approvalService->submit($pr);

            return $pr;
        });
    }

    /**
     * Check if user can create PR for project.
     */
    private function updateMRStatus(int $mrId): void
    {
        $mr = \App\Models\MaterialRequest::with('items')->find($mrId);
        if (!$mr) return;

        $allProcessed = $mr->items->every(fn($item) => $item->remaining_to_order <= 0);

        if ($allProcessed) {
            $mr->update(['status' => 'processed']);
        }
    }

    public function canCreatePR(int $userId, int $projectId): bool
    {
        $project = Project::find($projectId);
        if (!$project) return false;

        $user = \App\Models\User::find($userId);
        if ($user->hasAnyRole(['super-admin', 'Superadmin', 'administrator'])) {
            return true;
        }

        return $project->team()->where('users.id', $userId)->exists();
    }
}
