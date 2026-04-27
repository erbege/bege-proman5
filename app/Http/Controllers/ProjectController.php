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
        $projects = Project::with('creator')
            ->latest()
            ->paginate(10);

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
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

        $project = Project::create($validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Proyek berhasil dibuat.');
    }

    public function show(Project $project)
    {
        $project->load(['rabSections.items', 'creator']);

        $scheduleCalculator = new ScheduleCalculator();
        $scurveData = $scheduleCalculator->getScurveData($project);

        // Get Financial Metrics
        $costControlService = new \App\Services\CostControlService();
        $financialMetrics = $costControlService->getProjectFinancialSummary($project);

        return view('projects.show', compact('project', 'scurveData', 'financialMetrics'));
    }

    public function edit(Project $project)
    {
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

        $project->update($validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Proyek berhasil diperbarui.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Proyek berhasil dihapus.');
    }
}
