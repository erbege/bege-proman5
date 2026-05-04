<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\MonthlyReport;
use App\Models\SystemSetting;
use App\Services\DocumentationService;
use App\Services\MonthlyReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonthlyReportController extends Controller
{
    protected MonthlyReportService $service;
    protected DocumentationService $documentationService;

    public function __construct(MonthlyReportService $service, DocumentationService $documentationService)
    {
        $this->service = $service;
        $this->documentationService = $documentationService;
    }

    public function index(Project $project)
    {
        $this->authorize('monthly_report.view');
        $user = auth()->user();
        
        $query = MonthlyReport::where('project_id', $project->id);

        if ($user->hasRole('owner')) {
            $query->published();
        }

        $reports = $query->with('creator')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate(20);

        $nextPeriod = $this->service->getNextMonth($project);
        $currentDate = Carbon::now();

        return view('projects.monthly-reports.index', compact('project', 'reports', 'nextPeriod', 'currentDate'));
    }

    public function create(Project $project)
    {
        $this->authorize('monthly_report.manage');
        $nextPeriod = $this->service->getNextMonth($project);
        $period = $this->service->calculatePeriod($nextPeriod['year'], $nextPeriod['month']);
        
        $projectImages = \App\Models\ProjectFile::where('project_id', $project->id)
            ->whereHas('latestVersion', function ($query) {
                $query->whereIn('extension', ['jpg', 'jpeg', 'png']);
            })
            ->get();

        $progressPhotos = $this->documentationService->getProgressPhotosForPeriod($project, $period['start'], $period['end']);

        return view('projects.monthly-reports.create', compact('project', 'nextPeriod', 'period', 'projectImages', 'progressPhotos'));
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('monthly_report.manage');
        $validated = $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
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

        // Check if report already exists
        $existing = MonthlyReport::where('project_id', $project->id)
            ->where('year', $validated['year'])
            ->where('month', $validated['month'])
            ->first();

        if ($existing) {
            return back()->withErrors(['month' => 'Monthly report untuk periode ini sudah ada.']);
        }

        $coverImagePath = null;
        if ($request->hasFile('cover_image_upload')) {
            $disk = SystemSetting::getStorageDisk();
            $imageResizer = new \App\Services\ImageResizeService();
            $coverImagePath = $imageResizer->processAndSave(
                $request->file('cover_image_upload'),
                "monthly-reports/{$project->id}",
                $disk
            );
        }

        $periodEnd = \Carbon\Carbon::parse($validated['period_end']);
        $periodStart = \Carbon\Carbon::parse($validated['period_start']);

        $cumulativeData = $this->service->generateCumulativeData($project, $periodEnd, $validated['year'], $validated['month']);
        $detailData = $this->service->generateDetailData($project, $periodStart, $periodEnd);

        $report = MonthlyReport::create([
            'project_id' => $project->id,
            'year' => $validated['year'],
            'month' => $validated['month'],
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'cover_title' => $validated['cover_title'] ?? "Monthly Progress Report - " . Carbon::createFromDate($validated['year'], $validated['month'], 1)->translatedFormat('F Y'),
            'cover_image_id' => $validated['cover_image_id'] ?? null,
            'cover_image_path' => $coverImagePath,
            'cumulative_data' => $cumulativeData,
            'detail_data' => $detailData,
            'documentation_ids' => $validated['documentation_ids'] ?? [],
            'activities' => $validated['activities'] ?? null,
            'problems' => $validated['problems'] ?? null,
            'status' => MonthlyReport::STATUS_DRAFT,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('projects.monthly-reports.show', [$project, $report])
            ->with('success', 'Monthly report berhasil dibuat.');
    }

    public function show(Project $project, MonthlyReport $report)
    {
        $this->authorize('monthly_report.view');
        $user = auth()->user();

        if ($user->hasRole('owner') && $report->status !== MonthlyReport::STATUS_PUBLISHED) {
            abort(403, 'Laporan ini belum dipublikasikan untuk Klien.');
        }

        $report->load(['coverImage.latestVersion', 'creator', 'comments.user', 'approvalLogs.user']);

        return view('projects.monthly-reports.show', compact('project', 'report'));
    }

    public function edit(Project $project, MonthlyReport $report)
    {
        $this->authorize('monthly_report.manage');

        if (!$report->is_editable) {
            return redirect()->route('projects.monthly-reports.show', [$project, $report])
                ->with('error', 'Laporan yang sedang di-review atau sudah disetujui tidak dapat diubah.');
        }

        $projectImages = \App\Models\ProjectFile::where('project_id', $project->id)
            ->whereHas('latestVersion', function ($query) {
                $query->whereIn('extension', ['jpg', 'jpeg', 'png']);
            })
            ->get();

        $progressPhotos = $this->documentationService->getProgressPhotosForPeriod($project, $report->period_start, $report->period_end);

        return view('projects.monthly-reports.edit', compact('project', 'report', 'projectImages', 'progressPhotos'));
    }

    public function update(Request $request, Project $project, MonthlyReport $report)
    {
        $this->authorize('monthly_report.manage');

        if (!$report->is_editable) {
            return redirect()->route('projects.monthly-reports.show', [$project, $report])
                ->with('error', 'Laporan yang sedang di-review atau sudah disetujui tidak dapat diubah.');
        }

        $validated = $request->validate([
            'cover_title' => 'nullable|string|max:255',
            'cover_image_id' => 'nullable|exists:project_files,id',
            'cover_image_upload' => 'nullable|image|max:5120',
            'activities' => 'nullable|string',
            'problems' => 'nullable|string',
        ]);

        $coverImagePath = $report->cover_image_path;
        if ($request->hasFile('cover_image_upload')) {
            $disk = SystemSetting::getStorageDisk();
            $imageResizer = new \App\Services\ImageResizeService();
            $coverImagePath = $imageResizer->processAndSave(
                $request->file('cover_image_upload'),
                "monthly-reports/{$project->id}",
                $disk
            );
        }

        $report->update([
            'cover_title' => $validated['cover_title'] ?? $report->cover_title,
            'cover_image_id' => $validated['cover_image_id'] ?? $report->cover_image_id,
            'cover_image_path' => $coverImagePath,
            'activities' => $validated['activities'] ?? null,
            'problems' => $validated['problems'] ?? null,
        ]);

        return redirect()
            ->route('projects.monthly-reports.show', [$project, $report])
            ->with('success', 'Monthly report berhasil diperbarui.');
    }

    public function destroy(Project $project, MonthlyReport $report)
    {
        $this->authorize('monthly_report.manage');

        if ($report->status === MonthlyReport::STATUS_PUBLISHED) {
            return back()->with('error', 'Laporan yang sudah dipublish tidak dapat dihapus.');
        }

        $report->delete();

        return redirect()
            ->route('projects.monthly-reports.index', $project)
            ->with('success', 'Monthly report berhasil dihapus.');
    }

    // Workflow actions

    public function submit(Project $project, MonthlyReport $report)
    {
        $this->authorize('monthly_report.manage');
        
        try {
            $this->service->submitForReview($project, $report, auth()->user());
            return back()->with('success', 'Laporan berhasil diajukan untuk review.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(Project $project, MonthlyReport $report)
    {
        $this->authorize('monthly_report.approve');
        
        try {
            $this->service->approve($project, $report, auth()->user());
            return back()->with('success', 'Laporan berhasil disetujui.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, Project $project, MonthlyReport $report)
    {
        $this->authorize('monthly_report.approve');
        
        $request->validate(['rejection_reason' => 'required|string|min:5']);
        
        try {
            $this->service->reject($project, $report, auth()->user(), $request->rejection_reason);
            return back()->with('success', 'Laporan berhasil ditolak dan dikembalikan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function publish(Project $project, MonthlyReport $report)
    {
        $this->authorize('monthly_report.publish');
        
        try {
            $this->service->publish($project, $report, auth()->user());
            return back()->with('success', 'Laporan berhasil dipublish ke Owner.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // Documentation handling

    public function addDocumentation(Request $request, Project $project, MonthlyReport $report)
    {
        $this->authorize('monthly_report.manage');

        $validated = $request->validate([
            'files.*' => 'required|image|max:5120',
        ]);

        $disk = SystemSetting::getStorageDisk();
        $this->documentationService->addProgressPhotos($report, $validated['files'] ?? [], $disk, "monthly-reports/{$project->id}/{$report->id}");

        return back()->with('success', 'Dokumentasi berhasil ditambahkan.');
    }

    public function removeDocumentation(Request $request, Project $project, MonthlyReport $report)
    {
        $this->authorize('monthly_report.manage');

        $validated = $request->validate([
            'file_id' => 'required|string',
        ]);

        $disk = SystemSetting::getStorageDisk();
        $this->documentationService->removeDocumentation($report, $validated['file_id'], $disk);

        return back()->with('success', 'Dokumentasi berhasil dihapus.');
    }

    public function autoGenerateAll(Project $project)
    {
        $this->authorize('monthly_report.manage');

        $projectStart = Carbon::parse($project->start_date)->startOfMonth();
        $today = Carbon::now()->endOfMonth();
        
        $currentDate = $projectStart->copy();
        $generated = 0;

        while ($currentDate->lte($today)) {
            $year = $currentDate->year;
            $month = $currentDate->month;

            $exists = MonthlyReport::where('project_id', $project->id)
                ->where('year', $year)
                ->where('month', $month)
                ->exists();

            if (!$exists) {
                $periodStart = $currentDate->copy()->startOfMonth();
                $periodEnd = $currentDate->copy()->endOfMonth();

                $cumulativeData = $this->service->generateCumulativeData($project, $periodEnd, $year, $month);
                $detailData = $this->service->generateDetailData($project, $periodStart, $periodEnd);

                MonthlyReport::create([
                    'project_id' => $project->id,
                    'year' => $year,
                    'month' => $month,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'cover_title' => "Monthly Progress Report - " . $currentDate->translatedFormat('F Y'),
                    'cumulative_data' => $cumulativeData,
                    'detail_data' => $detailData,
                    'status' => MonthlyReport::STATUS_DRAFT,
                    'created_by' => auth()->id(),
                ]);
                
                $generated++;
            }

            $currentDate->addMonth();
        }

        return back()->with('success', "Berhasil generate {$generated} monthly report otomatis.");
    }

    public function updateCumulative(Request $request, Project $project, MonthlyReport $report)
    {
        $this->authorize('monthly_report.manage');
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
            'cascaded_months' => $result['cascaded_count'] ?? 0,
        ]);
    }

    public function updateCover(Request $request, Project $project, MonthlyReport $report)
    {
        $this->authorize('monthly_report.manage');
        
        $validated = $request->validate([
            'cover_title' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,published',
            'cover_image_id' => 'nullable|exists:project_files,id',
            'cover_image_upload' => 'nullable|image|max:5120',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'published') {
            $this->authorize('monthly_report.publish');
        }

        if ($request->hasFile('cover_image_upload')) {
            if ($report->cover_image_path) {
                $disk = SystemSetting::getStorageDisk();
                \Illuminate\Support\Facades\Storage::disk($disk)->delete($report->cover_image_path);
            }

            $disk = SystemSetting::getStorageDisk();
            $imageResizer = new \App\Services\ImageResizeService();
            $validated['cover_image_path'] = $imageResizer->processAndSave(
                $request->file('cover_image_upload'),
                "monthly-reports/{$project->id}",
                $disk
            );
            unset($validated['cover_image_upload']);
        }

        $report->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cover berhasil diperbarui.',
            'cover_image_url' => $report->cover_image_url,
        ]);
    }

    public function addProgressPhotos(Request $request, Project $project, MonthlyReport $report)
    {
        $this->authorize('monthly_report.manage');
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

    public function updateActivities(Request $request, Project $project, MonthlyReport $report)
    {
        $this->authorize('monthly_report.manage');
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

    public function bulkDestroy(Request $request, Project $project)
    {
        $this->authorize('monthly_report.manage');
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:monthly_reports,id',
        ]);

        $reports = MonthlyReport::whereIn('id', $validated['ids'])
            ->where('project_id', $project->id)
            ->get();

        $count = 0;
        foreach ($reports as $report) {
            if ($report->cover_image_path) {
                $disk = SystemSetting::getStorageDisk();
                \Illuminate\Support\Facades\Storage::disk($disk)->delete($report->cover_image_path);
            }
            $report->delete();
            $count++;
        }

        return redirect()
            ->route('projects.monthly-reports.index', $project)
            ->with('success', "{$count} monthly report berhasil dihapus.");
    }

    public function regenerate(Project $project, MonthlyReport $report)
    {
        $this->authorize('monthly_report.manage');
        $periodEnd = \Carbon\Carbon::parse($report->period_end);
        $periodStart = \Carbon\Carbon::parse($report->period_start);

        $cumulativeData = $this->service->generateCumulativeData($project, $periodEnd, $report->year, $report->month);
        $detailData = $this->service->generateDetailData($project, $periodStart, $periodEnd);

        $report->update([
            'cumulative_data' => $cumulativeData,
            'detail_data' => $detailData,
        ]);

        return redirect()
            ->route('projects.monthly-reports.show', [$project, $report])
            ->with('success', 'Data monthly report berhasil di-regenerate.');
    }

    public function exportPdf(Project $project, MonthlyReport $report)
    {
        $this->authorize('monthly_report.view');
        $report->load(['coverImage.latestVersion', 'creator']);

        $pdf = Pdf::loadView('projects.monthly-reports.pdf', [
            'project' => $project,
            'report' => $report,
        ]);

        $pdf->setPaper('a4', 'landscape');

        $filename = "monthly-report-{$project->code}-{$report->year}-{$report->month}.pdf";

        return $pdf->download($filename);
    }

    public function copyFromPrevious(Project $project, MonthlyReport $report)
    {
        $this->authorize('monthly_report.manage');

        try {
            $this->service->copyDataFromPreviousMonth($project, $report);
            return back()->with('success', 'Data dokumentasi dan kumulatif berhasil disalin dari bulan sebelumnya.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
