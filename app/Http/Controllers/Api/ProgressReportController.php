<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProgressReport;
use Illuminate\Http\Request;

class ProgressReportController extends Controller
{
    /**
     * List progress reports for a project.
     * 
     * Get a paginated list of progress reports for a specific project.
     */
    public function index(Project $project, Request $request)
    {
        return $project->progressReports()
            ->with('reportedBy:id,name')
            ->latest()
            ->paginate($request->per_page ?? 10);
    }

    /**
     * Get progress report details.
     * 
     * Get detailed information about a specific progress report.
     */
    public function show(Project $project, ProgressReport $report)
    {
        return response()->json([
            'data' => $report->load(['reportedBy', 'rabItem'])
        ]);
    }

    /**
     * Create new progress report.
     * 
     * Create a new progress report for a project.
     */
    public function store(Project $project, Request $request)
    {
        $validated = $request->validate([
            'rab_item_id' => 'required|exists:rab_items,id',
            'report_date' => 'required|date',
            'progress_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'issues' => 'nullable|string',
            'weather' => 'nullable|in:sunny,cloudy,rainy,stormy',
            'workers_count' => 'nullable|integer|min:0',
        ]);

        $validated['reported_by'] = auth()->id();
        
        if (!empty($validated['rab_item_id'])) {
            $rabItem = \App\Models\RabItem::find($validated['rab_item_id']);
            $validated['cumulative_progress'] = min(100, $rabItem->actual_progress + $validated['progress_percentage']);
        }

        $report = $project->progressReports()->create($validated);

        if ($report->rab_item_id) {
            $report->rabItem->update([
                'actual_progress' => $report->cumulative_progress ?? $report->progress_percentage,
            ]);

            $scheduleCalculator = new \App\Services\ScheduleCalculator();
            $scheduleCalculator->updateFromProgress($project);
        }

        // Notify stakeholders
        $this->notifyStakeholders($report);

        return response()->json([
            'message' => 'Progress report created successfully',
            'data' => $report->load(['reportedBy', 'rabItem'])
        ], 201);
    }

    /**
     * Notify relevant stakeholders about the new progress report.
     */
    protected function notifyStakeholders(ProgressReport $report): void
    {
        $users = collect();
        if ($report->project && $report->project->createdBy) {
            $users->push($report->project->createdBy);
        }

        // Add Project Managers explicitly as they are stakeholders but not necessarily 'admins' in helper
        $projectManagers = \App\Models\User::role('project-manager')->get();
        $users = $users->merge($projectManagers);

        \App\Services\NotificationHelper::sendToUsers(
            $users,
            new \App\Notifications\ProgressReportCreatedNotification($report)
        );
    }
}
