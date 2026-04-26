<?php

namespace App\Services;

use App\Models\AhspBasePrice;
use App\Models\AhspWorkType;

class AhspCalculatorService
{
    /**
     * Calculate unit price for a work type in a specific region
     * 
     * @return array{
     *   labor_cost: float,
     *   material_cost: float,
     *   equipment_cost: float,
     *   subtotal: float,
     *   overhead_percentage: float,
     *   overhead_cost: float,
     *   unit_price: float,
     *   breakdown: array
     * }
     */
    public function calculateUnitPrice(AhspWorkType $workType, string $regionCode): array
    {
        return $workType->calculateUnitPrice($regionCode);
    }

    /**
     * Calculate total cost for given volume
     */
    public function calculateTotal(AhspWorkType $workType, float $volume, string $regionCode): float
    {
        $calculation = $this->calculateUnitPrice($workType, $regionCode);
        return $calculation['unit_price'] * $volume;
    }

    /**
     * Get component breakdown with prices for a work type
     */
    public function getComponentBreakdown(AhspWorkType $workType, string $regionCode): array
    {
        $calculation = $this->calculateUnitPrice($workType, $regionCode);
        return $calculation['breakdown'];
    }

    /**
     * Compare prices across regions
     */
    public function comparePricesAcrossRegions(AhspWorkType $workType, array $regionCodes): array
    {
        $comparison = [];
        foreach ($regionCodes as $regionCode) {
            $calculation = $this->calculateUnitPrice($workType, $regionCode);
            $regionName = $this->getRegionName($regionCode);
            $comparison[$regionCode] = [
                'region_name' => $regionName,
                'unit_price' => $calculation['unit_price'],
                'labor_cost' => $calculation['labor_cost'],
                'material_cost' => $calculation['material_cost'],
                'equipment_cost' => $calculation['equipment_cost'],
            ];
        }
        return $comparison;
    }

    /**
     * Get available regions
     */
    public function getAvailableRegions(): array
    {
        return AhspBasePrice::getRegions();
    }

    /**
     * Get region name from code
     */
    public function getRegionName(string $regionCode): string
    {
        $regions = $this->getAvailableRegions();
        return $regions[$regionCode] ?? $regionCode;
    }

    /**
     * Batch calculate multiple work types
     */
    public function batchCalculate(array $workTypeIds, float $volume, string $regionCode): array
    {
        $results = [];
        foreach ($workTypeIds as $workTypeId) {
            $workType = AhspWorkType::find($workTypeId);
            if ($workType) {
                $calculation = $this->calculateUnitPrice($workType, $regionCode);
                $results[] = [
                    'work_type_id' => $workTypeId,
                    'work_type_code' => $workType->code,
                    'work_type_name' => $workType->name,
                    'unit' => $workType->unit,
                    'unit_price' => $calculation['unit_price'],
                    'volume' => $volume,
                    'total' => $calculation['unit_price'] * $volume,
                ];
            }
        }
        return $results;
    }
}
