<?php

namespace App\Http\Controllers\Api\Portal\Owner;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\WeeklyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $projects = Project::where('owner_id', $user->id)
            ->with(['schedules' => function($q) {
                $q->orderBy('week_number', 'desc');
            }])->get();

        $stats = [
            'total_projects' => $projects->count(),
            'active_projects' => $projects->where('status', 'active')->count(),
            'avg_progress' => round($projects->avg(function($p) {
                return $p->schedules->first()?->actual_cumulative ?? 0;
            }) ?? 0, 2),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'projects' => $projects->map(function($project) {
                    return [
                        'id' => $project->id,
                        'name' => $project->name,
                        'code' => $project->code,
                        'status' => $project->status,
                        'progress' => $project->schedules->first()?->actual_cumulative ?? 0,
                    ];
                }),
            ]
        ]);
    }
}
