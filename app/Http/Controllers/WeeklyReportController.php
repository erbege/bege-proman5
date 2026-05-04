<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProgressReport;
use App\Models\SystemSetting;
use App\Models\WeeklyReport;
use App\Services\DocumentationService;
use App\Services\NotificationHelper;
use App\Services\WeeklyReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WeeklyReportController extends Controller
{
    protected WeeklyReportService $service;
    protected DocumentationService $documentationService;

    public function __construct(WeeklyReportService $service, DocumentationService $documentationService)
    {
        $this->service = $service;
        $this->documentationService = $documentationService;
    }

    /**
     * Display a listing of weekly reports
     */
    public function index(Project $project)
    {
        $this->authorize('weekly_report.view');
        $user = auth()->user();
        
        $query = WeeklyReport::where('project_id', $project->id);

        // Owners can only see published reports
        if ($user->hasRole('owner')) {
            $query->published();
        }

        $reports = $query->with('creator')
            ->orderByDesc('week_number')
            ->paginate(20);

        $nextWeek = $this->service->getNextWeekNumber($project);
        $currentWeek = $this->service->getCurrentWeekNumber($project);

        return view('projects.weekly-reports.index', compact('project', 'reports', 'nextWeek', 'currentWeek'));
    }

    /**
     * Show form for creating a new weekly report
     */
    public function create(Project $project)
    {
        $this->authorize('weekly_report.manage');
        $nextWeek = $this->service->getNextWeekNumber($project);
        $period = $this->service->calculatePeriod($project, $nextWeek);
        $projectImages = $this->service->getProjectImages($project);

        return view('projects.weekly-reports.create', compact('project', 'nextWeek', 'period', 'projectImages'));
    }

    /**
     * Store a newly created weekly report
     */
    public function store(Request $request, Project $project)
    {
        $this->authorize('weekly_report.manage');
        $validated = $request->validate([
            'week_number' => 'required|integer|min:1',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'cover_title' => 'nullable|string|max:255',
            'cover_image_id' => 'nullable|exists:project_files,id',
            'cover_image_upload' => 'nullable|image|max:5120',
            'documentation_ids' => 'nullable|array',
            'documentation_ids.*' => 'exists:project_files,id',
            'activities' => 'nullable|string',
            'problems' => 'nullable|string',
        ]);

        // Check if report for this week already exists
        $existing = WeeklyReport::where('project_id', $project->id)
            ->where('week_number', $validated['week_number'])
            ->first();

        if ($existing) {
            return back()->withErrors(['week_number' => 'Weekly report untuk minggu ini sudah ada.']);
        }

        // Handle cover image upload
        $coverImagePath = null;
        if ($request->hasFile('cover_image_upload')) {
            $disk = SystemSetting::getStorageDisk();
            $imageResizer = new \App\Services\ImageResizeService();
            $coverImagePath = $imageResizer->processAndSave(
                $request->file('cover_image_upload'),
                "weekly-reports/{$project->id}",
                $disk
            );
        }

        // Generate cumulative and detail data
        $periodEnd = \Carbon\Carbon::parse($validated['period_end']);
        $periodStart = \Carbon\Carbon::parse($validated['period_start']);

        $cumulativeData = $this->service->generateCumulativeData($project, $periodEnd, $validated['week_number']);
        $detailData = $this->service->generateDetailData($project, $periodStart, $periodEnd);

        $report = WeeklyReport::create([
            'project_id' => $project->id,
            'week_number' => $validated['week_number'],
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'cover_title' => $validated['cover_title'] ?? "Weekly Progress Report - Week {$validated['week_number']}",
            'cover_image_id' => $validated['cover_image_id'] ?? null,
            'cover_image_path' => $coverImagePath,
            'cumulative_data' => $cumulativeData,
            'detail_data' => $detailData,
            'documentation_ids' => $validated['documentation_ids'] ?? [],
            'activities' => $validated['activities'] ?? null,
            'problems' => $validated['problems'] ?? null,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('projects.weekly-reports.show', [$project, $report])
            ->with('success', 'Weekly report berhasil dibuat.');
    }

    /**
     * Display the specified weekly report
     */
    public function show(Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.view');
        $user = auth()->user();

        // Owners can only see published reports
        if ($user->hasRole('owner') && $report->status !== 'published') {
            abort(403, 'Laporan ini belum dipublikasikan untuk Klien.');
        }

        $report->load(['coverImage.latestVersion', 'creator', 'comments.user']);

        return view('projects.weekly-reports.show', compact('project', 'report'));
    }

    /**
     * Show form for editing the weekly report
     */
    public function edit(Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.manage');
        $report->load(['coverImage.latestVersion', 'creator']);
        $projectImages = $this->service->getProjectImages($project);

        // Get progress report photos from the period
        $progressPhotos = $this->getProgressPhotos($project, $report);

        return view('projects.weekly-reports.edit', compact('project', 'report', 'projectImages', 'progressPhotos'));
    }

    /**
     * Update the specified weekly report (full form submit)
     */
    public function update(Request $request, Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.manage');
        
        $validated = $request->validate([
            'cover_title' => 'nullable|string|max:255',
            'cover_image_id' => 'nullable|exists:project_files,id',
            'cover_image_upload' => 'nullable|image|max:5120',
            'documentation_ids' => 'nullable|array',
            'documentation_ids.*' => 'exists:project_files,id',
            'documentation_uploads' => 'nullable|array',
            'documentation_uploads.*' => 'image|max:5120',
            'activities' => 'nullable|string',
            'problems' => 'nullable|string',
            'status' => 'nullable|in:draft,published',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'published') {
            $this->authorize('weekly_report.publish');
        }

        // Handle cover image upload
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
        }

        // Handle documentation uploads
        if ($request->hasFile('documentation_uploads')) {
            $disk = SystemSetting::getStorageDisk();
            $imageResizer = new \App\Services\ImageResizeService();
            $existingUploads = $report->documentation_uploads ?? [];

            foreach ($request->file('documentation_uploads') as $file) {
                $path = $imageResizer->processAndSave(
                    $file,
                    "weekly-reports/{$project->id}/docs",
                    $disk
                );
                $existingUploads[] = $path;
            }

            $validated['documentation_uploads'] = $existingUploads;
            unset($validated['documentation_uploads']);
            $report->documentation_uploads = $existingUploads;
        }

        $oldStatus = $report->status;
        $report->update($validated);

        if ($oldStatus !== 'published' && $report->status === 'published') {
            $owner = $report->project->owner;
            if ($owner) {
                $owner->notify(new \App\Notifications\WeeklyReportPublished($report));
            }
        }

        return redirect()
            ->route('projects.weekly-reports.show', [$project, $report])
            ->with('success', 'Weekly report berhasil diperbarui.');
    }

    public function togglePublish(Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.publish');

        $isPublishing = $report->status !== 'published';
        $report->update([
            'status' => $isPublishing ? 'published' : 'draft'
        ]);

        if ($isPublishing) {
            // Notify Owner
            $owner = $report->project->owner;
            if ($owner) {
                $owner->notify(new \App\Notifications\WeeklyReportPublished($report));
            }
            $message = 'Laporan mingguan berhasil dipublikasikan ke Owner.';
        } else {
            $message = 'Laporan mingguan berhasil ditarik (unpublish).';
        }

        return back()->with('success', $message);
    }

    /**
     * Update cover section (AJAX)
     */
    public function updateCover(Request $request, Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.manage');
        
        $validated = $request->validate([
            'cover_title' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,published',
            'cover_image_id' => 'nullable|exists:project_files,id',
            'cover_image_upload' => 'nullable|image|max:5120',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'published') {
            $this->authorize('weekly_report.publish');
        }

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
            // Clear project file selection if uploading new
            $validated['cover_image_id'] = null;
        }

        $oldStatus = $report->status;
        $report->update($validated);

        if ($oldStatus !== 'published' && $report->status === 'published') {
            $owner = $report->project->owner;
            if ($owner) {
                $owner->notify(new \App\Notifications\WeeklyReportPublished($report));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Cover berhasil diperbarui.',
            'cover_image_url' => $report->fresh()->cover_image_url,
        ]);
    }

    /**
     * Update detail data (AJAX)
     */
    public function updateDetail(Request $request, Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.manage');
        $request->validate([
            'detail_data' => 'required|array',
        ]);

        $report->update(['detail_data' => $request->input('detail_data')]);

        return response()->json([
            'success' => true,
            'message' => 'Detail progress berhasil diperbarui.',
        ]);
    }

    /**
     * Upload documentation photo (AJAX)
     */
    public function uploadDocumentation(Request $request, Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.manage');
        $request->validate([
            'photos' => 'required|array|min:1',
            'photos.*' => 'image|max:5120',
        ]);

        $result = $this->documentationService->uploadDocumentation($report, $project, $request->file('photos'));

        return response()->json([
            'success' => true,
            'message' => $result['count'] . ' foto berhasil diupload.',
            'photos' => $result['photos'],
        ]);
    }

    /**
     * Add progress report photos to documentation (AJAX)
     */
    public function addProgressPhotos(Request $request, Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.manage');
        $request->validate([
            'photo_paths' => 'required|array|min:1',
            'photo_paths.*' => 'string',
        ]);

        $result = $this->documentationService->addProgressPhotos($report, $request->input('photo_paths'));

        return response()->json([
            'success' => true,
            'message' => $result['count'] . ' foto dari progress report ditambahkan.',
            'photos' => $result['photos'],
        ]);
    }

    /**
     * Remove documentation photo (AJAX)
     */
    public function removeDocumentation(Request $request, Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.manage');
        $request->validate([
            'type' => 'required|in:project_file,upload',
            'id' => 'required_if:type,project_file',
            'path' => 'required_if:type,upload|string',
        ]);

        $type = $request->input('type');
        $identifier = $type === 'project_file' ? $request->input('id') : $request->input('path');
        $this->documentationService->removeDocumentation($report, $type, $identifier);

        return response()->json([
            'success' => true,
            'message' => 'Foto berhasil dihapus dari dokumentasi.',
        ]);
    }

    /**
     * Update documentation IDs from project files (AJAX)
     */
    public function updateDocumentationIds(Request $request, Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.manage');
        $request->validate([
            'documentation_ids' => 'nullable|array',
            'documentation_ids.*' => 'exists:project_files,id',
        ]);

        $this->documentationService->updateDocumentationIds($report, $request->input('documentation_ids', []));

        return response()->json([
            'success' => true,
            'message' => 'Dokumentasi dari project files berhasil diperbarui.',
        ]);
    }

    /**
     * Update activities and problems (AJAX)
     */
    public function updateActivities(Request $request, Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.manage');
        $request->validate([
            'activities' => 'nullable|string',
            'problems' => 'nullable|string',
        ]);

        $report->update([
            'activities' => $request->input('activities'),
            'problems' => $request->input('problems'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Aktivitas dan kendala berhasil diperbarui.',
        ]);
    }

    /**
     * Get progress report photos for the report period
     */
    protected function getProgressPhotos(Project $project, WeeklyReport $report): array
    {
        return $this->documentationService->getProgressPhotosForPeriod(
            $project,
            Carbon::parse($report->period_start),
            Carbon::parse($report->period_end)
        );
    }

    /**
     * Remove the specified weekly report
     */
    public function destroy(Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.manage');
        // Delete uploaded cover image if exists
        if ($report->cover_image_path) {
            $disk = SystemSetting::getStorageDisk();
            Storage::disk($disk)->delete($report->cover_image_path);
        }

        $report->delete();

        return redirect()
            ->route('projects.weekly-reports.index', $project)
            ->with('success', 'Weekly report berhasil dihapus.');
    }

    /**
     * Export weekly report to PDF (Format Standar PUPR)
     */
    public function exportPdf(Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.view');
        $report->load(['coverImage.latestVersion', 'creator', 'approver', 'reviewer', 'submitter']);

        // Pre-compute weather & labor summaries from detail_data
        $weatherSummary = [];
        $laborSummary = [];
        if ($report->detail_data && is_array($report->detail_data)) {
            foreach ($report->detail_data as $detail) {
                $date = $detail['date_label'] ?? '-';
                $weather = $detail['weather'] ?? '-';
                $workers = $detail['workers_count'] ?? 0;

                if (!isset($weatherSummary[$date])) {
                    $weatherSummary[$date] = [
                        'date' => $date,
                        'weather' => $weather,
                        'workers' => $workers,
                        'description' => $detail['description'] ?? '-',
                    ];
                } else {
                    // Aggregate workers for same date
                    $weatherSummary[$date]['workers'] = max($weatherSummary[$date]['workers'], $workers);
                }
            }
        }

        $pdf = Pdf::loadView('projects.weekly-reports.pdf', [
            'project' => $project,
            'report' => $report,
            'weatherSummary' => array_values($weatherSummary),
        ]);

        $pdf->setPaper('a4', 'portrait');

        $filename = "Laporan_Mingguan_{$project->code}_Minggu_{$report->week_number}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Regenerate cumulative and detail data for a report
     */
    public function regenerate(Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.manage');
        $periodEnd = \Carbon\Carbon::parse($report->period_end);
        $periodStart = \Carbon\Carbon::parse($report->period_start);

        $cumulativeData = $this->service->generateCumulativeData($project, $periodEnd, $report->week_number);
        $detailData = $this->service->generateDetailData($project, $periodStart, $periodEnd);

        $report->update([
            'cumulative_data' => $cumulativeData,
            'detail_data' => $detailData,
        ]);

        return redirect()
            ->route('projects.weekly-reports.show', [$project, $report])
            ->with('success', 'Data weekly report berhasil di-regenerate.');
    }

    /**
     * Auto-generate weekly report for current/specified week
     */
    public function autoGenerate(Request $request, Project $project)
    {
        $this->authorize('weekly_report.manage');
        $weekNumber = $request->input('week_number');

        try {
            $report = $this->service->autoGenerate($project, $weekNumber);

            return redirect()
                ->route('projects.weekly-reports.show', [$project, $report])
                ->with('success', "Weekly report Week {$report->week_number} berhasil di-generate.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal generate weekly report: ' . $e->getMessage()]);
        }
    }

    /**
     * Auto-generate all missing weekly reports
     */
    public function autoGenerateAll(Project $project)
    {
        $this->authorize('weekly_report.manage');
        try {
            $generated = $this->service->autoGenerateAll($project);
            $count = count($generated);

            if ($count === 0) {
                return redirect()
                    ->route('projects.weekly-reports.index', $project)
                    ->with('info', 'Semua weekly report sudah ter-generate.');
            }

            return redirect()
                ->route('projects.weekly-reports.index', $project)
                ->with('success', "{$count} weekly report berhasil di-generate.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal generate weekly reports: ' . $e->getMessage()]);
        }
    }

    /**
     * Update cumulative data (AJAX) - inline edit actual_current values
     */
    public function updateCumulative(Request $request, Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.manage');
        $request->validate([
            'items' => 'required|array',
            'items.*' => 'numeric|min:0',
        ]);

        $result = $this->service->updateCumulativeActuals($report, $request->input('items'));

        if (empty($result['data'])) {
            return response()->json(['error' => $result['message']], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'cumulative_data' => $result['data'],
            'cascaded_weeks' => $result['cascaded_count'],
        ]);
    }

    /**
     * Bulk delete weekly reports
     */
    public function bulkDestroy(Request $request, Project $project)
    {
        $this->authorize('weekly_report.manage');
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:weekly_reports,id',
        ]);

        $reports = WeeklyReport::whereIn('id', $validated['ids'])
            ->where('project_id', $project->id)
            ->get();

        $count = 0;
        foreach ($reports as $report) {
            // Delete uploaded cover image if exists
            if ($report->cover_image_path) {
                $disk = SystemSetting::getStorageDisk();
                Storage::disk($disk)->delete($report->cover_image_path);
            }
            $report->delete();
            $count++;
        }

        return redirect()
            ->route('projects.weekly-reports.index', $project)
            ->with('success', "{$count} weekly report berhasil dihapus.");
    }

    // ========================
    // Approval Workflow Actions
    // ========================

    /**
     * Submit weekly report for review (draft → in_review)
     */
    public function submitForReview(Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.manage');

        try {
            $this->service->submitForReview($report, auth()->id());

            // Notify project managers / reviewers
            $report->loadMissing('project');
            NotificationHelper::sendToProjectTeam(
                $report->project,
                new \App\Notifications\WeeklyReportStatusNotification($report, 'submitted'),
                auth()->id()
            );

            return back()->with('success', "Weekly Report Week {$report->week_number} berhasil diajukan untuk review.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Approve weekly report (in_review → approved)
     */
    public function approve(Request $request, Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.manage');

        try {
            $this->service->approve($report, auth()->id(), $request->input('comment'));

            // Notify the creator/submitter
            $report->loadMissing(['project', 'creator', 'submitter']);
            NotificationHelper::sendToProjectTeam(
                $report->project,
                new \App\Notifications\WeeklyReportStatusNotification($report, 'approved'),
                auth()->id()
            );

            return back()->with('success', "Weekly Report Week {$report->week_number} berhasil disetujui.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reject weekly report (in_review → rejected)
     */
    public function reject(Request $request, Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.manage');

        $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ]);

        try {
            $this->service->reject($report, auth()->id(), $request->input('rejection_reason'));

            // Notify the creator/submitter
            $report->loadMissing(['project', 'creator', 'submitter']);
            NotificationHelper::sendToProjectTeam(
                $report->project,
                new \App\Notifications\WeeklyReportStatusNotification($report, 'rejected'),
                auth()->id()
            );

            return back()->with('success', "Weekly Report Week {$report->week_number} ditolak.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Publish weekly report (approved → published)
     */
    public function publish(Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.manage');

        try {
            $this->service->publish($report, auth()->id());

            // Notify entire team including owner
            $report->loadMissing('project');
            NotificationHelper::sendToProjectTeam(
                $report->project,
                new \App\Notifications\WeeklyReportStatusNotification($report, 'published')
            );

            return back()->with('success', "Weekly Report Week {$report->week_number} berhasil dipublish.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
