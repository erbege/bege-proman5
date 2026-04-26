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

class RabAhspSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    protected Project $project;
    protected $ahspItems;
    protected int $rowCount = 0;
    protected array $itemHeaderRows = [];
    protected array $categoryRows = [];
    protected array $priceRows = [];

    public function __construct(Project $project, $ahspItems)
    {
        $this->project = $project;
        $this->ahspItems = $ahspItems;
    }

    public function array(): array
    {
        $rows = [];

        // Title rows
        $rows[] = ['ANALISA HARGA SATUAN PEKERJAAN', '', '', '', '', ''];
        $rows[] = [$this->project->name, '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];
        $this->rowCount = 3;

        foreach ($this->ahspItems as $item) {
            $workType = $item->ahspWorkType;
            $hasAhspData = $workType && $workType->components->isNotEmpty();

            // Item header
            $this->rowCount++;
            $this->itemHeaderRows[] = $this->rowCount;
            $rows[] = [$item->section?->name . ' - ' . $item->work_name, '', '', '', '', ''];

            $this->rowCount++;
            $rows[] = ['Kode: ' . ($workType?->code ?? $item->code) . ' | Satuan: ' . $item->unit . ' | Volume: ' . $this->formatNumber($item->volume), '', '', '', '', ''];

            // Component header
            $this->rowCount++;
            $rows[] = ['NO', 'URAIAN', 'KOEFISIEN', 'SATUAN', 'HARGA SATUAN', 'JUMLAH'];

            if ($hasAhspData) {
                $materials = $workType->components->where('component_type', 'material');
                $labor = $workType->components->where('component_type', 'labor');
                $equipment = $workType->components->where('component_type', 'equipment');

                // Materials
                if ($materials->isNotEmpty()) {
                    $this->rowCount++;
                    $this->categoryRows[] = $this->rowCount;
                    $rows[] = ['A. BAHAN', '', '', '', '', ''];

                    $no = 1;
                    foreach ($materials as $comp) {
                        $unitPrice = $comp->getPrice('DEFAULT');
                        $amount = $comp->coefficient * $unitPrice;
                        $this->rowCount++;
                        $rows[] = [
                            $no,
                            $comp->name,
                            $this->formatNumber($comp->coefficient, 4),
                            $comp->unit,
                            $unitPrice > 0 ? $this->formatNumber($unitPrice) : '-',
                            $amount > 0 ? $this->formatNumber($amount) : '-',
                        ];
                        $no++;
                    }
                }

                // Labor
                if ($labor->isNotEmpty()) {
                    $this->rowCount++;
                    $this->categoryRows[] = $this->rowCount;
                    $rows[] = ['B. TENAGA KERJA', '', '', '', '', ''];

                    $no = 1;
                    foreach ($labor as $comp) {
                        $unitPrice = $comp->getPrice('DEFAULT');
                        $amount = $comp->coefficient * $unitPrice;
                        $this->rowCount++;
                        $rows[] = [
                            $no,
                            $comp->name,
                            $this->formatNumber($comp->coefficient, 4),
                            $comp->unit,
                            $unitPrice > 0 ? $this->formatNumber($unitPrice) : '-',
                            $amount > 0 ? $this->formatNumber($amount) : '-',
                        ];
                        $no++;
                    }
                }

                // Equipment
                if ($equipment->isNotEmpty()) {
                    $this->rowCount++;
                    $this->categoryRows[] = $this->rowCount;
                    $rows[] = ['C. PERALATAN', '', '', '', '', ''];

                    $no = 1;
                    foreach ($equipment as $comp) {
                        $unitPrice = $comp->getPrice('DEFAULT');
                        $amount = $comp->coefficient * $unitPrice;
                        $this->rowCount++;
                        $rows[] = [
                            $no,
                            $comp->name,
                            $this->formatNumber($comp->coefficient, 4),
                            $comp->unit,
                            $unitPrice > 0 ? $this->formatNumber($unitPrice) : '-',
                            $amount > 0 ? $this->formatNumber($amount) : '-',
                        ];
                        $no++;
                    }
                }
            } else {
                $this->rowCount++;
                $rows[] = ['Data analisa AHSP tidak tersedia (item tidak terhubung dengan AHSP)', '', '', '', '', ''];
            }

            // Unit price total
            $this->rowCount++;
            $this->priceRows[] = $this->rowCount;
            $rows[] = ['', '', '', '', 'HARGA SATUAN', $this->formatNumber($item->unit_price)];

            $this->rowCount++;
            $this->priceRows[] = $this->rowCount;
            $rows[] = ['', '', '', '', 'JUMLAH (' . $this->formatNumber($item->volume) . ' x ' . $this->formatNumber($item->unit_price) . ')', $this->formatNumber($item->total_price)];

            // Spacing
            $this->rowCount++;
            $rows[] = ['', '', '', '', '', ''];
        }

        if ($this->ahspItems->isEmpty()) {
            $this->rowCount++;
            $rows[] = ['Tidak ada item pekerjaan dalam RAB ini', '', '', '', '', ''];
        }

        return $rows;
    }

    protected function formatNumber($value, int $decimals = 2): string
    {
        return number_format($value, $decimals, ',', '.');
    }

    public function title(): string
    {
        return 'ANALISA HARGA';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 40,
            'C' => 12,
            'D' => 10,
            'E' => 18,
            'F' => 18,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        $styles = [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];

        // Style item headers
        foreach ($this->itemHeaderRows as $row) {
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8E8E8'],
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
        }

        // Style category rows
        foreach ($this->categoryRows as $row) {
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'font' => ['bold' => true, 'italic' => true],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
        }

        // Style price rows
        foreach ($this->priceRows as $row) {
            $sheet->getStyle("E{$row}:F{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFFFCC'],
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
        }

        return $styles;
    }
}
