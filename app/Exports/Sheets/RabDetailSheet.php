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

        // Title rows
        $rows[] = ['RENCANA ANGGARAN BIAYA', '', '', '', '', ''];
        $rows[] = [$this->project->name, '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];
        $rows[] = ['Kegiatan        : ' . $this->project->name, '', '', '', '', ''];
        $rows[] = ['Lokasi/Wilayah  : ' . ($this->project->location ?? '-'), '', '', '', '', ''];
        $rows[] = ['Tahun Pengerjaan: ' . ($this->project->start_date ? $this->project->start_date->format('Y') : date('Y')), '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];

        // Header
        $rows[] = ['NO', 'URAIAN PEKERJAAN', 'VOLUME', 'SATUAN', 'HARGA SATUAN (RP)', 'JUMLAH HARGA (RP)'];
        $this->rowCount = 8;

        // Sections
        $letters = range('A', 'Z');
        $letterIndex = 0;

        foreach ($this->sections as $section) {
            $letter = $letters[$letterIndex] ?? ($letterIndex + 1);

            // Render section with its items and children
            $this->renderSection($rows, $section, $letter, 0);

            // Section subtotal
            $this->rowCount++;
            $this->subtotalRows[] = $this->rowCount;
            $rows[] = ['', '', '', '', 'JUMLAH ' . $letter, $this->formatNumber($section->total_price)];

            $letterIndex++;
        }

        // Grand total
        $this->rowCount++;
        $rows[] = ['', '', '', '', 'TOTAL', $this->formatNumber($this->grandTotal)];

        return $rows;
    }

    protected function renderSection(array &$rows, $section, $prefix, int $level): void
    {
        $indent = str_repeat('   ', $level);

        // Section header
        $this->rowCount++;
        $this->sectionRows[] = $this->rowCount;
        $rows[] = [$prefix, $indent . strtoupper($section->name), '', '', '', ''];

        // Items in this section
        $itemNo = 1;
        $items = $section->items ?? collect();
        foreach ($items->sortBy('code', SORT_NATURAL) as $item) {
            $this->rowCount++;
            $this->itemRows[] = $this->rowCount;
            $rows[] = [
                $itemNo,
                $indent . '   ' . $item->work_name,
                $this->formatNumber($item->volume),
                $item->unit,
                $this->formatNumber($item->unit_price),
                $this->formatNumber($item->total_price),
            ];
            $itemNo++;
        }

        // Child sections
        $children = $section->children ?? collect();
        $childIndex = 1;
        foreach ($children->sortBy('code', SORT_NATURAL) as $childSection) {
            $childPrefix = $prefix . '.' . $childIndex;
            $this->renderSection($rows, $childSection, $childPrefix, $level + 1);
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
        return [
            'A' => 8,
            'B' => 50,
            'C' => 12,
            'D' => 10,
            'E' => 18,
            'F' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

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
            $sheet->getStyle("A{$i}:F{$i}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);

            // Right align number columns
            $sheet->getStyle("C{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("E{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("F{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("D{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Style section headers
        foreach ($this->sectionRows as $row) {
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8E8E8'],
                ],
            ]);
        }

        // Style subtotal rows
        foreach ($this->subtotalRows as $row) {
            $sheet->getStyle("E{$row}:F{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFFFCC'],
                ],
            ]);
        }

        // Style grand total row
        $sheet->getStyle("E{$highestRow}:F{$highestRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9EDF7'],
            ],
        ]);

        return $styles;
    }
}
