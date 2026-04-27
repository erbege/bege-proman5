<?php

namespace App\Exports\Sheets;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RabDetailSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    protected Project $project;
    protected $sections;
    protected $grandTotal;
    protected int $rowCount = 0;
    protected array $sectionRows = [];
    protected array $itemRows = [];
    protected array $subtotalRows = [];

    public function __construct(Project $project, $sections, $grandTotal)
    {
        $this->project = $project;
        $this->sections = $sections;
        $this->grandTotal = $grandTotal;
    }

    public function array(): array
    {
        $rows = [];
        $canViewFinancials = auth()->user()->can('financials.view');
        $colCount = $canViewFinancials ? 6 : 4;
        $emptyRow = array_fill(0, $colCount, '');

        // Title rows
        $rows[] = array_merge(['RENCANA ANGGARAN BIAYA'], array_fill(0, $colCount - 1, ''));
        $rows[] = array_merge([$this->project->name], array_fill(0, $colCount - 1, ''));
        $rows[] = $emptyRow;
        $rows[] = array_merge(['Kegiatan        : ' . $this->project->name], array_fill(0, $colCount - 1, ''));
        $rows[] = array_merge(['Lokasi/Wilayah  : ' . ($this->project->location ?? '-')], array_fill(0, $colCount - 1, ''));
        $rows[] = array_merge(['Tahun Pengerjaan: ' . ($this->project->start_date ? $this->project->start_date->format('Y') : date('Y'))], array_fill(0, $colCount - 1, ''));
        $rows[] = $emptyRow;

        // Header
        $header = ['NO', 'URAIAN PEKERJAAN', 'VOLUME', 'SATUAN'];
        if ($canViewFinancials) {
            $header[] = 'HARGA SATUAN (RP)';
            $header[] = 'JUMLAH HARGA (RP)';
        }
        $rows[] = $header;
        $this->rowCount = 8;

        // Sections
        $letters = range('A', 'Z');
        $letterIndex = 0;

        foreach ($this->sections as $section) {
            $letter = $letters[$letterIndex] ?? ($letterIndex + 1);

            // Render section with its items and children
            $this->renderSection($rows, $section, $letter, 0, $canViewFinancials);

            // Section subtotal
            $this->rowCount++;
            $this->subtotalRows[] = $this->rowCount;
            
            $subtotalRow = ['', '', '', ''];
            if ($canViewFinancials) {
                $subtotalRow[] = 'JUMLAH ' . $letter;
                $subtotalRow[] = $this->formatNumber($section->total_price);
            } else {
                $subtotalRow[1] = 'JUMLAH ' . $letter;
            }
            $rows[] = $subtotalRow;

            $letterIndex++;
        }

        // Grand total
        $this->rowCount++;
        $totalRow = ['', '', '', ''];
        if ($canViewFinancials) {
            $totalRow[] = 'TOTAL';
            $totalRow[] = $this->formatNumber($this->grandTotal);
        } else {
            $totalRow[1] = 'TOTAL';
        }
        $rows[] = $totalRow;

        return $rows;
    }

    protected function renderSection(array &$rows, $section, $prefix, int $level, bool $canViewFinancials): void
    {
        $indent = str_repeat('   ', $level);
        $colCount = $canViewFinancials ? 6 : 4;

        // Section header
        $this->rowCount++;
        $this->sectionRows[] = $this->rowCount;
        $rows[] = array_merge([$prefix, $indent . strtoupper($section->name)], array_fill(0, $colCount - 2, ''));

        // Items in this section
        $itemNo = 1;
        $items = $section->items ?? collect();
        foreach ($items->sortBy('code', SORT_NATURAL) as $item) {
            $this->rowCount++;
            $this->itemRows[] = $this->rowCount;
            $row = [
                $itemNo,
                $indent . '   ' . $item->work_name,
                $this->formatNumber($item->volume),
                $item->unit,
            ];
            
            if ($canViewFinancials) {
                $row[] = $this->formatNumber($item->unit_price);
                $row[] = $this->formatNumber($item->total_price);
            }
            
            $rows[] = $row;
            $itemNo++;
        }

        // Child sections
        $children = $section->children ?? collect();
        $childIndex = 1;
        foreach ($children->sortBy('code', SORT_NATURAL) as $childSection) {
            $childPrefix = $prefix . '.' . $childIndex;
            $this->renderSection($rows, $childSection, $childPrefix, $level + 1, $canViewFinancials);
            $childIndex++;
        }
    }

    protected function formatNumber($value): string
    {
        return number_format($value, 2, ',', '.');
    }

    public function title(): string
    {
        return 'RINCIAN RAB';
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 8,
            'B' => 50,
            'C' => 12,
            'D' => 10,
        ];

        if (auth()->user()->can('financials.view')) {
            $widths['E'] = 18;
            $widths['F'] = 20;
        }

        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $canViewFinancials = auth()->user()->can('financials.view');
        $lastCol = $canViewFinancials ? 'F' : 'D';

        $styles = [
            // Title
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Header row
            8 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F0F0F0'],
                ],
            ],
        ];

        // Apply borders and alignment to data rows
        for ($i = 9; $i <= $highestRow; $i++) {
            $sheet->getStyle("A{$i}:{$lastCol}{$i}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);

            // Right align number columns
            $sheet->getStyle("C{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            if ($canViewFinancials) {
                $sheet->getStyle("E{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("F{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
            $sheet->getStyle("D{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Style section headers
        foreach ($this->sectionRows as $row) {
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8E8E8'],
                ],
            ]);
        }

        // Style subtotal rows
        foreach ($this->subtotalRows as $row) {
            $startCol = $canViewFinancials ? 'E' : 'B';
            $sheet->getStyle("{$startCol}{$row}:{$lastCol}{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFFFCC'],
                ],
            ]);
        }

        // Style grand total row
        $startCol = $canViewFinancials ? 'E' : 'B';
        $sheet->getStyle("{$startCol}{$highestRow}:{$lastCol}{$highestRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9EDF7'],
            ],
        ]);

        return $styles;
    }
}
