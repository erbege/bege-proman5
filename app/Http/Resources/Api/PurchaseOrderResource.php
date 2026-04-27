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
            'subtotal' => (float) $this->subtotal,
            'tax_amount' => (float) $this->tax_amount,
            'discount_amount' => (float) $this->discount_amount,
            'total_amount' => (float) $this->total_amount,
            'payment_terms' => $this->payment_terms,
            'notes' => $this->notes,
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(fn($item) => [
                    'id' => $item->id,
                    'material_id' => $item->material_id,
                    'material_name' => $item->material?->name,
                    'quantity' => (float) $item->quantity,
                    'unit' => $item->material?->unit ?? '-',
                    'unit_price' => (float) $item->unit_price,
                    'total_price' => (float) $item->total_price,
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
