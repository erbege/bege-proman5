<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

/**
 * @group Project Management
 * 
 * Endpoints for managing construction projects.
 */
class ProjectController extends Controller
{
    use ApiResponse;
    private const ELEVATED_ROLES = ['Superadmin', 'super-admin', 'administrator'];

    /**
     * List all projects.
     * 
     * Get a paginated list of all available projects.
     */
    public function index(Request $request)
    {
        $query = Project::with(['creator']);

        $user = auth()->user();
        if ($user) {
            $this->applyVisibilityScope($query, $user);
        }

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

        $projects = $query->latest()->paginate($request->per_page ?? 10);
        
        return $this->paginatedResponse(
            'Projects retrieved successfully.',
            ProjectResource::collection($projects)
        );
    }

    /**
     * Get project details.
     * 
     * Get detailed information about a specific project including client and team.
     */
    public function show(Project $project)
    {
        $user = auth()->user();
        if (!$user || !$this->canViewProject($project, $user)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse(
            'Project details retrieved successfully.',
            new ProjectResource($project->load(['creator']))
        );
    }

    /**
     * Get project team members.
     * 
     * Get list of team members assigned to this project.
     */
    public function team(Project $project)
    {
        $team = $project->team()->select('users.id', 'users.name', 'users.email')->get();
        
        return $this->successResponse('Project team retrieved successfully.', $team);
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

        return $this->successResponse('Project statistics retrieved successfully.', [
            'id' => $project->id,
            'name' => $project->name,
            'status' => $project->status,
            'contract_value' => (float) $project->contract_value,
            'total_budget' => $totalBudget,
            'overall_progress' => round($weightedProgress, 2),
            'start_date' => $project->start_date?->format('Y-m-d'),
            'end_date' => $project->end_date?->format('Y-m-d'),
            'duration_weeks' => $project->duration_weeks,
            'rab_items_count' => $rabItems->count(),
            'scheduled_items_count' => $rabItems->whereNotNull('planned_start')->count(),
        ]);
    }

    private function applyVisibilityScope($query, $user): void
    {
        if ($user->hasAnyRole(self::ELEVATED_ROLES) || $user->can('projects.view.all')) {
            return;
        }

        $query->whereHas('team', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        });
    }

    private function canViewProject(Project $project, $user): bool
    {
        if ($user->hasAnyRole(self::ELEVATED_ROLES) || $user->can('projects.view.all')) {
            return true;
        }

        if ((int) $project->created_by === (int) $user->id) {
            return true;
        }

        return $project->team()->where('users.id', $user->id)->exists();
    }
}
