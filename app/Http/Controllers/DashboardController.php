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

        // Cache dashboard stats for 5 minutes (300 seconds)
        $stats = Cache::remember('dashboard_stats', 300, function () {
            return [
                'totalProjects' => Project::count(),
                'activeProjects' => Project::where('status', 'active')->count(),
                'completedProjects' => Project::where('status', 'completed')->count(),
                'onHoldProjects' => Project::where('status', 'on_hold')->count(),
                'planningProjects' => Project::where('status', 'planning')->count(),
                'pendingMR' => MaterialRequest::where('status', 'pending')->count(),
                'pendingPR' => PurchaseRequest::where('status', 'pending')->count(),
                'pendingPO' => PurchaseOrder::where('status', 'pending')->count(),
                'outOfStockCount' => Inventory::where('quantity', '<=', 0)->count(),
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
        $projectProgress = Cache::remember('dashboard_project_progress', 120, function () {
            $activeProjectsData = Project::where('status', 'active')
                ->with('schedules')
                ->get();

            return $activeProjectsData->map(function ($project) {
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

        // Recent Progress Reports (no cache - needs to be real-time)
        $recentReports = ProgressReport::with(['rabItem', 'project'])
            ->latest('report_date')
            ->limit(5)
            ->get();

        // Projects with Issues (cache for 2 minutes)
        $projectsWithIssues = Cache::remember('dashboard_projects_issues', 120, function () {
            return Project::where('status', 'active')
                ->whereHas('schedules', function ($query) {
                    $query->where('deviation', '<', -5);
                })
                ->with('schedules')
                ->limit(5)
                ->get()
                ->map(function ($project) {
                    $latestSchedule = $project->schedules->sortByDesc('week_number')->first();
                    return [
                        'id' => $project->id,
                        'name' => $project->name,
                        'deviation' => $latestSchedule ? round($latestSchedule->deviation, 1) : 0,
                    ];
                });
        });

        // Low Stock Alerts (cache for 1 minute)
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
        $recentProjects = Project::with('creator')
            ->latest()
            ->take(5)
            ->get();

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
        Cache::forget('dashboard_stats');
        Cache::forget('dashboard_project_progress');
        Cache::forget('dashboard_projects_issues');
        Cache::forget('dashboard_low_stock');
    }
}
