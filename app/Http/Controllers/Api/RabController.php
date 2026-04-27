<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class RabController extends Controller
{
    /**
     * Get project RAB (Budget).
     */
    public function index(Project $project)
    {
        $this->authorize('rab.view');
        $canViewFinancials = auth()->user()->can('financials.view');

        $sections = $project->rabSections()->with(['items' => function($q) use ($canViewFinancials) {
            if (!$canViewFinancials) {
                $q->select('id', 'rab_section_id', 'code', 'work_name', 'volume', 'unit', 'weight_percentage');
            }
        }])->get();

        return response()->json([
            'data' => [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'contract_value' => $canViewFinancials ? $project->contract_value : 0,
                'sections' => $sections,
                'summary' => [
                    'total_items' => $project->rabItems()->count(),
                    'total_budget' => $canViewFinancials ? $project->rabItems()->sum('total_price') : 0,
                    'total_weight' => $project->rabItems()->sum('weight_percentage'),
                ]
            ]
        ]);
    }

    /**
     * Get single RAB item.
     */
    public function show(Project $project, $itemId)
    {
        $this->authorize('rab.view');
        $canViewFinancials = auth()->user()->can('financials.view');

        $item = $project->rabItems()->with(['section', 'materialForecasts.material'])->findOrFail($itemId);

        if (!$canViewFinancials) {
            $item->makeHidden(['unit_price', 'total_price']);
        }

        return response()->json([
            'data' => $item
        ]);
    }
}
