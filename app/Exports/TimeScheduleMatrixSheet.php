<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TimeScheduleMatrixSheet implements FromArray, WithTitle, WithStyles, WithEvents
{
    protected Project $project;
    protected $schedules;
    protected $rabSections;
    protected int $totalWeeks;
    protected array $months;

    public function __construct(Project $project, $schedules, $rabSections)
    {
        $this->project = $project;
        $this->schedules = $schedules;
        $this->rabSections = $rabSections;
        $this->calculateWeeks();
    }

    protected function calculateWeeks(): void
    {
        $startDate = $this->project->start_date;
        $endDate = $this->project->end_date;
        $this->totalWeeks = max(1, (int) ceil($startDate->diffInDays($endDate) / 7));

        $this->months = [];
        for ($w = 0; $w < $this->totalWeeks; $w++) {
            $weekDate = $startDate->copy()->addWeeks($w);
            $monthKey = $weekDate->format('M-Y');
            if (!isset($this->months[$monthKey])) {
                $this->months[$monthKey] = ['label' => $weekDate->format('M Y'), 'weeks' => []];
            }
            $this->months[$monthKey]['weeks'][] = [
                'num' => $w + 1,
                'date' => $weekDate->format('d'),
                'full' => $weekDate
            ];
        }
    }

    public function title(): string
    {
        return 'Time Schedule';
    }

    public function array(): array
    {
        $data = [];
        $startDate = $this->project->start_date;
        $sectionLetters = range('A', 'Z');
        $sectionIndex = 0;

        // Header rows - Month and Week
        $monthRow = ['NO', 'URAIAN PEKERJAAN', 'BOBOT %'];
        $weekRow = ['', '', ''];

        foreach ($this->months as $monthData) {
            foreach ($monthData['weeks'] as $i => $week) {
                $monthRow[] = ($i === 0) ? $monthData['label'] : '';
                $weekRow[] = 'M' . $week['num'];
            }
        }
        $data[] = $monthRow;
        $data[] = $weekRow;

        // Recursive processing of sections
        foreach ($this->rabSections as $section) {
            $this->processSection($section, $data, 0);
        }

        // Footer rows
        $footerLabels = [
            'Rencana Mingguan (%)',
            'Rencana Kumulatif (%)',
            'Realisasi Mingguan (%)',
            'Realisasi Kumulatif (%)',
            'Deviasi (%)',
        ];

        foreach ($footerLabels as $label) {
            $row = ['', $label, ''];
            $scheduleArray = $this->schedules->toArray();
            for ($w = 0; $w < $this->totalWeeks; $w++) {
                $schedule = $scheduleArray[$w] ?? null;
                if ($schedule) {
                    $value = match ($label) {
                        'Rencana Mingguan (%)' => number_format($schedule['planned_weight'], 1),
                        'Rencana Kumulatif (%)' => number_format($schedule['planned_cumulative'], 1),
                        'Realisasi Mingguan (%)' => number_format($schedule['actual_weight'], 1),
                        'Realisasi Kumulatif (%)' => number_format($schedule['actual_cumulative'], 1),
                        'Deviasi (%)' => ($schedule['deviation'] > 0 ? '+' : '') . number_format($schedule['deviation'], 1),
                        default => ''
                    };
                    $row[] = $value;
                } else {
                    $row[] = '';
                }
            }
            $data[] = $row;
        }

        return $data;
    }

    protected function processSection($section, &$data, $level)
    {
        // No visual indentation as requested

        // Section Row
        $sectionRow = [
            $section->code,
            $section->name,
            number_format($section->weight_percentage, 1)
        ];

        for ($w = 0; $w < $this->totalWeeks; $w++) {
            $sectionRow[] = '';
        }
        $data[] = $sectionRow;

        // Process Items
        foreach ($section->items as $item) {
            $itemRow = [
                $item->code,
                $item->work_name,
                number_format($item->weight_percentage, 1)
            ];

            for ($w = 0; $w < $this->totalWeeks; $w++) {
                $weekDate = $this->project->start_date->copy()->addWeeks($w);
                $weekEnd = $weekDate->copy()->addDays(6);
                $isPlanned = $item->planned_start && $item->planned_end &&
                    !($item->planned_end < $weekDate || $item->planned_start > $weekEnd);
                $itemRow[] = $isPlanned ? '■' : '';
            }
            $data[] = $itemRow;
        }

        // Recursive Children
        foreach ($section->recursiveChildren as $childSection) {
            $this->processSection($childSection, $data, $level + 1);
        }
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = $this->getColLetter(3 + $this->totalWeeks);

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
            2 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D4A574'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Left align 'NO' column (A) for all rows except headers
            'A3:A' . $sheet->getHighestRow() => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = $this->getColLetter(3 + $this->totalWeeks);
                $lastRow = $sheet->getHighestRow();

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(35);
                $sheet->getColumnDimension('C')->setWidth(10);
                for ($i = 4; $i <= 3 + $this->totalWeeks; $i++) {
                    $sheet->getColumnDimension($this->getColLetter($i))->setWidth(6);
                }

                // Apply borders
                $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Center align week columns
                $sheet->getStyle("D1:{$lastCol}{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }

    protected function getColLetter(int $num): string
    {
        $letter = '';
        while ($num > 0) {
            $num--;
            $letter = chr(65 + ($num % 26)) . $letter;
            $num = intdiv($num, 26);
        }
        return $letter;
    }
}
