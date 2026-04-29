<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\WeeklyReportResource;
use App\Models\Project;
use App\Models\WeeklyReport;
use App\Models\SystemSetting;
use App\Services\WeeklyReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @group Project Reports: Weekly
 * @authenticated
 * 
 * Endpoints for generating and managing weekly progress reports.
 */
class WeeklyReportController extends Controller
{
    use ApiResponse;

    protected WeeklyReportService $service;
    private const ELEVATED_ROLES = ['Superadmin', 'super-admin', 'administrator'];

    public function __construct(WeeklyReportService $service)
    {
        $this->service = $service;
    }

    /**
     * Get list of weekly reports for a project
     */
    public function index(Project $project)
    {
        $this->authorize('weekly_report.view');
        $user = auth()->user();
        if (!$user || !$this->canViewProject($project, $user)) {
            return $this->errorResponse('Unauthorized access to project', 403);
        }

        $reports = WeeklyReport::where('project_id', $project->id)
            ->with(['creator', 'coverImage.latestVersion'])
            ->orderByDesc('week_number')
            ->get();

        return $this->successResponse(
            'Weekly reports retrieved successfully.',
            [
                'reports' => WeeklyReportResource::collection($reports),
                'next_week' => $this->service->getNextWeekNumber($project),
                'current_week' => $this->service->getCurrentWeekNumber($project),
            ]
        );
    }

    /**
     * Generate or store a new weekly report draft
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'week_number' => 'required|integer|min:1',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'cover_title' => 'nullable|string|max:255',
            'activities' => 'nullable|string',
            'problems' => 'nullable|string',
        ]);

        $existing = WeeklyReport::where('project_id', $project->id)
            ->where('week_number', $validated['week_number'])
            ->first();

        if ($existing) {
            return $this->errorResponse('Weekly report untuk minggu ini sudah ada.', 422);
        }

        $periodEnd = Carbon::parse($validated['period_end']);
        $periodStart = Carbon::parse($validated['period_start']);

        $cumulativeData = $this->service->generateCumulativeData($project, $periodEnd, $validated['week_number']);
        $detailData = $this->service->generateDetailData($project, $periodStart, $periodEnd);

        $report = WeeklyReport::create([
            'project_id' => $project->id,
            'week_number' => $validated['week_number'],
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'cover_title' => $validated['cover_title'] ?? "Weekly Progress Report - Week {$validated['week_number']}",
            'cumulative_data' => $cumulativeData,
            'detail_data' => $detailData,
            'activities' => $validated['activities'] ?? null,
            'problems' => $validated['problems'] ?? null,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return $this->successResponse(
            'Weekly report berhasil dibuat.',
            new WeeklyReportResource($report),
            201
        );
    }

    /**
     * Auto generate weekly report for current or specified week
     */
    public function autoGenerate(Request $request, Project $project)
    {
        $weekNumber = $request->input('week_number');

        $report = $this->service->autoGenerate($project, $weekNumber);

        return $this->successResponse(
            "Weekly report Week {$report->week_number} berhasil di-generate.",
            new WeeklyReportResource($report)
        );
    }

    /**
     * Show detailed weekly report data
     */
    public function show(Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.view');
        $user = auth()->user();
        if (!$user || !$this->canViewProject($project, $user)) {
            return $this->errorResponse('Unauthorized access to project', 403);
        }

        if (!$this->belongsToProject($project, $report)) {
            return $this->errorResponse('Weekly report not found for this project.', 404);
        }

        $report->load(['coverImage.latestVersion', 'creator', 'comments.user']);

        return $this->successResponse(
            'Weekly report detail retrieved.',
            new WeeklyReportResource($report)
        );
    }

    /**
     * Update Cover Section
     */
    public function updateCover(Request $request, Project $project, WeeklyReport $report)
    {
        if (!$this->belongsToProject($project, $report)) {
            return $this->errorResponse('Weekly report not found for this project.', 404);
        }

        $validated = $request->validate([
            'cover_title' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,published',
            'cover_image_id' => 'nullable|exists:project_files,id',
            'cover_image_upload' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('cover_image_upload')) {
            if ($report->cover_image_path) {
                $disk = SystemSetting::getStorageDisk();
                Storage::disk($disk)->delete($report->cover_image_path);
            }

            $disk = SystemSetting::getStorageDisk();
            $imageResizer = new \App\Services\ImageResizeService();
            $validated['cover_image_path'] = $imageResizer->processAndSave(
                $request->file('cover_image_upload'),
                "weekly-reports/{$project->id}",
                $disk
            );
            $validated['cover_image_id'] = null; // Clear if uploading manual
        }

        $report->update($validated);

        return $this->successResponse(
            'Cover berhasil diperbarui.',
            new WeeklyReportResource($report->fresh(['coverImage.latestVersion']))
        );
    }

    /**
     * Update Cumulative data inline (Actual Progress)
     */
    public function updateCumulative(Request $request, Project $project, WeeklyReport $report)
    {
        if (!$this->belongsToProject($project, $report)) {
            return $this->errorResponse('Weekly report not found for this project.', 404);
        }

        $request->validate([
            'items' => 'required|array',
            'items.*' => 'numeric|min:0',
        ]);

        $itemUpdates = $request->input('items');
        
        $cascadeCount = $this->service->updateCumulativeActuals($report, $itemUpdates);

        $message = 'Data realisasi berhasil disimpan.';
        if ($cascadeCount > 0) {
            $message .= " ({$cascadeCount} minggu berikutnya juga diperbarui)";
        }

        return $this->successResponse($message, [
            'cumulative_data' => $report->fresh()->cumulative_data,
            'cascaded_weeks' => $cascadeCount,
        ]);
    }

    /**
     * Update Detailed Data (Usually manual edit override of progress item records)
     */
    public function updateDetail(Request $request, Project $project, WeeklyReport $report)
    {
        if (!$this->belongsToProject($project, $report)) {
            return $this->errorResponse('Weekly report not found for this project.', 404);
        }

        $request->validate([
            'detail_data' => 'required|array',
        ]);

        $report->update(['detail_data' => $request->input('detail_data')]);

        return $this->successResponse(
            'Detail progress berhasil diperbarui.',
            new WeeklyReportResource($report->fresh())
        );
    }

    /**
     * Upload documentation images
     */
    public function uploadDocumentation(Request $request, Project $project, WeeklyReport $report)
    {
        if (!$this->belongsToProject($project, $report)) {
            return $this->errorResponse('Weekly report not found for this project.', 404);
        }

        $request->validate([
            'photos' => 'required|array|min:1',
            'photos.*' => 'image|max:5120',
        ]);

        $disk = SystemSetting::getStorageDisk();
        $imageResizer = new \App\Services\ImageResizeService();
        $uploads = $report->documentation_uploads ?? [];

        $newPhotos = [];
        foreach ($request->file('photos') as $file) {
            $path = $imageResizer->processAndSave(
                $file,
                "weekly-reports/{$project->id}/docs",
                $disk
            );
            $uploads[] = $path;
            
            $newPhotos[] = [
                'path' => $path,
                'url' => SystemSetting::getFileUrl($path),
                'name' => $file->getClientOriginalName(),
                'id' => 'upload_' . (count($uploads) - 1),
                'source' => 'upload',
            ];
        }

        $report->update(['documentation_uploads' => $uploads]);

        return $this->successResponse(count($newPhotos) . ' foto berhasil diupload.', $newPhotos);
    }

    /**
     * Add existing progress photos to documentations
     */
    public function addProgressPhotos(Request $request, Project $project, WeeklyReport $report)
    {
        if (!$this->belongsToProject($project, $report)) {
            return $this->errorResponse('Weekly report not found for this project.', 404);
        }

        $request->validate([
            'photo_paths' => 'required|array|min:1',
            'photo_paths.*' => 'string',
        ]);

        $uploads = $report->documentation_uploads ?? [];
        $added = [];

        foreach ($request->input('photo_paths') as $path) {
            if (!in_array($path, $uploads)) {
                $uploads[] = $path;
                $added[] = [
                    'path' => $path,
                    'url' => SystemSetting::getFileUrl($path),
                    'name' => basename($path),
                    'id' => 'upload_' . (count($uploads) - 1),
                    'source' => 'upload',
                ];
            }
        }

        $report->update(['documentation_uploads' => $uploads]);

        return $this->successResponse(count($added) . ' foto dari progress report ditambahkan.', $added);
    }

    /**
     * Remove single documentation photo
     */
    public function removeDocumentation(Request $request, Project $project, WeeklyReport $report)
    {
        if (!$this->belongsToProject($project, $report)) {
            return $this->errorResponse('Weekly report not found for this project.', 404);
        }

        $request->validate([
            'type' => 'required|in:project_file,upload',
            'id' => 'required_if:type,project_file',
            'path' => 'required_if:type,upload|string',
        ]);

        if ($request->input('type') === 'project_file') {
            $ids = $report->documentation_ids ?? [];
            $ids = array_values(array_filter($ids, fn($id) => $id != $request->input('id')));
            $report->update(['documentation_ids' => $ids]);
        } else {
            $uploads = $report->documentation_uploads ?? [];
            $path = $request->input('path');
            $uploads = array_values(array_filter($uploads, fn($p) => $p !== $path));
            $report->update(['documentation_uploads' => $uploads]);
        }

        return $this->successResponse('Foto berhasil dihapus dari dokumentasi.');
    }

    /**
     * Update activities and problems
     */
    public function updateActivities(Request $request, Project $project, WeeklyReport $report)
    {
        if (!$this->belongsToProject($project, $report)) {
            return $this->errorResponse('Weekly report not found for this project.', 404);
        }

        $request->validate([
            'activities' => 'nullable|string',
            'problems' => 'nullable|string',
        ]);

        $report->update([
            'activities' => $request->input('activities'),
            'problems' => $request->input('problems'),
        ]);

        return $this->successResponse('Aktivitas dan kendala berhasil diperbarui.', [
            'activities' => $report->activities,
            'problems' => $report->problems,
        ]);
    }

    /**
     * Delete a weekly report
     */
    public function destroy(Project $project, WeeklyReport $report)
    {
        if (!$this->belongsToProject($project, $report)) {
            return $this->errorResponse('Weekly report not found for this project.', 404);
        }

        if ($report->cover_image_path) {
            $disk = SystemSetting::getStorageDisk();
            Storage::disk($disk)->delete($report->cover_image_path);
        }
        
        if ($report->documentation_uploads && count($report->documentation_uploads) > 0) {
            $disk = SystemSetting::getStorageDisk();
            Storage::disk($disk)->delete($report->documentation_uploads);
        }

        $report->delete();

        return $this->successResponse('Weekly report berhasil dihapus.');
    }

    private function belongsToProject(Project $project, WeeklyReport $report): bool
    {
        return (int) $report->project_id === (int) $project->id;
    }

    private function canViewProject(Project $project, $user): bool
    {
        if ($user->hasAnyRole(self::ELEVATED_ROLES) || $user->can('projects.view.all')) {
            return true;
        }

        return $project->team()->where('users.id', $user->id)->exists();
    }
}
