<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TimeScheduleSummarySheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected Project $project;
    protected $schedules;

    public function __construct(Project $project, $schedules)
    {
        $this->project = $project;
        $this->schedules = $schedules;
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function headings(): array
    {
        return [
            'Minggu',
            'Periode',
            'Rencana (%)',
            'Realisasi (%)',
            'Kum. Rencana (%)',
            'Kum. Realisasi (%)',
            'Deviasi (%)',
        ];
    }

    public function array(): array
    {
        $data = [];
        foreach ($this->schedules as $schedule) {
            $data[] = [
                $schedule->week_label,
                $schedule->week_start->format('d M') . ' - ' . $schedule->week_end->format('d M Y'),
                number_format($schedule->planned_weight, 2),
                number_format($schedule->actual_weight, 2),
                number_format($schedule->planned_cumulative, 2),
                number_format($schedule->actual_cumulative, 2),
                ($schedule->deviation > 0 ? '+' : '') . number_format($schedule->deviation, 2),
            ];
        }
        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 25,
            'C' => 15,
            'D' => 15,
            'E' => 18,
            'F' => 18,
            'G' => 12,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->schedules) + 1;

        return [
            // Header style
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D4A574'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // All cells border
            "A1:G{$lastRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }
}
