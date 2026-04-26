<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ScheduleCalculator;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Get project schedule.
     * 
     * Get schedule data for all RAB items in a project.
     */
    public function index(Project $project)
    {
        $items = $project->rabItems()
            ->with('section:id,code,name')
            ->select('id', 'rab_section_id', 'code', 'work_name', 'weight_percentage', 'planned_start', 'planned_end', 'actual_progress')
            ->orderBy('rab_section_id')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => [
                'project' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'start_date' => $project->start_date?->format('Y-m-d'),
                    'end_date' => $project->end_date?->format('Y-m-d'),
                    'duration_weeks' => $project->duration_weeks,
                ],
                'items' => $items,
            ]
        ]);
    }

    /**
     * Get S-Curve data.
     * 
     * Get S-Curve (plan vs actual) data for the project.
     */
    public function scurve(Project $project)
    {
        $calculator = new ScheduleCalculator();
        $scurveData = $calculator->getScurveData($project);

        return response()->json([
            'data' => $scurveData
        ]);
    }
}
