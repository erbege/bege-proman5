<?php

namespace App\Http\Controllers\Portal\Owner;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\WeeklyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects for the owner
     */
    public function index(Request $request)
    {
        $query = Project::query()
            ->where('owner_id', Auth::id())
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim((string) $request->string('q'));
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%")
                        ->orWhere('location', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('status') && $request->get('status') !== 'all', function ($q) use ($request) {
                $q->where('status', $request->get('status'));
            });

        $projects = $query
            ->with(['schedules' => function ($q) {
                $q->orderBy('week_number', 'desc');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $filters = [
            'q' => (string) $request->get('q', ''),
            'status' => (string) $request->get('status', 'all'),
        ];

        return view('portal.owner.projects.index', compact('projects', 'filters'));
    }

    /**
     * Display project details for the owner
     */
    public function show(Project $project)
    {
        // Security check
        if ($project->owner_id != Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke proyek ini.');
        }

        $project->load(['schedules' => function($q) {
            $q->orderBy('week_number', 'asc');
        }]);

        $weeklyReports = WeeklyReport::where('project_id', $project->id)
            ->published()
            ->with('creator')
            ->orderBy('week_number', 'desc')
            ->get();

        $monthlyReports = \App\Models\MonthlyReport::where('project_id', $project->id)
            ->published()
            ->with('creator')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Get latest progress stats
        $latestSchedule = $project->schedules->last();
        
        $stats = [
            'actual_progress' => $latestSchedule?->actual_cumulative ?? 0,
            'planned_progress' => $latestSchedule?->planned_cumulative ?? 0,
            'deviation' => ($latestSchedule?->actual_cumulative ?? 0) - ($latestSchedule?->planned_cumulative ?? 0),
        ];

        return view('portal.owner.projects.show', compact('project', 'weeklyReports', 'monthlyReports', 'stats'));
    }
}
