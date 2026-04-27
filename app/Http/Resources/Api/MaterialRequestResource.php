<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialRequestResource extends JsonResource
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
            'code' => $this->code,
            'project_id' => $this->project_id,
            'project_name' => $this->project?->name,
            'requested_by_id' => $this->requested_by,
            'requested_by_name' => $this->requestedBy?->name,
            'request_date' => $this->request_date?->format('Y-m-d'),
            'status' => $this->status,
            'notes' => $this->notes,
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(fn($item) => [
                    'id' => $item->id,
                    'material_id' => $item->material_id,
                    'material_name' => $item->material?->name,
                    'quantity' => (float) $item->quantity,
                    'ordered_quantity' => (float) $item->ordered_quantity,
                    'remaining_to_order' => (float) $item->remaining_to_order,
                    'unit' => $item->unit,
                    'notes' => $item->notes,
                ]);
            }),
            'approval_logs' => $this->whenLoaded('approvalLogs'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
