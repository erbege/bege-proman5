<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * List all projects.
     * 
     * Get a paginated list of all available projects.
     */
    public function index(Request $request)
    {
        $query = Project::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->select('id', 'code', 'name', 'client_name', 'start_date', 'end_date', 'status', 'contract_value')
            ->latest()
            ->paginate($request->per_page ?? 10);
    }

    /**
     * Get project details.
     * 
     * Get detailed information about a specific project including client and team.
     */
    public function show(Project $project)
    {
        return response()->json([
            'data' => $project->load(['team'])
        ]);
    }

    /**
     * Get project team members.
     * 
     * Get list of team members assigned to this project.
     */
    public function team(Project $project)
    {
        return response()->json([
            'data' => $project->team()->select('users.id', 'users.name', 'users.email')->get()
        ]);
    }

    /**
     * Get project statistics.
     * 
     * Get summary statistics for the project including progress, budget, and schedule.
     */
    public function stats(Project $project)
    {
        $rabItems = $project->rabItems;
        $totalBudget = $rabItems->sum('total_price');
        $weightedProgress = $rabItems->sum(fn($item) => $item->weight_percentage * ($item->actual_progress / 100));

        return response()->json([
            'data' => [
                'id' => $project->id,
                'name' => $project->name,
                'status' => $project->status,
                'contract_value' => $project->contract_value,
                'total_budget' => $totalBudget,
                'overall_progress' => round($weightedProgress, 2),
                'start_date' => $project->start_date?->format('Y-m-d'),
                'end_date' => $project->end_date?->format('Y-m-d'),
                'duration_weeks' => $project->duration_weeks,
                'rab_items_count' => $rabItems->count(),
                'scheduled_items_count' => $rabItems->whereNotNull('planned_start')->count(),
            ]
        ]);
    }
}
