<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $canViewFinancials = auth()->user()?->can('financials.view') ?? false;

        return [
            'id' => $this->id,
            'pr_number' => $this->pr_number,
            'project_id' => $this->project_id,
            'project_name' => $this->project?->name,
            'requested_by_id' => $this->requested_by,
            'requested_by_name' => $this->requestedBy?->name,
            'request_date' => $this->request_date?->format('Y-m-d'),
            'required_date' => $this->required_date?->format('Y-m-d'),
            'status' => $this->status,
            'priority' => $this->priority,
            'notes' => $this->notes,
            'rejection_reason' => $this->rejection_reason,
            'total_estimated_price' => $canViewFinancials ? (float) $this->total_estimated_price : 0,
            'items' => $this->whenLoaded('items', function () use ($canViewFinancials) {
                return $this->items->map(fn($item) => [
                    'id' => $item->id,
                    'material_id' => $item->material_id,
                    'material_name' => $item->material?->name,
                    'material_request_item_id' => $item->material_request_item_id,
                    'quantity' => (float) $item->quantity,
                    'unit' => $item->material?->unit,
                    'estimated_price' => $canViewFinancials ? (float) $item->estimated_price : 0,
                    'notes' => $item->notes,
                ]);
            }),
            'approval_logs' => $this->whenLoaded('approvalLogs'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
