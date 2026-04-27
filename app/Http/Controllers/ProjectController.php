<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Services\ScheduleCalculator;
use App\Traits\GeneratesUniqueCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    use GeneratesUniqueCode;

    public function index()
    {
        $user = Auth::user();
        $query = Project::with('creator');

        // Data Scoping: If not superadmin/privileged, only show projects where user is in the team
        if (!$user->hasRole(['super-admin', 'Superadmin', 'administrator']) && !$user->can('financials.manage')) {
            $query->whereHas('team', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('is_active', true);
            });
        }

        $projects = $query->latest()
            ->paginate(10);

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        if (!Auth::user()->can('financials.manage')) {
            abort(403, 'Unauthorized action.');
        }

        $users = User::all();
        $types = [
            'construction' => 'Konstruksi',
            'architecture' => 'Arsitektur',
            'interior' => 'Interior',
            'exterior' => 'Eksterior',
        ];

        return view('projects.create', compact('users', 'types'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('financials.manage')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:projects,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_name' => 'required|string|max:255',
            'type' => 'required|in:construction,architecture,interior,exterior',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'contract_value' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Auto-generate code if empty
        if (empty($validated['code'])) {
            $validated['code'] = $this->generateUniqueCode(Project::class, 'PRJ');
        }

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'draft';

        // Only allow contract_value if user has permission
        if (!Auth::user()->can('financials.manage')) {
            $validated['contract_value'] = 0;
        }

        $project = Project::create($validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Proyek berhasil dibuat.');
    }

    public function show(Project $project)
    {
        $user = Auth::user();
        
        // Authorization check: User must be in team or be admin/privileged
        if (!$user->hasRole(['super-admin', 'Superadmin', 'administrator']) && 
            !$user->can('financials.manage') && 
            !$project->team()->where('user_id', $user->id)->where('is_active', true)->exists()) {
            abort(403, 'Anda tidak terdaftar dalam tim proyek ini.');
        }

        $project->load(['rabSections.items', 'creator']);

        $scheduleCalculator = new ScheduleCalculator();
        $scurveData = $scheduleCalculator->getScurveData($project);

        // Get Financial Metrics only if authorized
        $financialMetrics = [
            'actual_cost' => 0,
            'earned_value' => 0,
            'cost_variance' => 0,
        ];

        if (Auth::user()->can('financials.view')) {
            $costControlService = new \App\Services\CostControlService();
            $financialMetrics = $costControlService->getProjectFinancialSummary($project);
        }

        return view('projects.show', compact('project', 'scurveData', 'financialMetrics'));
    }

    public function edit(Project $project)
    {
        if (!Auth::user()->can('financials.manage')) {
            abort(403, 'Unauthorized action.');
        }

        $users = User::all();
        $types = [
            'construction' => 'Konstruksi',
            'architecture' => 'Arsitektur',
            'interior' => 'Interior',
            'exterior' => 'Eksterior',
        ];
        $statuses = [
            'draft' => 'Draft',
            'active' => 'Aktif',
            'on_hold' => 'Ditunda',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        return view('projects.edit', compact('project', 'users', 'types', 'statuses'));
    }

    public function update(Request $request, Project $project)
    {
        if (!Auth::user()->can('financials.manage')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_name' => 'required|string|max:255',
            'type' => 'required|in:construction,architecture,interior,exterior',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'contract_value' => 'required|numeric|min:0',
            'status' => 'required|in:draft,active,on_hold,completed,cancelled',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Only allow contract_value update if user has permission
        if (!Auth::user()->can('financials.manage')) {
            unset($validated['contract_value']);
        }

        $project->update($validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Proyek berhasil diperbarui.');
    }

    public function destroy(Project $project)
    {
        if (!Auth::user()->hasRole(['super-admin', 'Superadmin'])) {
            abort(403, 'Hanya Superadmin yang dapat menghapus proyek.');
        }

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Proyek berhasil dihapus.');
    }
}
