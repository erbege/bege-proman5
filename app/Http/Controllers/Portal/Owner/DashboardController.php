<?php

namespace App\Http\Controllers\Portal\Owner;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\WeeklyReport;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Projects explicitly assigned to this owner via owner_id
        $projects = Project::where('owner_id', $user->id)
            ->with(['schedules' => function($q) {
                $q->orderBy('week_number', 'desc');
            }])->get();

        $stats = [
            'total_projects' => $projects->count(),
            'active_projects' => $projects->where('status', 'active')->count(),
            'avg_progress' => $projects->avg(function($p) {
                return $p->schedules->first()?->actual_cumulative ?? 0;
            }) ?? 0,
        ];

        // Recent Published Weekly Reports
        $recentReports = WeeklyReport::whereIn('project_id', $projects->pluck('id'))
            ->where('status', 'published')
            ->with(['project', 'creator'])
            ->latest()
            ->take(5)
            ->get();

        // Recent Interactions (Comments on their projects)
        $recentComments = Comment::whereHasMorph('commentable', [WeeklyReport::class], function($q) use ($projects) {
                $q->whereIn('project_id', $projects->pluck('id'));
            })
            ->with(['user', 'commentable.project'])
            ->latest()
            ->take(5)
            ->get();

        return view('portal.owner.dashboard', compact('projects', 'stats', 'recentReports', 'recentComments'));
    }
}
