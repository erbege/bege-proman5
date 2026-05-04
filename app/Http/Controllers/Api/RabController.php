<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class RabController extends Controller
{
    private const ELEVATED_ROLES = ['Superadmin', 'super-admin', 'administrator'];
    /**
     * Get project RAB (Budget).
     */
    public function index(Project $project)
    {
        $user = auth()->user();
        if (!$user || !$this->canViewProject($project, $user)) {
            return response()->json(['message' => 'Unauthorized access to project'], 403);
        }

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
        $user = auth()->user();
        if (!$user || !$this->canViewProject($project, $user)) {
            return response()->json(['message' => 'Unauthorized access to project'], 403);
        }

        $canViewFinancials = auth()->user()->can('financials.view');

        $item = $project->rabItems()->with(['section', 'materialForecasts.material'])->findOrFail($itemId);

        if (!$canViewFinancials) {
            $item->makeHidden(['unit_price', 'total_price']);
        }

        return response()->json([
            'data' => $item
        ]);
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
