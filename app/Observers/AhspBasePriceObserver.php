<?php

namespace App\Observers;

use App\Models\AhspBasePrice;
use App\Models\Material;
use Illuminate\Support\Facades\Log;

/**
 * Observer for AhspBasePrice model
 * Automatically syncs material-type AHSP base prices to materials table
 */
class AhspBasePriceObserver
{
    /**
     * Handle the AhspBasePrice "created" event.
     */
    public function created(AhspBasePrice $ahspBasePrice): void
    {
        $this->syncToMaterial($ahspBasePrice);
    }

    /**
     * Handle the AhspBasePrice "updated" event.
     */
    public function updated(AhspBasePrice $ahspBasePrice): void
    {
        $this->syncToMaterial($ahspBasePrice);
    }

    /**
     * Sync AHSP base price to materials table
     */
    protected function syncToMaterial(AhspBasePrice $ahspBasePrice): void
    {
        // Only sync material-type prices
        if ($ahspBasePrice->component_type !== 'material') {
            return;
        }

        try {
            // If already linked to a material, update it
            if ($ahspBasePrice->material_id) {
                $material = Material::find($ahspBasePrice->material_id);
                if ($material) {
                    $material->update([
                        'name' => $ahspBasePrice->name,
                        'unit' => $ahspBasePrice->unit,
                        'unit_price' => $ahspBasePrice->price,
                        'region_code' => $ahspBasePrice->region_code,
                        'region_name' => $ahspBasePrice->region_name,
                        'effective_date' => $ahspBasePrice->effective_date,
                        'source' => $ahspBasePrice->source,
                        'is_active' => $ahspBasePrice->is_active,
                    ]);
                    return;
                }
            }

            // Find existing material by code or create new one
            $material = Material::updateOrCreate(
                ['code' => $ahspBasePrice->code],
                [
                    'name' => $ahspBasePrice->name,
                    'category' => $this->detectCategory($ahspBasePrice->name),
                    'unit' => $ahspBasePrice->unit,
                    'unit_price' => $ahspBasePrice->price,
                    'region_code' => $ahspBasePrice->region_code,
                    'region_name' => $ahspBasePrice->region_name,
                    'effective_date' => $ahspBasePrice->effective_date,
                    'source' => $ahspBasePrice->source,
                    'is_active' => $ahspBasePrice->is_active,
                ]
            );

            // Link AHSP price to material (prevent infinite loop by using saveQuietly)
            if (!$ahspBasePrice->material_id) {
                $ahspBasePrice->material_id = $material->id;
                $ahspBasePrice->saveQuietly(); // Avoid triggering observer again
            }

            Log::info("Auto-synced AHSP price to Material: {$material->code} - {$material->name}");
        } catch (\Exception $e) {
            Log::error("Failed to sync AHSP to Material: " . $e->getMessage());
        }
    }

    /**
     * Detect material category from name
     */
    protected function detectCategory(string $name): string
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
