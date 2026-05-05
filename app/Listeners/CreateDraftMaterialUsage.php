<?php

namespace App\Listeners;

use App\Events\ProgressReportApproved;
use App\Models\MaterialUsage;
use App\Models\MaterialUsageItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class CreateDraftMaterialUsage
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProgressReportApproved $event): void
    {
        $report = $event->report;

        // Skip if no materials reported
        $materials = $report->progressReportMaterials;
        if (!$materials || $materials->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($report, $materials) {
            $usage = MaterialUsage::create([
                'project_id' => $report->project_id,
                'rab_item_id' => $report->rab_item_id,
                'usage_date' => $report->report_date,
                'notes' => 'Otomatis dibuat dari Progress Report ' . $report->report_code,
                'created_by' => $report->reported_by,
            ]);

            foreach ($materials as $item) {
                // Get current material price (fallback to 0 if not available)
                $unitCost = $item->material ? ($item->material->latest_price ?? 0) : 0;

                MaterialUsageItem::create([
                    'material_usage_id' => $usage->id,
                    'material_id' => $item->material_id,
                    'material_name' => $item->material_name,
                    'quantity' => $item->quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $unitCost * $item->quantity,
                    'notes' => $item->notes,
                ]);
            }
        });
    }
}
