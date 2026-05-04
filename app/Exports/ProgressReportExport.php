<?php

namespace App\Exports;

use App\Models\ProgressReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProgressReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected int $projectId;

    public function __construct(int $projectId)
    {
        $this->projectId = $projectId;
    }

    public function collection()
    {
        return ProgressReport::with(['rabItem', 'reporter'])
            ->where('project_id', $this->projectId)
            ->whereNotIn('status', [ProgressReport::STATUS_REJECTED, ProgressReport::STATUS_DRAFT])
            ->orderBy('report_date', 'asc')
            ->get();
    }

    public function map($report): array
    {
        return [
            $report->report_date->format('Y-m-d'),
            $report->report_code,
            $report->rabItem ? $report->rabItem->work_name : 'Umum',
            $report->progress_percentage . '%',
            $report->cumulative_progress . '%',
            $report->weather_label,
            $report->workers_count,
            $report->safety_incident_count,
            $report->status_label,
            $report->reporter ? $report->reporter->name : '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kode Laporan',
            'Pekerjaan (RAB)',
            'Progress Harian',
            'Progress Kumulatif',
            'Cuaca',
            'Jml Pekerja',
            'Insiden K3',
            'Status',
            'Pelapor',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
