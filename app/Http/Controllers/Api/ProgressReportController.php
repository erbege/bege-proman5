<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProgressReportRequest;
use App\Http\Requests\UpdateProgressReportRequest;
use App\Models\ProgressReport;
use App\Models\Project;
use App\Services\ProgressReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProgressReportController extends Controller
{
    protected ProgressReportService $service;

    public function __construct(ProgressReportService $service)
    {
        $this->service = $service;
    }

    public function index(Project $project, Request $request): JsonResponse
    {
        return response()->json($project->progressReports()
            ->with('reportedBy:id,name')
            ->latest()
            ->paginate($request->per_page ?? 10));
    }

    public function show(Project $project, ProgressReport $report): JsonResponse
    {
        if (Gate::denies('progress.view')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json([
            'data' => $report->load(['reportedBy', 'rabItem', 'reviewer', 'rejector', 'publisher']),
        ]);
    }

    public function store(StoreProgressReportRequest $request, Project $project): JsonResponse
    {
        if (Gate::denies('progress.create')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $validated = $request->validated();
            $report = $this->service->create($project, $validated, [], auth()->id());
            $this->service->notifyTeam($report, auth()->id());

            return response()->json([
                'message' => 'Progress report created successfully',
                'data' => $report->load(['reportedBy', 'rabItem']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(UpdateProgressReportRequest $request, Project $project, ProgressReport $report): JsonResponse
    {
        if (Gate::denies('progress.update')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $validated = $request->validated();
            $report = $this->service->updateReport($report, $project, $validated);

            return response()->json([
                'message' => 'Progress report updated successfully',
                'data' => $report->load(['reportedBy', 'rabItem']),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Project $project, ProgressReport $report): JsonResponse
    {
        if (Gate::denies('progress.delete')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $this->service->delete($report, $project);

            return response()->json(['message' => 'Progress report deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // ========================
    // Workflow Endpoints
    // ========================

    public function submit(Project $project, ProgressReport $report): JsonResponse
    {
        if (Gate::denies('progress.manage')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $report = $this->service->submit($report);

            return response()->json([
                'message' => 'Progress report submitted successfully',
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Single endpoint for approve/reject.
     * Body: { "action": "approve" | "reject", "notes": "..." }
     */
    public function review(Request $request, Project $project, ProgressReport $report): JsonResponse
    {
        if (Gate::denies('progress.approve')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:2000',
        ]);

        try {
            $reviewerId = auth()->id();

            if ($validated['action'] === 'approve') {
                $report = $this->service->approve($report, $reviewerId, $validated['notes'] ?? null);
                $message = 'Progress report approved successfully';
            } else {
                $report = $this->service->reject($report, $reviewerId, $validated['notes'] ?? null);
                $message = 'Progress report rejected successfully';
            }

            return response()->json([
                'message' => $message,
                'data' => $report->load(['reviewer', 'rejector']),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function publish(Project $project, ProgressReport $report): JsonResponse
    {
        if (Gate::denies('progress.publish')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $report = $this->service->publish($report, auth()->id());

            return response()->json([
                'message' => 'Progress report published successfully',
                'data' => $report,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
