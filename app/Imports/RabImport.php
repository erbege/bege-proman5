<?php

namespace App\Imports;

use App\Models\AhspWorkType;
use App\Models\Project;
use App\Models\RabItem;
use App\Models\RabSection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class RabImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    protected Project $project;
    protected array $sectionMap = [];
    protected array $ahspWorkTypeMap = [];
    protected int $sortOrder = 0;

    public function __construct(Project $project)
    {
        $this->project = $project;
        $this->loadAhspWorkTypes();
    }

    /**
     * Load AHSP work types for auto-matching
     */
    protected function loadAhspWorkTypes(): void
    {
        // Load all active AHSP work types and index by name and code for fast lookup
        $workTypes = AhspWorkType::active()->get();

        foreach ($workTypes as $wt) {
            // Index by normalized name
            $normalizedName = strtolower(trim($wt->name));
            $this->ahspWorkTypeMap['name'][$normalizedName] = $wt->id;

            // Index by code if available
            if ($wt->code) {
                $normalizedCode = strtolower(trim($wt->code));
                $this->ahspWorkTypeMap['code'][$normalizedCode] = $wt->id;
            }
        }
    }

    /**
     * Try to match work name/code with AHSP work type
     */
    protected function findMatchingAhspWorkType(?string $workName, ?string $itemCode): ?int
    {
        // Try to match by code first (more specific)
        if ($itemCode) {
            $normalizedCode = strtolower(trim($itemCode));
            if (isset($this->ahspWorkTypeMap['code'][$normalizedCode])) {
                return $this->ahspWorkTypeMap['code'][$normalizedCode];
            }
        }

        // Try to match by exact name
        if ($workName) {
            $normalizedName = strtolower(trim($workName));
            if (isset($this->ahspWorkTypeMap['name'][$normalizedName])) {
                return $this->ahspWorkTypeMap['name'][$normalizedName];
            }
        }

        return null;
    }


    /**
     * Process the Excel collection
     * 
     * Expected columns:
     * - section_code: Kode bagian (A, B, C atau I, II, III)
     * - section_name: Nama bagian pekerjaan
     * - item_code: Kode item (Optional)
     * - work_name: Nama pekerjaan
     * - volume: Volume pekerjaan
     * - unit: Satuan (m3, m2, ls, kg, bh)
     * - unit_price: Harga satuan
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Skip completely empty rows
            if ($this->isEmptyRow($row)) {
                continue;
            }

            // Get or normalize values
            $sectionCode = $this->getValue($row, ['section_code', 'kode_bagian', 'kode_section']);
            $sectionName = $this->getValue($row, ['section_name', 'nama_bagian', 'nama_section', 'section']);
            $itemCode = $this->getValue($row, ['item_code', 'kode_item', 'kode']);
            $workName = $this->getValue($row, ['work_name', 'nama_pekerjaan', 'pekerjaan', 'uraian']);
            $volume = $this->getNumericValue($row, ['volume', 'vol']);
            $unit = $this->getValue($row, ['unit', 'satuan', 'sat']);
            $unitPrice = $this->getNumericValue($row, ['unit_price', 'harga_satuan', 'harga']);

            // If this is a section header row (has section info but no volume)
            if ($sectionCode && $sectionName && !$volume) {
                $section = $this->getOrCreateSection($sectionCode, $sectionName);
                continue;
            }

            // If this is an item row
            if ($workName && $volume > 0) {
                // Determine the section for this item
                $section = null;
                if ($sectionCode) {
                    $section = $this->getOrCreateSection($sectionCode, $sectionName ?? $sectionCode);
                } else {
                    // Use the last section if no section specified
                    $section = $this->getLastSection();
                }

                if (!$section) {
                    // Create a default section if none exists
                    $section = $this->getOrCreateSection('A', 'Pekerjaan Umum');
                }

                $this->createRabItem($section, $itemCode, $workName, $volume, $unit, $unitPrice);
            }
        }

        // After import, calculate weight percentages
        $this->project->calculateTotalWeight();
    }

    protected function isEmptyRow(Collection $row): bool
    {
        return $row->filter(fn($value) => !empty($value) && $value !== null)->isEmpty();
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

    protected function getOrCreateSection(string $code, string $name): RabSection
    {
        $key = strtoupper($code);

        if (!isset($this->sectionMap[$key])) {
            $this->sectionMap[$key] = RabSection::firstOrCreate(
                [
                    'project_id' => $this->project->id,
                    'code' => $code,
                ],
                [
                    'name' => $name,
                    'sort_order' => count($this->sectionMap),
                    'level' => 0,
                ]
            );
        }

        return $this->sectionMap[$key];
    }

    protected function getLastSection(): ?RabSection
    {
        if (empty($this->sectionMap)) {
            return null;
        }

        return end($this->sectionMap);
    }

    protected function createRabItem(
        RabSection $section,
        ?string $itemCode,
        string $workName,
        float $volume,
        ?string $unit,
        float $unitPrice
    ): RabItem {
        $this->sortOrder++;

        // Try to find matching AHSP work type
        $ahspWorkTypeId = $this->findMatchingAhspWorkType($workName, $itemCode);
        $source = $ahspWorkTypeId ? 'ahsp' : 'import';

        return RabItem::create([
            'project_id' => $this->project->id,
            'rab_section_id' => $section->id,
            'ahsp_work_type_id' => $ahspWorkTypeId,
            'code' => $itemCode,
            'work_name' => $workName,
            'volume' => $volume,
            'unit' => $unit ?? 'ls',
            'unit_price' => $unitPrice,
            'total_price' => $volume * $unitPrice,
            'sort_order' => $this->sortOrder,
            'source' => $source,
        ]);
    }

    public function getImportedCount(): int
    {
        return $this->sortOrder;
    }
}
