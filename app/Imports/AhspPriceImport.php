<?php

namespace App\Imports;

use App\Models\AhspBasePrice;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

/**
 * Import AHSP Base Prices from Excel
 * 
 * Expected columns:
 * - no: Nomor urut
 * - uraian/nama/name: Nama bahan/upah/alat
 * - kode/code: Kode komponen (L.01, M.01, etc)
 * - satuan/unit/sat: Satuan (OH, kg, m3, etc)
 * - harga/harga_satuan/price: Harga satuan
 * - tipe/type/jenis: labor/material/equipment (optional, auto-detect)
 */
class AhspPriceImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    protected string $regionCode;
    protected string $regionName;
    protected string $effectiveDate;
    protected int $importedCount = 0;

    public function __construct(string $regionCode, string $regionName, string $effectiveDate)
    {
        $this->regionCode = $regionCode;
        $this->regionName = $regionName;
        $this->effectiveDate = $effectiveDate;
    }

    public function collection(Collection $rows)
    {
        $currentType = 'material'; // Default type

        foreach ($rows as $row) {
            // Try to detect section headers for type
            $name = $this->getValue($row, ['uraian', 'nama', 'name', 'description']);

            // Detect type from section headers
            if ($this->isTypeHeader($name)) {
                $currentType = $this->detectType($name);
                continue;
            }

            // Get price - skip if no price (header row or empty)
            $price = $this->getNumericValue($row, ['harga', 'harga_satuan', 'price', 'harga_rp']);
            if ($price <= 0) {
                continue;
            }

            // Get other fields
            $code = $this->getValue($row, ['kode', 'code', 'no']);
            $unit = $this->getValue($row, ['satuan', 'unit', 'sat']);
            $type = $this->getValue($row, ['tipe', 'type', 'jenis']) ?? $currentType;
            $category = $this->getValue($row, ['kategori', 'category', 'group', 'kelompok']);

            // Normalize type
            $type = $this->normalizeType($type);

            if (!$name || !$unit) {
                continue;
            }

            // Create or update price
            AhspBasePrice::updateOrCreate(
                [
                    'name' => $name,
                    'region_code' => $this->regionCode,
                    'component_type' => $type,
                ],
                [
                    'code' => $code,
                    'category' => $category,
                    'unit' => $unit,
                    'region_name' => $this->regionName,
                    'price' => $price,
                    'effective_date' => $this->effectiveDate,
                    'source' => 'Import Excel',
                    'is_active' => true,
                ]
            );

            $this->importedCount++;
        }
    }

    protected function isTypeHeader(?string $name): bool
    {
        if (!$name)
            return false;

        $name = strtoupper(trim($name));
        return str_contains($name, 'TENAGA KERJA') ||
            str_contains($name, 'UPAH') ||
            str_contains($name, 'BAHAN') ||
            str_contains($name, 'MATERIAL') ||
            str_contains($name, 'PERALATAN') ||
            str_contains($name, 'ALAT');
    }

    protected function detectType(?string $name): string
    {
        if (!$name)
            return 'material';

        $name = strtoupper(trim($name));

        if (str_contains($name, 'TENAGA KERJA') || str_contains($name, 'UPAH')) {
            return 'labor';
        }
        if (str_contains($name, 'PERALATAN') || str_contains($name, 'ALAT')) {
            return 'equipment';
        }
        return 'material';
    }

    protected function normalizeType(?string $type): string
    {
        if (!$type)
            return 'material';

        $type = strtolower(trim($type));

        return match (true) {
            str_contains($type, 'labor') || str_contains($type, 'upah') || str_contains($type, 'tenaga') => 'labor',
            str_contains($type, 'equip') || str_contains($type, 'alat') || str_contains($type, 'peralatan') => 'equipment',
            default => 'material',
        };
    }

    protected function getValue(Collection $row, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            if (isset($row[$key]) && !empty($row[$key])) {
                return trim($row[$key]);
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
