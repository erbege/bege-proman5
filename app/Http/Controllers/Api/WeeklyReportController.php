<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProgressReport;
use App\Models\SystemSetting;
use App\Models\WeeklyReport;
use App\Services\WeeklyReportService;
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
     * Get list of weekly reports for a project
     */
    public function index(Project $project)
    {
        $reports = WeeklyReport::where('project_id', $project->id)
            ->with(['creator', 'coverImage.latestVersion'])
            ->orderByDesc('week_number')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Weekly reports retrieved successfully.',
            'data' => [
                'reports' => $reports,
                'next_week' => $this->service->getNextWeekNumber($project),
                'current_week' => $this->service->getCurrentWeekNumber($project),
            ]
        ]);
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
            return response()->json([
                'success' => false,
                'message' => 'Weekly report untuk minggu ini sudah ada.',
            ], 422);
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

        return response()->json([
            'success' => true,
            'message' => 'Weekly report berhasil dibuat.',
            'data' => $report,
        ], 201);
    }

    /**
     * Auto generate weekly report for current or specified week
     */
    public function autoGenerate(Request $request, Project $project)
    {
        $weekNumber = $request->input('week_number');

        try {
            $report = $this->service->autoGenerate($project, $weekNumber);

            return response()->json([
                'success' => true,
                'message' => "Weekly report Week {$report->week_number} berhasil di-generate.",
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate weekly report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show detailed weekly report data
     */
    public function show(Project $project, WeeklyReport $report)
    {
        // Load relationships
        $report->load(['coverImage.latestVersion', 'creator']);

        return response()->json([
            'success' => true,
            'message' => 'Weekly report detail retrieved.',
            'data' => $report,
        ]);
    }

    /**
     * Update Cover Section
     */
    public function updateCover(Request $request, Project $project, WeeklyReport $report)
    {
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

        return response()->json([
            'success' => true,
            'message' => 'Cover berhasil diperbarui.',
            'data' => $report->fresh(['coverImage.latestVersion']),
        ]);
    }

    /**
     * Update Cumulative data inline (Actual Progress)
     */
    public function updateCumulative(Request $request, Project $project, WeeklyReport $report)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*' => 'numeric|min:0',
        ]);

        $itemUpdates = $request->input('items');
        $data = $report->cumulative_data;

        if (!$data || !isset($data['sections'])) {
            return response()->json(['success' => false, 'message' => 'No cumulative data found.'], 422);
        }

        // Logic is the same as Web: Reset totals and compute
        $totals = [
            'weight' => 0, 'planned_prev' => 0, 'planned_current' => 0, 'planned_cumulative' => 0,
            'actual_prev' => 0, 'actual_current' => 0, 'actual_cumulative' => 0,
            'deviation_prev' => 0, 'deviation_current' => 0, 'deviation_cumulative' => 0,
        ];

        foreach ($data['sections'] as &$section) {
            $this->updateSectionItems($section, $itemUpdates, $totals);
        }

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
     * Update Detailed Data (Usually manual edit override of progress item records)
     */
    public function updateDetail(Request $request, Project $project, WeeklyReport $report)
    {
        $request->validate([
            'detail_data' => 'required|array',
        ]);

        $report->update(['detail_data' => $request->input('detail_data')]);

        return response()->json([
            'success' => true,
            'message' => 'Detail progress berhasil diperbarui.',
            'data' => $report->fresh(),
        ]);
    }

    /**
     * Upload documentation images
     */
    public function uploadDocumentation(Request $request, Project $project, WeeklyReport $report)
    {
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

        return response()->json([
            'success' => true,
            'message' => count($newPhotos) . ' foto berhasil diupload.',
            'data' => $newPhotos,
        ]);
    }

    /**
     * Add existing progress photos to documentations
     */
    public function addProgressPhotos(Request $request, Project $project, WeeklyReport $report)
    {
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

        return response()->json([
            'success' => true,
            'message' => count($added) . ' foto dari progress report ditambahkan.',
            'data' => $added,
        ]);
    }

    /**
     * Remove single documentation photo
     */
    public function removeDocumentation(Request $request, Project $project, WeeklyReport $report)
    {
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
            // Optional: delete from storage
            // Storage::disk(SystemSetting::getStorageDisk())->delete($path);
        }

        return response()->json([
            'success' => true,
            'message' => 'Foto berhasil dihapus dari dokumentasi.',
        ]);
    }

    /**
     * Update activities and problems
     */
    public function updateActivities(Request $request, Project $project, WeeklyReport $report)
    {
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
            'data' => [
                'activities' => $report->activities,
                'problems' => $report->problems,
            ]
        ]);
    }

    /**
     * Delete a weekly report
     */
    public function destroy(Project $project, WeeklyReport $report)
    {
        if ($report->cover_image_path) {
            $disk = SystemSetting::getStorageDisk();
            Storage::disk($disk)->delete($report->cover_image_path);
        }
        
        if ($report->documentation_uploads && count($report->documentation_uploads) > 0) {
            $disk = SystemSetting::getStorageDisk();
            Storage::disk($disk)->delete($report->documentation_uploads);
        }

        $report->delete();

        return response()->json([
            'success' => true,
            'message' => 'Weekly report berhasil dihapus.',
        ]);
    }

    // ========================================================================
    // Helper Methods for Cumulative Updating/Cascading (copied from Web Controller)
    // ========================================================================

    protected function cascadeToSubsequentWeeks(Project $project, WeeklyReport $currentReport, array $currentData): int
    {
        $subsequentReports = WeeklyReport::where('project_id', $project->id)
            ->where('week_number', '>', $currentReport->week_number)
            ->orderBy('week_number')
            ->get();

        if ($subsequentReports->isEmpty()) return 0;

        $prevCumulatives = [];
        $this->collectItemCumulatives($currentData['sections'], $prevCumulatives);

        foreach ($subsequentReports as $nextReport) {
            $nextData = $nextReport->cumulative_data;
            if (!$nextData || !isset($nextData['sections'])) continue;

            $nextTotals = [
                'weight' => 0, 'planned_prev' => 0, 'planned_current' => 0, 'planned_cumulative' => 0,
                'actual_prev' => 0, 'actual_current' => 0, 'actual_cumulative' => 0,
                'deviation_prev' => 0, 'deviation_current' => 0, 'deviation_cumulative' => 0,
            ];

            foreach ($nextData['sections'] as &$section) {
                $this->cascadeSectionItems($section, $prevCumulatives, $nextTotals);
            }

            $nextTotals['deviation_prev'] = $nextTotals['actual_prev'] - $nextTotals['planned_prev'];
            $nextTotals['deviation_current'] = $nextTotals['actual_current'] - $nextTotals['planned_current'];
            $nextTotals['deviation_cumulative'] = $nextTotals['actual_cumulative'] - $nextTotals['planned_cumulative'];

            $nextData['totals'] = $nextTotals;
            $nextReport->update(['cumulative_data' => $nextData]);

            $prevCumulatives = [];
            $this->collectItemCumulatives($nextData['sections'], $prevCumulatives);
        }

        return $subsequentReports->count();
    }

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
                $this->updateSectionItems($child, $itemUpdates, $totals);
            }
        }
    }
}
