<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery' => 'nullable|date|after_or_equal:order_date',
            'payment_terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.purchase_request_item_id' => 'nullable|exists:purchase_request_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'purchase_request_ids' => 'nullable|array',
            'purchase_request_ids.*' => 'exists:purchase_requests,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'project_id' => 'Proyek',
            'supplier_id' => 'Supplier',
            'order_date' => 'Tanggal Order',
            'items' => 'Daftar Barang',
            'items.*.quantity' => 'Jumlah Barang',
            'items.*.unit_price' => 'Harga Satuan',
        ];
    }
}
