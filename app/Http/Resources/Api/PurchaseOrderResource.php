<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
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
            'po_number' => $this->po_number,
            'project_id' => $this->project_id,
            'project_name' => $this->project?->name,
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->supplier?->name,
            'order_date' => $this->order_date?->format('Y-m-d'),
            'expected_delivery' => $this->expected_delivery?->format('Y-m-d'),
            'status' => $this->status,
            'status_label' => $this->status_label,
            'subtotal' => $canViewFinancials ? (float) $this->subtotal : 0,
            'tax_amount' => $canViewFinancials ? (float) $this->tax_amount : 0,
            'discount_amount' => $canViewFinancials ? (float) $this->discount_amount : 0,
            'total_amount' => $canViewFinancials ? (float) $this->total_amount : 0,
            'payment_terms' => $this->payment_terms,
            'notes' => $this->notes,
            'items' => $this->whenLoaded('items', function () use ($canViewFinancials) {
                return $this->items->map(fn($item) => [
                    'id' => $item->id,
                    'material_id' => $item->material_id,
                    'material_name' => $item->material?->name,
                    'quantity' => (float) $item->quantity,
                    'unit' => $item->material?->unit ?? '-',
                    'unit_price' => $canViewFinancials ? (float) $item->unit_price : 0,
                    'total_price' => $canViewFinancials ? (float) $item->total_price : 0,
                    'notes' => $item->notes,
                ]);
            }),
            'purchase_requests' => $this->whenLoaded('purchaseRequests', function () {
                return $this->purchaseRequests->map(fn($pr) => [
                    'id' => $pr->id,
                    'pr_number' => $pr->pr_number,
                ]);
            }),
            'approval_logs' => $this->whenLoaded('approvalLogs'),
            'created_by' => [
                'id' => $this->created_by,
                'name' => $this->createdBy?->name,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
