<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProgressReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('progress.create');
    }

    public function rules(): array
    {
        return [
            'rab_item_id' => ['nullable', 'integer', 'exists:rab_items,id'],
            'report_date' => ['required', 'date'],
            'progress_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'cumulative_progress' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'issues' => ['nullable', 'string', 'max:5000'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'max:5120'],
            'weather' => ['nullable', Rule::in(['sunny', 'cloudy', 'rainy', 'stormy'])],
            'weather_duration' => ['nullable', 'string', 'max:100'],
            'workers_count' => ['nullable', 'integer', 'min:0'],
            'labor_details' => ['nullable', 'array'],
            'labor_details.*.category' => ['required_with:labor_details', 'string'],
            'labor_details.*.count' => ['required_with:labor_details', 'integer', 'min:0'],
            'equipment_details' => ['nullable', 'array'],
            'equipment_details.*.name' => ['required_with:equipment_details', 'string'],
            'equipment_details.*.qty' => ['required_with:equipment_details', 'integer', 'min:1'],
            'equipment_details.*.condition' => ['nullable', 'string'],
            'equipment_details.*.hours' => ['nullable', 'numeric', 'min:0'],
            'material_usage_summary' => ['nullable', 'array'],
            'material_usage_summary.*.material' => ['required_with:material_usage_summary', 'string'],
            'material_usage_summary.*.qty_used' => ['required_with:material_usage_summary', 'numeric', 'min:0'],
            'material_usage_summary.*.unit' => ['nullable', 'string'],
            'safety_details' => ['nullable', 'array'],
            'safety_details.incidents' => ['nullable', 'integer', 'min:0'],
            'safety_details.near_miss' => ['nullable', 'integer', 'min:0'],
            'safety_details.apd_compliance' => ['nullable', 'boolean'],
            'safety_details.notes' => ['nullable', 'string'],
            'next_day_plan' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'progress_percentage.max' => 'Persentase progress tidak boleh lebih dari 100.',
            'photos.max' => 'Maksimal 5 foto dokumentasi.',
            'photos.*.max' => 'Ukuran tiap foto maksimal 5MB.',
        ];
    }
}
