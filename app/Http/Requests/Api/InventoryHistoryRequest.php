<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class InventoryHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'nullable|integer|exists:projects,id',
            'material_id' => 'nullable|integer|exists:materials,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
