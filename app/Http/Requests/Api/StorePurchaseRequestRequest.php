<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.material_request_item_id' => 'nullable|exists:material_request_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.estimated_price' => 'nullable|numeric|min:0',
        ];
    }
}
