<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProgressReport;
use App\Models\SystemSetting;
use App\Models\WeeklyReport;
use App\Services\WeeklyReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WeeklyReportController extends Controller
{
    protected WeeklyReportService $service;

    public function __construct(WeeklyReportService $service)
    {
        $this->service = $service;
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
            ];
        }

        $report->update(['documentation_uploads' => $uploads]);

        return response()->json([
            'success' => true,
            'message' => count($newPhotos) . ' foto berhasil diupload.',
            'photos' => $newPhotos,
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

        $uploads = $report->documentation_uploads ?? [];
        $added = [];

        foreach ($request->input('photo_paths') as $path) {
            if (!in_array($path, $uploads)) {
                $uploads[] = $path;
                $added[] = [
                    'path' => $path,
                    'url' => SystemSetting::getFileUrl($path),
                    'name' => basename($path),
                ];
            }
        }

        $report->update(['documentation_uploads' => $uploads]);

        return response()->json([
            'success' => true,
            'message' => count($added) . ' foto dari progress report ditambahkan.',
            'photos' => $added,
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

        if ($request->input('type') === 'project_file') {
            $ids = $report->documentation_ids ?? [];
            $ids = array_values(array_filter($ids, fn($id) => $id != $request->input('id')));
            $report->update(['documentation_ids' => $ids]);
        } else {
            $uploads = $report->documentation_uploads ?? [];
            $path = $request->input('path');
            $uploads = array_values(array_filter($uploads, fn($p) => $p !== $path));
            $report->update(['documentation_uploads' => $uploads]);

            // Optionally delete the file
            // $disk = SystemSetting::getStorageDisk();
            // Storage::disk($disk)->delete($path);
        }

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

        $report->update(['documentation_ids' => $request->input('documentation_ids', [])]);

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
        $reports = ProgressReport::where('project_id', $project->id)
            ->whereBetween('report_date', [$report->period_start, $report->period_end])
            ->with(['rabItem'])
            ->orderBy('report_date')
            ->get();

        $photos = [];
        foreach ($reports as $pr) {
            if ($pr->photos && is_array($pr->photos)) {
                foreach ($pr->photos as $photoPath) {
                    $photos[] = [
                        'path' => $photoPath,
                        'url' => SystemSetting::getFileUrl($photoPath),
                        'date' => $pr->report_date->format('d M Y'),
                        'rab_item' => $pr->rabItem ? $pr->rabItem->full_code . ' - ' . $pr->rabItem->work_name : 'N/A',
                        'reporter' => $pr->reporter ? $pr->reporter->name : 'Unknown',
                    ];
                }
            }
        }

        return $photos;
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
     * Export weekly report to PDF
     */
    public function exportPdf(Project $project, WeeklyReport $report)
    {
        $this->authorize('weekly_report.view');
        $report->load(['coverImage.latestVersion', 'creator']);

        $pdf = Pdf::loadView('projects.weekly-reports.pdf', [
            'project' => $project,
            'report' => $report,
        ]);

        // A4 paper size with landscape orientation for wide cumulative progress table
        $pdf->setPaper('a4', 'landscape');

        $filename = "weekly-report-{$project->code}-week-{$report->week_number}.pdf";

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

        $itemUpdates = $request->input('items'); // ['item_code' => new_actual_current]
        $data = $report->cumulative_data;

        if (!$data || !isset($data['sections'])) {
            return response()->json(['error' => 'No cumulative data found.'], 422);
        }

        // Reset totals
        $totals = [
            'weight' => 0,
            'planned_prev' => 0,
            'planned_current' => 0,
            'planned_cumulative' => 0,
            'actual_prev' => 0,
            'actual_current' => 0,
            'actual_cumulative' => 0,
            'deviation_prev' => 0,
            'deviation_current' => 0,
            'deviation_cumulative' => 0,
        ];

        // Recursively update sections
        foreach ($data['sections'] as &$section) {
            $this->updateSectionItems($section, $itemUpdates, $totals);
        }

        // Calculate deviation totals
        $totals['deviation_prev'] = $totals['actual_prev'] - $totals['planned_prev'];
        $totals['deviation_current'] = $totals['actual_current'] - $totals['planned_current'];
        $totals['deviation_cumulative'] = $totals['actual_cumulative'] - $totals['planned_cumulative'];

        $data['totals'] = $totals;
        $report->update(['cumulative_data' => $data]);

        // Cascade update to subsequent weeks
        $cascadeCount = $this->cascadeToSubsequentWeeks($project, $report, $data);

        $message = 'Data realisasi berhasil disimpan.';
        if ($cascadeCount > 0) {
            $message .= " ({$cascadeCount} minggu berikutnya juga diperbarui)";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'cumulative_data' => $data,
            'cascaded_weeks' => $cascadeCount,
        ]);
    }

    /**
     * Cascade actual progress changes to all subsequent weekly reports
     */
    protected function cascadeToSubsequentWeeks(Project $project, WeeklyReport $currentReport, array $currentData): int
    {
        // Get all subsequent weekly reports ordered by week_number
        $subsequentReports = WeeklyReport::where('project_id', $project->id)
            ->where('week_number', '>', $currentReport->week_number)
            ->orderBy('week_number')
            ->get();

        if ($subsequentReports->isEmpty()) {
            return 0;
        }

        // Build item code => actual_cumulative map from the current week
        $prevCumulatives = [];
        $this->collectItemCumulatives($currentData['sections'], $prevCumulatives);

        // Process each subsequent week
        foreach ($subsequentReports as $nextReport) {
            $nextData = $nextReport->cumulative_data;
            if (!$nextData || !isset($nextData['sections'])) {
                continue;
            }

            // Reset totals for recalculation
            $nextTotals = [
                'weight' => 0,
                'planned_prev' => 0,
                'planned_current' => 0,
                'planned_cumulative' => 0,
                'actual_prev' => 0,
                'actual_current' => 0,
                'actual_cumulative' => 0,
                'deviation_prev' => 0,
                'deviation_current' => 0,
                'deviation_cumulative' => 0,
            ];

            // Update each item's actual.up_to_prev from previous week's cumulative
            foreach ($nextData['sections'] as &$section) {
                $this->cascadeSectionItems($section, $prevCumulatives, $nextTotals);
            }

            // Calculate deviation totals
            $nextTotals['deviation_prev'] = $nextTotals['actual_prev'] - $nextTotals['planned_prev'];
            $nextTotals['deviation_current'] = $nextTotals['actual_current'] - $nextTotals['planned_current'];
            $nextTotals['deviation_cumulative'] = $nextTotals['actual_cumulative'] - $nextTotals['planned_cumulative'];

            $nextData['totals'] = $nextTotals;
            $nextReport->update(['cumulative_data' => $nextData]);

            // Update prevCumulatives for the next iteration
            $prevCumulatives = [];
            $this->collectItemCumulatives($nextData['sections'], $prevCumulatives);
        }

        return $subsequentReports->count();
    }

    /**
     * Collect item code => actual_cumulative from sections recursively
     */
    protected function collectItemCumulatives(array $sections, array &$map): void
    {
        foreach ($sections as $section) {
            foreach ($section['items'] ?? [] as $item) {
                $map[$item['code']] = $item['actual']['cumulative'] ?? 0;
            }
            if (isset($section['children'])) {
                $this->collectItemCumulatives($section['children'], $map);
            }
        }
    }

    /**
     * Update a section's items with cascaded actual.up_to_prev and recalculate
     */
    protected function cascadeSectionItems(array &$section, array $prevCumulatives, array &$totals): void
    {
        foreach ($section['items'] as &$item) {
            $code = $item['code'];

            if (isset($prevCumulatives[$code])) {
                $newUpToPrev = round((float) $prevCumulatives[$code], 4);
                $item['actual']['up_to_prev'] = $newUpToPrev;
                $item['actual']['cumulative'] = round($newUpToPrev + $item['actual']['current'], 4);
                $item['deviation']['up_to_prev'] = round($newUpToPrev - $item['planned']['up_to_prev'], 4);
                $item['deviation']['current'] = round($item['actual']['current'] - $item['planned']['current'], 4);
                $item['deviation']['cumulative'] = round($item['actual']['cumulative'] - $item['planned']['cumulative'], 4);
            }

            // Accumulate totals
            $totals['weight'] += $item['weight'] ?? 0;
            $totals['planned_prev'] += $item['planned']['up_to_prev'] ?? 0;
            $totals['planned_current'] += $item['planned']['current'] ?? 0;
            $totals['planned_cumulative'] += $item['planned']['cumulative'] ?? 0;
            $totals['actual_prev'] += $item['actual']['up_to_prev'] ?? 0;
            $totals['actual_current'] += $item['actual']['current'] ?? 0;
            $totals['actual_cumulative'] += $item['actual']['cumulative'] ?? 0;
        }

        if (isset($section['children'])) {
            foreach ($section['children'] as &$child) {
                $this->cascadeSectionItems($child, $prevCumulatives, $totals);
            }
        }
    }

    /**
     * Recursively update items in a section and accumulate totals
     */
    protected function updateSectionItems(array &$section, array $itemUpdates, array &$totals): void
    {
        foreach ($section['items'] as &$item) {
            $code = $item['code'];

            if (isset($itemUpdates[$code])) {
                $newActualCurrent = round((float) $itemUpdates[$code], 4);
                $item['actual']['current'] = $newActualCurrent;
                $item['actual']['cumulative'] = round($item['actual']['up_to_prev'] + $newActualCurrent, 4);
                $item['deviation']['current'] = round($newActualCurrent - $item['planned']['current'], 4);
                $item['deviation']['cumulative'] = round($item['actual']['cumulative'] - $item['planned']['cumulative'], 4);
            }

            // Accumulate totals
            $totals['weight'] += $item['weight'] ?? 0;
            $totals['planned_prev'] += $item['planned']['up_to_prev'] ?? 0;
            $totals['planned_current'] += $item['planned']['current'] ?? 0;
            $totals['planned_cumulative'] += $item['planned']['cumulative'] ?? 0;
            $totals['actual_prev'] += $item['actual']['up_to_prev'] ?? 0;
            $totals['actual_current'] += $item['actual']['current'] ?? 0;
            $totals['actual_cumulative'] += $item['actual']['cumulative'] ?? 0;
        }

        // Process children recursively
        if (isset($section['children'])) {
            foreach ($section['children'] as &$child) {
                $this->updateSectionItems($child, $itemUpdates, $totals);
            }
        }
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
}
