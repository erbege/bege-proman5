<?php

namespace App\Livewire;

use App\Models\Project;
use App\Services\ProgressReportService;
use App\Services\ScheduleCalculator;
use Livewire\Component;

class ProgressDashboard extends Component
{
    public Project $project;

    public float $variance = 0.0;
    public float $productivityIndex = 0.0;
    public float $safetyScore = 0.0;

    // S-Curve data
    public array $scurveData = [];
    public array $scurveSummary = [];
    public bool $scurveHasData = false;

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->calculateKPIs();
        $this->loadScurveData();
    }

    protected function calculateKPIs()
    {
        /** @var ProgressReportService $service */
        $service = app(ProgressReportService::class);

        $this->variance = $service->calculateProgressVariance($this->project);
        $this->productivityIndex = $service->calculateProductivityIndex($this->project);
        $this->safetyScore = $service->calculateSafetyScore($this->project);
    }

    protected function loadScurveData()
    {
        /** @var ScheduleCalculator $calculator */
        $calculator = app(ScheduleCalculator::class);

        $data = $calculator->getEnhancedScurveData($this->project);

        $this->scurveHasData = $data['hasData'];
        $this->scurveSummary = $data['summary'];

        // Store chart-ready data (labels + datasets) for JavaScript
        $this->scurveData = [
            'labels' => $data['labels'],
            'planned' => $data['planned'],
            'actual' => $data['actual'],
            'projected' => $data['projected'],
            'deviation' => $data['deviation'],
            'currentWeekIndex' => $data['currentWeekIndex'],
        ];
    }

    public function refreshScurve()
    {
        /** @var ScheduleCalculator $calculator */
        $calculator = app(ScheduleCalculator::class);

        // Force regenerate schedule from latest progress data
        $calculator->updateFromProgress($this->project);

        // Reload data
        $this->loadScurveData();
        $this->calculateKPIs();
    }

    public function exportExcel()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ProgressReportExport($this->project->id), 
            'Progress_Report_' . $this->project->code . '_' . date('Ymd') . '.xlsx'
        );
    }

    public function exportPdf()
    {
        $reports = \App\Models\ProgressReport::with(['rabItem', 'reporter'])
            ->where('project_id', $this->project->id)
            ->whereNotIn('status', [\App\Models\ProgressReport::STATUS_REJECTED, \App\Models\ProgressReport::STATUS_DRAFT])
            ->orderBy('report_date', 'asc')
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.progress_reports', [
            'project' => $this->project,
            'reports' => $reports
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'Progress_Report_' . $this->project->code . '_' . date('Ymd') . '.pdf');
    }

    public function render()
    {
        return view('livewire.progress-dashboard');
    }
}
