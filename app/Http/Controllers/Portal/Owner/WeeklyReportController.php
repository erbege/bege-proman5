<?php

namespace App\Http\Controllers\Portal\Owner;

use App\Http\Controllers\Controller;
use App\Models\WeeklyReport;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeeklyReportController extends Controller
{
    /**
     * Display the specified weekly report for the owner
     */
    public function show(WeeklyReport $report)
    {
        $report->load(['project', 'coverImage.latestVersion', 'creator', 'comments.user']);

        // Security checks
        if ($report->project->owner_id != Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }

        if ($report->status !== 'published') {
            abort(403, 'Laporan ini belum dipublikasikan untuk Owner.');
        }

        return view('portal.owner.weekly-reports.show', compact('report'));
    }

    /**
     * Export weekly report to PDF for the owner
     */
    public function exportPdf(WeeklyReport $report)
    {
        $report->load(['project', 'coverImage.latestVersion', 'creator']);

        // Security check
        if ($report->project->owner_id != Auth::id()) {
            abort(403);
        }

        if ($report->status !== 'published') {
            abort(403);
        }

        $pdf = Pdf::loadView('projects.weekly-reports.pdf', [
            'project' => $report->project,
            'report' => $report,
        ]);

        $pdf->setPaper('a4', 'landscape');
        $filename = "weekly-report-{$report->project->code}-week-{$report->week_number}.pdf";

        return $pdf->download($filename);
    }
}
