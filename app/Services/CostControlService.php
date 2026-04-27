<?php

namespace App\Services;

use App\Models\Project;
use App\Models\RabItem;
use Illuminate\Support\Collection;

class CostControlService
{
    /**
     * Generate Cost Control Report comparing RAB with Actual Usage
     */
    public function generateReport(Project $project): Collection
    {
        // Get all RAB items with their actual progress and budget
        $rabItems = RabItem::where('project_id', $project->id)
            ->with(['section', 'materialUsages.items'])
            ->get();

        $report = collect();

        foreach ($rabItems as $item) {
            // 1. Calculate Budget (Planned Cost)
            $budgetCost = $item->total_price;
            
            // 2. Calculate Actual Cost from Material Usages
            $actualCost = 0;
            foreach ($item->materialUsages as $usage) {
                // Since we added total_cost to material_usage_items
                $actualCost += $usage->items->sum('total_cost');
            }

            // 3. Calculate Earned Value (Nilai Hasil)
            // Earned Value = Budget * Actual Progress %
            $earnedValue = $budgetCost * ($item->actual_progress / 100);

            // 4. Calculate Deviations
            // Cost Variance (CV) = Earned Value - Actual Cost
            // Positive is good (Under budget), Negative is bad (Over budget)
            $costVariance = $earnedValue - $actualCost;

            $report->push([
                'rab_id' => $item->id,
                'code' => $item->full_code,
                'work_name' => $item->work_name,
                'budget_cost' => $budgetCost,
                'actual_cost' => $actualCost,
                'actual_progress' => $item->actual_progress,
                'earned_value' => $earnedValue,
                'cost_variance' => $costVariance,
                'status' => $costVariance >= 0 ? 'Under Budget' : 'Over Budget',
            ]);
        }

        return $report;
    }

    /**
     * Get Project Level Financial Summary (Project Metrics)
     */
    public function getProjectFinancialSummary(Project $project): array
    {
        $report = $this->generateReport($project);

        $totalBudget = $report->sum('budget_cost');
        $totalActual = $report->sum('actual_cost');
        $totalEarned = $report->sum('earned_value');
        $totalVariance = $report->sum('cost_variance');
        $cpi = $totalActual > 0 ? ($totalEarned / $totalActual) : 1;

        // EAC (Estimate at Completion) = Budget / CPI
        $eac = $cpi > 0 ? ($totalBudget / $cpi) : $totalBudget;
        
        // ETC (Estimate to Complete) = EAC - Actual Cost
        $etc = $eac - $totalActual;

        return [
            'total_budget' => $totalBudget,
            'actual_cost' => $totalActual,
            'earned_value' => $totalEarned,
            'cost_variance' => $totalVariance,
            'health_status' => $totalVariance >= 0 ? 'Sehat' : 'Rugi / Over Budget',
            'cpi' => round($cpi, 2),
            'eac' => $eac,
            'etc' => $etc,
            'forecast_status' => $eac <= $totalBudget ? 'Aman' : 'Bahaya (Akan Melebihi Budget)',
        ];
    }
}
