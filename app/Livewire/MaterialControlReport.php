<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\RabItem;
use App\Models\MaterialForecast;
use App\Models\MaterialUsageItem;
use Livewire\Component;

class MaterialControlReport extends Component
{
    public Project $project;
    public $search = '';

    public function mount(Project $project)
    {
        $this->project = $project;
    }

    public function getData()
    {
        // Get all material forecasts for the project
        $forecasts = MaterialForecast::query()
            ->join('rab_items', 'material_forecasts.rab_item_id', '=', 'rab_items.id')
            ->where('rab_items.project_id', $this->project->id)
            ->with(['material', 'rabItem'])
            ->select('material_forecasts.*')
            ->get();

        // Group by material_id to see total needs vs total usage
        $grouped = $forecasts->groupBy('material_id');

        $report = collect();

        foreach ($grouped as $materialId => $items) {
            if (!$materialId) continue;

            $material = $items->first()->material;
            $totalBudgetQty = $items->sum('estimated_qty');
            
            // Get actual usage for this material in this project
            $totalActualQty = MaterialUsageItem::where('material_id', $materialId)
                ->whereHas('materialUsage', function($q) {
                    $q->where('project_id', $this->project->id);
                })
                ->sum('quantity');

            $variance = $totalBudgetQty - $totalActualQty;
            $percentUsed = $totalBudgetQty > 0 ? ($totalActualQty / $totalBudgetQty) * 100 : 0;

            $report->push([
                'material_id' => $materialId,
                'material_name' => $material->name ?? 'Unknown',
                'unit' => $items->first()->unit,
                'budget_qty' => $totalBudgetQty,
                'actual_qty' => $totalActualQty,
                'variance' => $variance,
                'percent_used' => $percentUsed,
                'status' => $variance >= 0 ? 'Aman' : 'Over-Usage',
            ]);
        }

        if ($this->search) {
            $report = $report->filter(function($item) {
                return str_contains(strtolower($item['material_name']), strtolower($this->search));
            });
        }

        return $report;
    }

    public function render()
    {
        return view('livewire.material-control-report', [
            'data' => $this->getData()
        ]);
    }
}
