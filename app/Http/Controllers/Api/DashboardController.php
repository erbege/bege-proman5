<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\MaterialRequest;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     * 
     * Get summary statistics for the dashboard.
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        // Project stats
        $projectsCount = Project::count();
        $activeProjects = Project::where('status', 'active')->count();

        // Pending requests
        $pendingMaterialRequests = MaterialRequest::where('status', 'pending')->count();
        $pendingPurchaseRequests = PurchaseRequest::where('status', 'pending')->count();

        // Recent projects
        $recentProjects = Project::select('id', 'code', 'name', 'client_name', 'status')
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'projects' => [
                    'total' => $projectsCount,
                    'active' => $activeProjects,
                ],
                'pending_requests' => [
                    'material_requests' => $pendingMaterialRequests,
                    'purchase_requests' => $pendingPurchaseRequests,
                ],
                'recent_projects' => $recentProjects,
            ]
        ]);
    }
}
