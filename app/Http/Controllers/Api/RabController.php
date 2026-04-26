<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class RabController extends Controller
{
    /**
     * Get project RAB (Budget).
     * 
     * Get all RAB sections and items for a project.
     */
    public function index(Project $project)
    {
        return response()->json([
            'data' => [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'contract_value' => $project->contract_value,
                'sections' => $project->rabSections()->with('items')->get(),
                'summary' => [
                    'total_items' => $project->rabItems()->count(),
                    'total_budget' => $project->rabItems()->sum('total_price'),
                    'total_weight' => $project->rabItems()->sum('weight_percentage'),
                ]
            ]
        ]);
    }

    /**
     * Get single RAB item.
     * 
     * Get detailed information about a specific RAB item.
     */
    public function show(Project $project, $itemId)
    {
        $item = $project->rabItems()->with(['section', 'materialForecasts.material'])->findOrFail($itemId);

        return response()->json([
            'data' => $item
        ]);
    }
}
