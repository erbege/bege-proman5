<?php

namespace App\Http\Controllers\Portal\Owner;

use App\Http\Controllers\Controller;
use App\Models\MonthlyReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class MonthlyReportController extends Controller
{
    /**
     * Display the specified monthly report for the owner
     */
    public function show(MonthlyReport $report)
    {
        $report->load(['project', 'creator']);

        // Security checks
        if ($report->project->owner_id != Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }

        if ($report->status !== 'published') {
            abort(403, 'Laporan ini belum dipublikasikan untuk Owner.');
        }

        return view('portal.owner.monthly-reports.show', compact('report'));
    }

    /**
     * Export monthly report to PDF for the owner
     */
    public function exportPdf(MonthlyReport $report)
    {
        $report->load(['project', 'creator']);

        // Security check
        if ($report->project->owner_id != Auth::id()) {
            abort(403);
        }

        if ($report->status !== 'published') {
            abort(403);
        }

        $pdf = Pdf::loadView('projects.monthly-reports.pdf', [
            'project' => $report->project,
            'report' => $report,
        ]);

        $pdf->setPaper('a4', 'landscape');
        $filename = "monthly-report-{$report->project->code}-month-{$report->month_number}.pdf";

        return $pdf->download($filename);
    }
}
