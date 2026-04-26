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

class RabRecapSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    protected Project $project;
    protected $sections;
    protected $grandTotal;
    protected int $headerRow = 5;
    protected int $dataStartRow = 6;
    protected int $totalRow = 0;

    public function __construct(Project $project, $sections, $grandTotal)
    {
        $this->project = $project;
        $this->sections = $sections;
        $this->grandTotal = $grandTotal;
    }

    public function array(): array
    {
        $rows = [];

        // Title
        $rows[] = ['DAFTAR REKAPITULASI RENCANA ANGGARAN BIAYA (RAB)', '', ''];
        $rows[] = ['', '', ''];

        // Project info
        $year = $this->project->start_date ? $this->project->start_date->format('Y') : date('Y');
        $rows[] = ['PEKERJAAN', ': ' . strtoupper($this->project->name), ''];
        $rows[] = ['TAHUN ANGGARAN', ': ' . $year, ''];

        // Header
        $rows[] = ['NO', 'URAIAN PEKERJAAN', 'JUMLAH HARGA'];
        $this->headerRow = 5;
        $this->dataStartRow = 6;

        // Sections with Roman numerals
        $romanNumerals = [
            'I',
            'II',
            'III',
            'IV',
            'V',
            'VI',
            'VII',
            'VIII',
            'IX',
            'X',
            'XI',
            'XII',
            'XIII',
            'XIV',
            'XV',
            'XVI',
            'XVII',
            'XVIII',
            'XIX',
            'XX'
        ];
        $index = 0;
        $sectionLabels = [];

        foreach ($this->sections as $section) {
            $roman = $romanNumerals[$index] ?? ($index + 1);
            $sectionLabels[] = $roman;
            $rows[] = [$roman, strtoupper($section->name), $this->formatNumber($section->total_price)];
            $index++;
        }

        // Calculate totals
        $subtotal = $this->grandTotal;
        $ppn = $subtotal * 0.10; // PPN 10%
        $grandTotalWithPpn = $subtotal + $ppn;
        $grandTotalRounded = round($grandTotalWithPpn / 1000) * 1000; // Round to nearest thousand

        // Build labels string
        $labelsString = implode(' + ', $sectionLabels);

        // Totals section
        $rows[] = ['', 'JUMLAH TOTAL ( ' . $labelsString . ' )', $this->formatNumber($subtotal)];
        $rows[] = ['', 'PPN 10%', $this->formatNumber($ppn)];
        $rows[] = ['', 'GRAND TOTAL', $this->formatNumber($grandTotalWithPpn)];
        $rows[] = ['', 'GRAND TOTAL DIBULATKAN', $this->formatNumber($grandTotalRounded)];

        // Terbilang
        $rows[] = ['', '', ''];
        $rows[] = ['Terbilang', ': ' . $this->terbilang($grandTotalRounded) . ' rupiah', ''];

        $this->totalRow = 5 + $index + 4; // header + sections + 4 total rows

        return $rows;
    }

    protected function formatNumber($value): string
    {
        return number_format($value, 2, ',', '.');
    }

    protected function terbilang($angka): string
    {
        $angka = abs($angka);
        $bilangan = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($angka < 12) {
            return $bilangan[$angka];
        } elseif ($angka < 20) {
            return $this->terbilang($angka - 10) . ' belas';
        } elseif ($angka < 100) {
            return $this->terbilang(floor($angka / 10)) . ' puluh ' . $this->terbilang($angka % 10);
        } elseif ($angka < 200) {
            return 'seratus ' . $this->terbilang($angka - 100);
        } elseif ($angka < 1000) {
            return $this->terbilang(floor($angka / 100)) . ' ratus ' . $this->terbilang($angka % 100);
        } elseif ($angka < 2000) {
            return 'seribu ' . $this->terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            return $this->terbilang(floor($angka / 1000)) . ' ribu ' . $this->terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            return $this->terbilang(floor($angka / 1000000)) . ' juta ' . $this->terbilang($angka % 1000000);
        } elseif ($angka < 1000000000000) {
            return $this->terbilang(floor($angka / 1000000000)) . ' milyar ' . $this->terbilang($angka % 1000000000);
        } elseif ($angka < 1000000000000000) {
            return $this->terbilang(floor($angka / 1000000000000)) . ' triliun ' . $this->terbilang($angka % 1000000000000);
        }

        return trim(preg_replace('/\s+/', ' ', $this->terbilang($angka)));
    }

    public function title(): string
    {
        return 'REKAPITULASI RAB';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 60,
            'C' => 25,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $sectionCount = count($this->sections);

        $styles = [
            // Title
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Project info
            3 => ['font' => ['bold' => false]],
            4 => ['font' => ['bold' => false]],
            // Header row
            5 => [
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

        // Apply borders to all data rows
        for ($i = 6; $i <= (5 + $sectionCount + 4); $i++) {
            $sheet->getStyle("A{$i}:C{$i}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
            // Right align column C (numbers)
            $sheet->getStyle("C{$i}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // Style total rows
        $totalStartRow = 6 + $sectionCount;
        for ($i = $totalStartRow; $i <= $totalStartRow + 3; $i++) {
            $sheet->getStyle("B{$i}")->getFont()->setBold(true);
            $sheet->getStyle("C{$i}")->getFont()->setBold(true);
        }

        // GRAND TOTAL row highlight
        $grandTotalRow = $totalStartRow + 3;
        $sheet->getStyle("A{$grandTotalRow}:C{$grandTotalRow}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFCC'],
            ],
        ]);

        // Terbilang row
        $terbilangRow = $grandTotalRow + 2;
        $sheet->getStyle("A{$terbilangRow}")->getFont()->setBold(true);

        // Merge title cell
        $sheet->mergeCells('A1:C1');

        return $styles;
    }
}
