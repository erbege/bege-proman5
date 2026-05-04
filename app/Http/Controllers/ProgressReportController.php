<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProgressReportRequest;
use App\Http\Requests\UpdateProgressReportRequest;
use App\Models\ProgressReport;
use App\Models\Project;
use App\Services\ProgressReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressReportController extends Controller
{
    protected ProgressReportService $service;

    public function __construct(ProgressReportService $service)
    {
        $this->service = $service;
    }

    public function index(Project $project)
    {
        $this->authorize('progress.view');
        $reports = $project->progressReports()
            ->with(['rabItem', 'reporter'])
            ->orderByDesc('report_date')
            ->paginate(20);

        return view('projects.progress.index', compact('project', 'reports'));
    }

    public function create(Project $project)
    {
        $this->authorize('progress.create');
        $rabItems = $project->rabItems()
            ->with('section')
            ->orderBy('sort_order')
            ->get();

        $weatherOptions = [
            'sunny' => 'Cerah',
            'cloudy' => 'Berawan',
            'rainy' => 'Hujan',
            'stormy' => 'Badai',
        ];

        return view('projects.progress.create', compact('project', 'rabItems', 'weatherOptions'));
    }

    public function store(StoreProgressReportRequest $request, Project $project)
    {
        $this->authorize('progress.create');
        $validated = $request->validated();
        $photoFiles = $request->file('photos', []);

        $report = $this->service->create($project, $validated, $photoFiles, Auth::id());
        $this->service->notifyTeam($report, Auth::id());

        return redirect()
            ->route('projects.progress.index', $project)
            ->with('success', 'Laporan progress berhasil ditambahkan.');
    }

    public function show(Project $project, ProgressReport $report)
    {
        $this->authorize('progress.view');
        $report->load(['rabItem.section', 'reporter', 'reviewer', 'rejector', 'publisher']);

        return view('projects.progress.show', compact('project', 'report'));
    }

    public function reviewPage(Project $project, ProgressReport $report)
    {
        $this->authorize('progress.approve');
        $report->load(['rabItem.section', 'reporter', 'reviewer', 'rejector', 'publisher']);

        return view('projects.progress.review', compact('project', 'report'));
    }

    public function update(UpdateProgressReportRequest $request, Project $project, ProgressReport $report)
    {
        $this->authorize('progress.update');
        $validated = $request->validated();
        $photoFiles = $request->file('photos', []);

        $report = $this->service->updateReport($report, $project, $validated, $photoFiles);

        return redirect()
            ->route('projects.progress.show', [$project, $report])
            ->with('success', 'Laporan progress berhasil diperbarui.');
    }

    public function destroy(Project $project, ProgressReport $report)
    {
        $this->authorize('progress.delete');

        if (! $report->canDelete) {
            return redirect()
                ->route('projects.progress.index', $project)
                ->with('error', "Laporan dengan status '{$report->status_label}' tidak dapat dihapus.");
        }

        $this->service->delete($report, $project);

        return redirect()
            ->route('projects.progress.index', $project)->with('success', 'Laporan progress berhasil dihapus.');
    }

    // ========================
    // Workflow Actions
    // ========================

    public function submit(Project $project, ProgressReport $report): RedirectResponse
    {
        $this->authorize('progress.manage');

        try {
            $this->service->submit($report);
            $message = "Laporan {$report->report_code} berhasil diajukan untuk diverifikasi.";
        } catch (\Exception $e) {
            $message = "Gagal mengajukan: {$e->getMessage()}";
        }

        return redirect()
            ->route('projects.progress.index', $project)
            ->with('success', $message);
    }

    public function approve(Request $request, Project $project, ProgressReport $report): RedirectResponse
    {
        $this->authorize('progress.approve');

        $notes = $request->input('notes');
        $reviewerId = Auth::id();

        try {
            $this->service->approve($report, $reviewerId, $notes);
            $message = "Laporan {$report->report_code} berhasil diverifikasi.";
        } catch (\Exception $e) {
            $message = "Gagal memverifikasi: {$e->getMessage()}";
        }

        return redirect()
            ->route('projects.progress.index', $project)
            ->with('success', $message);
    }

    public function reject(Request $request, Project $project, ProgressReport $report): RedirectResponse
    {
        $this->authorize('progress.approve');

        $validated = $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        $reviewerId = Auth::id();

        try {
            $this->service->reject($report, $reviewerId, $validated['notes'] ?? null);
            $message = "Laporan {$report->report_code} ditolak dan dikembalikan untuk revisi.";
        } catch (\Exception $e) {
            $message = "Gagal menolak: {$e->getMessage()}";
        }

        return redirect()
            ->route('projects.progress.index', $project)
            ->with('success', $message);
    }

    public function publish(Project $project, ProgressReport $report): RedirectResponse
    {
        $this->authorize('progress.publish');

        try {
            $this->service->publish($report, Auth::id());
            $message = "Laporan {$report->report_code} berhasil dipublikasikan.";
        } catch (\Exception $e) {
            $message = "Gagal memublikasikan: {$e->getMessage()}";
        }

        return redirect()
            ->route('projects.progress.index', $project)
            ->with('success', $message);
    }

    public function exportPdf(Project $project, ProgressReport $report)
    {
        $this->authorize('progress.view');
        $report->load(['rabItem.section', 'reporter', 'reviewer', 'rejector', 'publisher']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('projects.progress.pdf', [
            'project' => $project,
            'report' => $report,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, "Laporan_Harian_{$project->code}_{$report->report_date->format('Ymd')}.pdf");
    }
}
