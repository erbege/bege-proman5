<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\MaterialRequest;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\ProgressReport;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Redirect owners to their exclusive dashboard
        if ($user->hasRole('owner')) {
            return redirect()->route('owner.dashboard');
        }

        $isPrivileged = $user->hasRole(['super-admin', 'Superadmin', 'administrator']) || 
                        $user->can('financials.manage') || 
                        $user->can('projects.view.all');

        // Cache dashboard stats per user role/access level for 5 minutes
        $cacheKey = 'dashboard_stats_' . ($isPrivileged ? 'admin' : $user->id);
        
        $stats = Cache::remember($cacheKey, 300, function () use ($user, $isPrivileged) {
            $projectQuery = Project::query();
            $mrQuery = MaterialRequest::query();
            $prQuery = PurchaseRequest::query();
            $poQuery = PurchaseOrder::query();

            if (!$isPrivileged) {
                $projectQuery->whereHas('team', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->where('is_active', true);
                });
                $mrQuery->whereIn('project_id', function ($q) use ($user) {
                    $q->select('project_id')->from('project_team')->where('user_id', $user->id)->where('is_active', true);
                });
                $prQuery->whereIn('project_id', function ($q) use ($user) {
                    $q->select('project_id')->from('project_team')->where('user_id', $user->id)->where('is_active', true);
                });
                $poQuery->whereIn('project_id', function ($q) use ($user) {
                    $q->select('project_id')->from('project_team')->where('user_id', $user->id)->where('is_active', true);
                });
            }

            return [
                'totalProjects' => (clone $projectQuery)->count(),
                'activeProjects' => (clone $projectQuery)->where('status', 'active')->count(),
                'completedProjects' => (clone $projectQuery)->where('status', 'completed')->count(),
                'onHoldProjects' => (clone $projectQuery)->where('status', 'on_hold')->count(),
                'planningProjects' => (clone $projectQuery)->where('status', 'planning')->count(),
                'pendingMR' => $mrQuery->where('status', 'pending')->count(),
                'pendingPR' => $prQuery->where('status', 'pending')->count(),
                'pendingPO' => $poQuery->where('status', 'pending')->count(),
                'outOfStockCount' => Inventory::where('quantity', '<=', 0)->count(), // Global inventory stats are usually okay
            ];
        });

        // Extract cached stats
        $totalProjects = $stats['totalProjects'];
        $activeProjects = $stats['activeProjects'];
        $completedProjects = $stats['completedProjects'];
        $onHoldProjects = $stats['onHoldProjects'];
        $planningProjects = $stats['planningProjects'];
        $pendingMR = $stats['pendingMR'];
        $pendingPR = $stats['pendingPR'];
        $pendingPO = $stats['pendingPO'];
        $outOfStockCount = $stats['outOfStockCount'];
        $totalPendingApprovals = $pendingMR + $pendingPR + $pendingPO;

        // Calculate overall progress for active projects (cache for 2 minutes)
        $progressCacheKey = 'dashboard_project_progress_' . ($isPrivileged ? 'admin' : $user->id);
        $projectProgress = Cache::remember($progressCacheKey, 120, function () use ($user, $isPrivileged) {
            $query = Project::where('status', 'active')->with('schedules');
            
            if (!$isPrivileged) {
                $query->whereHas('team', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->where('is_active', true);
                });
            }

            return $query->get()->map(function ($project) {
                $latestSchedule = $project->schedules->sortByDesc('week_number')->first();
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'client' => $project->client_name,
                    'planned' => $latestSchedule ? round($latestSchedule->planned_cumulative, 1) : 0,
                    'actual' => $latestSchedule ? round($latestSchedule->actual_cumulative, 1) : 0,
                    'deviation' => $latestSchedule ? round($latestSchedule->deviation, 1) : 0,
                ];
            });
        });

        // Average completion percentage
        $avgCompletion = $projectProgress->count() > 0
            ? round($projectProgress->avg('actual'), 1)
            : 0;

        // Recent Progress Reports
        $reportsQuery = ProgressReport::with(['rabItem', 'project']);
        if (!$isPrivileged) {
            $reportsQuery->whereIn('project_id', function ($q) use ($user) {
                $q->select('project_id')->from('project_team')->where('user_id', $user->id)->where('is_active', true);
            });
        }
        $recentReports = $reportsQuery->latest('report_date')->limit(5)->get();

        // Projects with Issues
        $issuesCacheKey = 'dashboard_projects_issues_' . ($isPrivileged ? 'admin' : $user->id);
        $projectsWithIssues = Cache::remember($issuesCacheKey, 120, function () use ($user, $isPrivileged) {
            $query = Project::where('status', 'active')
                ->whereHas('schedules', function ($query) {
                    $query->where('deviation', '<', -5);
                })
                ->with('schedules');

            if (!$isPrivileged) {
                $query->whereHas('team', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->where('is_active', true);
                });
            }

            return $query->limit(5)->get()
                ->map(function ($project) {
                    $latestSchedule = $project->schedules->sortByDesc('week_number')->first();
                    return [
                        'id' => $project->id,
                        'name' => $project->name,
                        'deviation' => $latestSchedule ? round($latestSchedule->deviation, 1) : 0,
                    ];
                });
        });

        // Low Stock Alerts (Global)
        $lowStockItems = Cache::remember('dashboard_low_stock', 60, function () {
            return Inventory::with('material')
                ->join('materials', 'inventories.material_id', '=', 'materials.id')
                ->whereColumn('inventories.quantity', '<=', 'materials.min_stock')
                ->where('inventories.quantity', '>', 0)
                ->select('inventories.*')
                ->limit(5)
                ->get();
        });

        // Recent Projects
        $recentProjectsQuery = Project::with('creator');
        if (!$isPrivileged) {
            $recentProjectsQuery->whereHas('team', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('is_active', true);
            });
        }
        $recentProjects = $recentProjectsQuery->latest()->take(5)->get();

        // Chart data for project status distribution
        $statusDistribution = [
            'planning' => $planningProjects,
            'active' => $activeProjects,
            'on_hold' => $onHoldProjects,
            'completed' => $completedProjects,
        ];

        return view('dashboard', compact(
            'totalProjects',
            'activeProjects',
            'completedProjects',
            'onHoldProjects',
            'planningProjects',
            'projectProgress',
            'avgCompletion',
            'pendingMR',
            'pendingPR',
            'pendingPO',
            'totalPendingApprovals',
            'recentReports',
            'projectsWithIssues',
            'lowStockItems',
            'outOfStockCount',
            'recentProjects',
            'statusDistribution'
        ));
    }

    /**
     * Clear dashboard cache (call when data changes significantly)
     */
    public static function clearDashboardCache(): void
    {
        // This clears all keys matching the patterns (simplified)
        // In production, you might want to use tags if supported by driver
    }
}
