<?php

namespace App\Imports;

use App\Models\AhspCategory;
use App\Models\AhspComponent;
use App\Models\AhspWorkType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Import AHSP Work Types from Excel (Permen PUPR format)
 * 
 * Expected structure:
 * Index sheet: NO | URAIAN PEKERJAAN | SATUAN | HARGA SATUAN | KETERANGAN
 * Detail sheets: One per work type with component breakdown
 * 
 * Or single sheet with all data
 */
class AhspWorkTypeImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    protected ?AhspCategory $currentCategory = null;
    protected array $categoryMap = [];
    protected int $importedCount = 0;
    protected string $source;
    protected string $reference;

    public function __construct(string $source = 'PUPR', string $reference = '')
    {
        $this->source = $source;
        $this->reference = $reference;
    }

    public function collection(Collection $rows)
    {
        $currentWorkType = null;
        $currentComponentType = 'material';

        foreach ($rows as $row) {
            // Get NO/code column
            $no = $this->getValue($row, ['no', 'kode', 'code']);
            $uraian = $this->getValue($row, ['uraian', 'uraian_pekerjaan', 'description', 'nama']);

            if (!$no && !$uraian) {
                continue;
            }

            // Detect what type of row this is based on NO format
            $level = $this->detectLevel($no);

            // Check if this is a component type header (TENAGA KERJA, BAHAN, PERALATAN)
            if ($this->isComponentTypeHeader($no, $uraian)) {
                $currentComponentType = $this->detectComponentType($uraian);
                continue;
            }

            // Check if this is a work type row (has unit and/or price)
            $satuan = $this->getValue($row, ['satuan', 'unit', 'sat']);
            $harga = $this->getNumericValue($row, ['harga_satuan', 'harga', 'price']);
            $koefisien = $this->getNumericValue($row, ['koefisien', 'coefficient', 'coef', 'koef']);

            if ($level <= 3 && $satuan && !$koefisien) {
                // This is a category or work type definition
                if ($level < 3) {
                    // Category (1., 1.1, 1.1.1)
                    $this->createOrGetCategory($no, $uraian, $level);
                } else {
                    // Work type (1.1.1.1)
                    $currentWorkType = $this->createWorkType($no, $uraian, $satuan, $harga);
                    $this->importedCount++;
                }
            } elseif ($koefisien > 0 && $currentWorkType) {
                // This is a component row
                $this->createComponent($currentWorkType, $currentComponentType, $no, $uraian, $satuan, $koefisien);
            }
        }
    }

    protected function detectLevel(?string $no): int
    {
        if (!$no)
            return 0;

        // Count dots in the number
        $dots = substr_count($no, '.');

        // Remove trailing dot if exists
        $no = rtrim($no, '.');

        // Also check by segment count
        $segments = explode('.', $no);

        return max($dots, count($segments));
    }

    protected function isComponentTypeHeader(?string $no, ?string $uraian): bool
    {
        if (!$uraian)
            return false;

        $uraian = strtoupper(trim($uraian));
        $no = strtoupper(trim($no ?? ''));

        return $no === 'A' || $no === 'B' || $no === 'C' ||
            str_contains($uraian, 'TENAGA KERJA') ||
            str_contains($uraian, 'BAHAN') ||
            str_contains($uraian, 'PERALATAN') ||
            str_contains($uraian, 'JUMLAH HARGA');
    }

    protected function detectComponentType(?string $uraian): string
    {
        if (!$uraian)
            return 'material';

        $uraian = strtoupper(trim($uraian));

        if (str_contains($uraian, 'TENAGA KERJA') || str_contains($uraian, 'UPAH')) {
            return 'labor';
        }
        if (str_contains($uraian, 'PERALATAN') || str_contains($uraian, 'ALAT')) {
            return 'equipment';
        }
        return 'material';
    }

    protected function createOrGetCategory(string $code, string $name, int $level): AhspCategory
    {
        $parentId = null;

        // Find parent based on code structure
        if ($level > 1) {
            $parts = explode('.', rtrim($code, '.'));
            array_pop($parts);
            $parentCode = implode('.', $parts);

            if (isset($this->categoryMap[$parentCode])) {
                $parentId = $this->categoryMap[$parentCode]->id;
            }
        }

        $category = AhspCategory::firstOrCreate(
            ['code' => rtrim($code, '.')],
            [
                'name' => $name,
                'parent_id' => $parentId,
                'level' => $level - 1,
                'sort_order' => count($this->categoryMap),
            ]
        );

        $this->categoryMap[rtrim($code, '.')] = $category;
        $this->currentCategory = $category;

        return $category;
    }

    protected function createWorkType(string $code, string $name, string $unit, float $harga): AhspWorkType
    {
        // Find parent category
        $parts = explode('.', rtrim($code, '.'));
        array_pop($parts);
        $parentCode = implode('.', $parts);

        $categoryId = isset($this->categoryMap[$parentCode])
            ? $this->categoryMap[$parentCode]->id
            : ($this->currentCategory?->id ?? $this->getDefaultCategory()->id);

        return AhspWorkType::firstOrCreate(
            ['code' => rtrim($code, '.')],
            [
                'ahsp_category_id' => $categoryId,
                'name' => $name,
                'unit' => $unit,
                'source' => $this->source,
                'reference' => $this->reference,
                'overhead_percentage' => 10,
            ]
        );
    }

    protected function createComponent(
        AhspWorkType $workType,
        string $type,
        ?string $code,
        ?string $name,
        ?string $unit,
        float $coefficient
    ): void {
        if (!$name)
            return;

        AhspComponent::firstOrCreate(
            [
                'ahsp_work_type_id' => $workType->id,
                'name' => $name,
            ],
            [
                'component_type' => $type,
                'code' => $code,
                'unit' => $unit ?? '-',
                'coefficient' => $coefficient,
                'sort_order' => $workType->components()->count(),
            ]
        );
    }

    protected function getDefaultCategory(): AhspCategory
    {
        return AhspCategory::firstOrCreate(
            ['code' => 'DEFAULT'],
            [
                'name' => 'Umum',
                'level' => 0,
                'sort_order' => 0,
            ]
        );
    }

    protected function getValue(Collection $row, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            if (isset($row[$key]) && !empty($row[$key])) {
                return trim((string) $row[$key]);
            }
        }
        return null;
    }

    protected function getNumericValue(Collection $row, array $possibleKeys): float
    {
        foreach ($possibleKeys as $key) {
            if (isset($row[$key]) && is_numeric($row[$key])) {
                return (float) $row[$key];
            }
        }
        return 0;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }
}
