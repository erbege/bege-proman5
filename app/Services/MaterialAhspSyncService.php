<?php

namespace App\Services;

use App\Models\AhspBasePrice;
use App\Models\Material;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for synchronizing materials with AHSP base prices
 * 
 * This service handles bidirectional sync:
 * 1. Material → AHSP: When a material is updated, update related AHSP base prices
 * 2. AHSP → Material: When AHSP price is updated, optionally update the material
 */
class MaterialAhspSyncService
{
    /**
     * Sync a material to AHSP base prices
     * Creates or updates AHSP base price entries for the material
     */
    public function syncMaterialToAhsp(Material $material, string $regionCode = 'ID-JK', ?string $regionName = null): AhspBasePrice
    {
        $regionName = $regionName ?? $this->getRegionName($regionCode);

        return AhspBasePrice::updateOrCreate(
            [
                'material_id' => $material->id,
                'region_code' => $regionCode,
            ],
            [
                'code' => $material->code,
                'name' => $material->name,
                'component_type' => 'material',
                'unit' => $material->unit,
                'price' => $material->unit_price,
                'region_name' => $regionName,
                'effective_date' => now(),
                'source' => 'Material Master',
                'is_active' => $material->is_active,
            ]
        );
    }

    /**
     * Sync an AHSP base price to materials table
     * Creates or updates material entry from AHSP price
     */
    public function syncAhspToMaterial(AhspBasePrice $ahspPrice): ?Material
    {
        // Only sync material-type prices
        if ($ahspPrice->component_type !== 'material') {
            return null;
        }

        // If already linked to a material, update it
        if ($ahspPrice->material_id) {
            $material = Material::find($ahspPrice->material_id);
            if ($material) {
                $material->update([
                    'name' => $ahspPrice->name,
                    'unit' => $ahspPrice->unit,
                    'unit_price' => $ahspPrice->price,
                    'is_active' => $ahspPrice->is_active,
                ]);
                return $material;
            }
        }

        // Create new material if not linked
        $material = Material::updateOrCreate(
            ['code' => $ahspPrice->code],
            [
                'name' => $ahspPrice->name,
                'category' => $this->detectMaterialCategory($ahspPrice->name),
                'unit' => $ahspPrice->unit,
                'unit_price' => $ahspPrice->price,
                'is_active' => $ahspPrice->is_active,
            ]
        );

        // Link AHSP price to material
        $ahspPrice->update(['material_id' => $material->id]);

        return $material;
    }

    /**
     * Bulk sync all materials to AHSP for a specific region
     */
    public function syncAllMaterialsToAhsp(string $regionCode = 'ID-JK', ?string $regionName = null): Collection
    {
        $materials = Material::active()->get();
        $synced = collect();

        DB::transaction(function () use ($materials, $regionCode, $regionName, &$synced) {
            foreach ($materials as $material) {
                $synced->push($this->syncMaterialToAhsp($material, $regionCode, $regionName));
            }
        });

        Log::info("Synced {$synced->count()} materials to AHSP for region {$regionCode}");

        return $synced;
    }

    /**
     * Bulk sync all AHSP material prices to materials table
     */
    public function syncAllAhspToMaterials(): Collection
    {
        $ahspPrices = AhspBasePrice::where('component_type', 'material')
            ->active()
            ->get();

        $synced = collect();

        DB::transaction(function () use ($ahspPrices, &$synced) {
            foreach ($ahspPrices as $price) {
                $material = $this->syncAhspToMaterial($price);
                if ($material) {
                    $synced->push($material);
                }
            }
        });

        Log::info("Synced {$synced->count()} AHSP prices to Materials");

        return $synced;
    }

    /**
     * Auto-link unlinked AHSP prices to existing materials by code
     */
    public function autoLinkAhspToMaterials(): int
    {
        $linked = 0;

        $unlinkedPrices = AhspBasePrice::whereNull('material_id')
            ->where('component_type', 'material')
            ->get();

        foreach ($unlinkedPrices as $price) {
            $material = Material::where('code', $price->code)->first();
            if ($material) {
                $price->update(['material_id' => $material->id]);
                $linked++;
            }
        }

        Log::info("Auto-linked {$linked} AHSP prices to existing materials");

        return $linked;
    }

    /**
     * Get region name from code
     */
    private function getRegionName(string $regionCode): string
    {
        $regions = [
            'ID-JK' => 'DKI Jakarta',
            'ID-JB' => 'Jawa Barat',
            'ID-JT' => 'Jawa Tengah',
            'ID-JI' => 'Jawa Timur',
            'ID-BT' => 'Banten',
            'ID-YO' => 'DI Yogyakarta',
            'ID-SU' => 'Sumatera Utara',
            'ID-SS' => 'Sumatera Selatan',
            'ID-KS' => 'Kalimantan Selatan',
            'ID-KT' => 'Kalimantan Timur',
            'ID-SA' => 'Sulawesi Utara',
            'ID-SN' => 'Sulawesi Selatan',
            'ID-BA' => 'Bali',
            'ID-NB' => 'Nusa Tenggara Barat',
            'ID-NT' => 'Nusa Tenggara Timur',
            'ID-PA' => 'Papua',
        ];

        return $regions[$regionCode] ?? $regionCode;
    }

    /**
     * Detect material category from name
     */
    private function detectMaterialCategory(string $name): string
    {
        $name = strtolower($name);

        $categories = [
            'Semen' => ['semen', 'pc ', 'portland'],
            'Pasir' => ['pasir'],
            'Batu' => ['batu', 'kerikil', 'split', 'sirtu'],
            'Besi' => ['besi', 'kawat', 'wiremesh'],
            'Beton' => ['beton', 'ready mix'],
            'Bata' => ['bata', 'batako', 'hebel', 'roster'],
            'Kayu' => ['kayu', 'triplek', 'multiplek', 'paku'],
            'Cat' => ['cat', 'plamir', 'dempul', 'meni'],
            'Keramik' => ['keramik', 'granit', 'nat '],
            'Plafon' => ['gypsum', 'kalsi', 'hollow', 'list '],
            'Atap' => ['genteng', 'spandek', 'baja ringan', 'reng', 'nok'],
            'Pipa' => ['pipa'],
            'Sanitair' => ['closet', 'wastafel', 'floor drain', 'kran'],
            'Listrik' => ['kabel', 'saklar', 'stop kontak', 'fitting', 'mcb'],
        ];

        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($name, $keyword)) {
                    return $category;
                }
            }
        }

        return 'Lainnya';
    }
}
