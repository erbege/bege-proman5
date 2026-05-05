<?php

namespace App\Services;

use App\Models\AhspBasePrice;
use App\Models\AhspComponent;
use App\Models\AhspWorkType;
use App\Models\Material;
use Illuminate\Support\Facades\Cache;

class MaterialMatcherService
{
    /**
     * Common construction material keywords mapping to help identify materials
     */
    protected array $materialKeywords = [
        'beton' => ['semen', 'pasir', 'kerikil', 'split', 'batu pecah', 'air'],
        'besi' => ['besi beton', 'besi tulangan', 'kawat bendrat', 'kawat ikat'],
        'bekisting' => ['kayu', 'multiplek', 'papan', 'paku', 'minyak bekisting'],
        'pasangan' => ['bata', 'batako', 'semen', 'pasir', 'mortar'],
        'plester' => ['semen', 'pasir', 'acian'],
        'keramik' => ['keramik', 'semen', 'nat keramik', 'semen warna'],
        'cat' => ['cat', 'plamir', 'amplas', 'kuas', 'roller'],
        'atap' => ['genteng', 'rangka atap', 'baja ringan', 'seng', 'asbes'],
        'kusen' => ['kayu', 'aluminium', 'kaca', 'engsel', 'kunci'],
        'pipa' => ['pipa pvc', 'fitting', 'lem pipa', 'kran', 'valve'],
        'listrik' => ['kabel', 'saklar', 'stop kontak', 'mcb', 'lampu'],
        'pondasi' => ['batu kali', 'semen', 'pasir', 'besi', 'beton'],
        'galian' => [], // Usually no materials, just work
        'urugan' => ['tanah urug', 'pasir urug', 'sirtu'],
    ];

    /**
     * Analyze work name and find relevant materials using keyword matching
     * Enhanced to include AHSP components and base prices as reference
     * 
     * @param string $workName
     * @param float $volume
     * @param string $unit
     * @return array
     */
    public function analyzeWorkName(string $workName, float $volume = 1, string $unit = 'm3'): array
    {
        $foundMaterials = [];
        $workNameLower = strtolower($workName);

        // 1. Try to find matching AHSP work type first (best source for coefficients)
        $ahspComponents = $this->findAhspComponentsByWorkName($workName);
        foreach ($ahspComponents as $comp) {
            $foundMaterials[] = [
                'material_name' => $comp['name'],
                'material_id' => $comp['material_id'],
                'material' => $comp['material'],
                'coefficient' => $comp['coefficient'],
                'estimated_qty' => round($volume * $comp['coefficient'], 4),
                'unit' => $comp['unit'],
                'match_score' => $comp['score'],
                'source' => 'ahsp_component',
            ];
        }

        // 2. Search AHSP base prices (for component_type = 'material')
        $ahspPrices = $this->searchAhspBasePrices($workName);
        foreach ($ahspPrices as $price) {
            // Check if already added from AHSP components
            $exists = collect($foundMaterials)->contains(
                fn($m) =>
                strtolower($m['material_name']) === strtolower($price['name'])
            );

            if (!$exists) {
                $foundMaterials[] = [
                    'material_name' => $price['name'],
                    'material_id' => $price['material_id'],
                    'material' => $price['material'],
                    'coefficient' => $this->estimateCoefficient($price['name'], $unit),
                    'estimated_qty' => round($volume * $this->estimateCoefficient($price['name'], $unit), 4),
                    'unit' => $price['unit'],
                    'match_score' => $price['score'],
                    'source' => 'ahsp_base_price',
                ];
            }
        }

        // 3. Search master materials table
        $masterMaterials = $this->getMasterMaterials();
        foreach ($masterMaterials as $material) {
            // Check if already added
            $exists = collect($foundMaterials)->contains(
                fn($m) =>
                $m['material_id'] === $material->id
            );

            if ($exists)
                continue;

            // Pre-filter: material name must contain at least one keyword from work name
            // This avoids thousands of fuzzy matches for irrelevant materials
            $keywords = $this->extractKeywords($workName);
            $hasKeyword = false;
            foreach ($keywords as $keyword) {
                if (str_contains(strtolower($material->name), $keyword)) {
                    $hasKeyword = true;
                    break;
                }
            }

            if (!$hasKeyword)
                continue;

            $score = $this->calculateMatchScore($workNameLower, strtolower($material->name));

            if ($score >= 50) {
                $coefficient = $this->estimateCoefficient($material->name, $unit);

                $foundMaterials[] = [
                    'material_name' => $material->name,
                    'material_id' => $material->id,
                    'material' => $material,
                    'coefficient' => $coefficient,
                    'estimated_qty' => round($volume * $coefficient, 4),
                    'unit' => $material->unit ?? $unit,
                    'match_score' => $score,
                    'source' => 'master_material',
                ];
            }
        }

        // Sort by score descending
        usort($foundMaterials, fn($a, $b) => $b['match_score'] <=> $a['match_score']);

        // Limit to top 10 matches
        return array_slice($foundMaterials, 0, 10);
    }

    /**
     * Analyze a RAB item for materials based on its source
     * - If source='ahsp': Use ahsp_components table directly
     * - Otherwise: Use materials table via analyzeWorkName()
     * 
     * Optimized: Uses eager loading and selective column queries
     */
    public function analyzeRabItemLocal(\App\Models\RabItem $item): array
    {
        // If source is 'ahsp' and has ahsp_work_type_id, use AHSP components directly
        if ($item->source === 'ahsp' && $item->ahsp_work_type_id) {
            return $this->getAhspComponentMaterials($item);
        }

        // Otherwise, use materials table via keyword matching
        return $this->analyzeWorkName(
            $item->work_name,
            (float) $item->volume,
            $item->unit
        );
    }

    /**
     * Get material components directly from ahsp_components table
     * Used when RabItem source='ahsp'
     * 
     * Optimized: Selects only needed columns, no N+1 queries
     */
    protected function getAhspComponentMaterials(\App\Models\RabItem $item): array
    {
        $components = AhspComponent::where('ahsp_work_type_id', $item->ahsp_work_type_id)
            ->where('component_type', AhspComponent::TYPE_MATERIAL)
            ->select('id', 'name', 'unit', 'coefficient')
            ->get();

        if ($components->isEmpty()) {
            return [];
        }

        return $components->map(function ($component) use ($item) {
            // Try to find matching master material
            // Use findBestMatch but prioritize exact match
            $match = $this->findBestMatch($component->name);

            return [
                'material_name' => $component->name,
                'material_id' => $match['id'], // Link to materials table if found
                'material' => $match['material'],
                'coefficient' => (float) $component->coefficient,
                'estimated_qty' => round((float) $item->volume * (float) $component->coefficient, 4),
                'unit' => $component->unit,
                'match_score' => $match['id'] ? $match['score'] : 100, // Use match score if found, else 100 (from AHSP)
                'analysis_source' => 'ahsp_component',
                'notes' => 'Matched via AHSP Component',
            ];
        })->toArray();
    }

    /**
     * Find AHSP components that match the work name
     * This provides accurate coefficients from AHSP analysis data
     * Optimized: Uses database-level filtering to avoid loading all work types
     */
    protected function findAhspComponentsByWorkName(string $workName): array
    {
        $keywords = $this->extractKeywords($workName);
        $results = [];

        if (empty($keywords)) {
            return $results;
        }

        // Use database-level filtering instead of loading all work types
        $workTypes = AhspWorkType::active()
            ->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('name', 'LIKE', "%{$keyword}%");
                }
            })
            ->with(['components' => fn($q) => $q->whereIn('component_type', ['material', 'equipment'])])
            ->limit(20) // Limit to prevent memory issues
            ->get();

        $workNameLower = strtolower($workName);

        foreach ($workTypes as $workType) {
            $wtNameLower = strtolower($workType->name);

            // Check similarity
            similar_text($workNameLower, $wtNameLower, $similarity);
            $score = max($similarity, 70); // Boost score since we already filtered by keyword

            foreach ($workType->components as $component) {
                // Find linked material
                $linkedMaterial = $this->findMaterialByName($component->name);

                $results[] = [
                    'name' => $component->name,
                    'unit' => $component->unit,
                    'coefficient' => (float) $component->coefficient,
                    'score' => min(95, $score + 5),
                    'material_id' => $linkedMaterial?->id,
                    'material' => $linkedMaterial,
                    'component_type' => $component->component_type,
                    'ahsp_work_type' => $workType->code . ' - ' . $workType->name,
                ];
            }
        }

        return $results;
    }

    /**
     * Extract meaningful keywords from text for database searching
     */
    protected function extractKeywords(string $text): array
    {
        $words = preg_split('/[\s\-\_\.\,]+/', strtolower($text));
        // Filter out short words and common stop words
        $stopWords = ['dan', 'atau', 'yang', 'untuk', 'dengan', 'dari', 'per', 'ke', 'di'];
        return array_values(array_filter($words, fn($w) => strlen($w) >= 3 && !in_array($w, $stopWords)));
    }

    /**
     * Search AHSP base prices for materials matching the work name
     * Optimized: Uses database-level filtering with LIKE queries
     */
    protected function searchAhspBasePrices(string $workName): array
    {
        $keywords = $this->extractKeywords($workName);
        $results = [];

        if (empty($keywords)) {
            return $results;
        }

        // Use database-level filtering instead of loading all base prices
        $basePrices = AhspBasePrice::where('component_type', 'material')
            ->active()
            ->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('name', 'LIKE', "%{$keyword}%");
                }
            })
            ->select('id', 'code', 'name', 'unit', 'material_id')
            ->limit(50) // Limit results
            ->get();

        $workNameLower = strtolower($workName);

        foreach ($basePrices as $price) {
            $priceNameLower = strtolower($price->name);
            $score = $this->calculateMatchScore($workNameLower, $priceNameLower);

            $results[] = [
                'name' => $price->name,
                'unit' => $price->unit,
                'score' => $score,
                'material_id' => $price->material_id,
                'material' => $price->material_id ? Material::find($price->material_id) : null,
            ];
        }

        return $results;
    }

    /**
     * Calculate match score between work name and material name
     */
    protected function calculateMatchScore(string $workNameLower, string $materialNameLower): int
    {
        $score = 0;

        // Check if material name appears in work name
        if (str_contains($workNameLower, $materialNameLower)) {
            $score = 90;
        } elseif (str_contains($materialNameLower, $workNameLower)) {
            $score = 85;
        } else {
            // Check individual words
            $workWords = preg_split('/[\s\-\_\.\,]+/', $workNameLower);
            $materialWords = preg_split('/[\s\-\_\.\,]+/', $materialNameLower);

            $matchCount = 0;
            foreach ($workWords as $word) {
                if (strlen($word) < 3)
                    continue;
                foreach ($materialWords as $mWord) {
                    if (strlen($mWord) < 3)
                        continue;
                    if (str_contains($mWord, $word) || str_contains($word, $mWord)) {
                        $matchCount++;
                    }
                }
            }

            if ($matchCount > 0) {
                $score = min(80, 50 + ($matchCount * 10));
            }
        }

        // Also check against material keywords mapping
        foreach ($this->materialKeywords as $keyword => $materials) {
            if (str_contains($workNameLower, $keyword)) {
                foreach ($materials as $matKeyword) {
                    if (str_contains($materialNameLower, strtolower($matKeyword))) {
                        $score = max($score, 75);
                    }
                }
            }
        }

        return $score;
    }

    /**
     * Find material by name (fuzzy match)
     */
    protected function findMaterialByName(string $name)
    {
        $nameLower = strtolower($name);

        $materials = $this->getMasterMaterials();

        foreach ($materials as $material) {
            similar_text($nameLower, strtolower($material->name), $similarity);
            if ($similarity >= 80) {
                return $material;
            }
        }

        // Try partial match
        foreach ($materials as $material) {
            if (
                str_contains(strtolower($material->name), $nameLower) ||
                str_contains($nameLower, strtolower($material->name))
            ) {
                return $material;
            }
        }

        return null;
    }

    /**
     * Estimate coefficient based on material type and unit
     */
    protected function estimateCoefficient(string $materialName, string $unit): float
    {
        $materialLower = strtolower($materialName);

        // Common coefficients for construction materials per m3/m2
        $coefficients = [
            'semen' => ['m3' => 7, 'm2' => 0.2, 'unit' => 1],
            'pasir' => ['m3' => 0.5, 'm2' => 0.02, 'unit' => 1],
            'kerikil' => ['m3' => 0.8, 'm2' => 0, 'unit' => 1],
            'split' => ['m3' => 0.8, 'm2' => 0, 'unit' => 1],
            'besi' => ['m3' => 150, 'm2' => 10, 'unit' => 1],
            'kawat' => ['m3' => 2, 'm2' => 0.15, 'unit' => 1],
            'paku' => ['m3' => 0.5, 'm2' => 0.05, 'unit' => 1],
            'multiplek' => ['m3' => 0.3, 'm2' => 0.02, 'unit' => 1],
            'bata' => ['m3' => 0, 'm2' => 70, 'unit' => 1],
            'batako' => ['m3' => 0, 'm2' => 12.5, 'unit' => 1],
            'keramik' => ['m3' => 0, 'm2' => 1.05, 'unit' => 1],
            'cat' => ['m3' => 0, 'm2' => 0.1, 'unit' => 1],
        ];

        $unitLower = strtolower($unit);

        foreach ($coefficients as $keyword => $coefs) {
            if (str_contains($materialLower, $keyword)) {
                if (isset($coefs[$unitLower])) {
                    return $coefs[$unitLower];
                }
            }
        }

        // Default coefficient
        return 1.0;
    }

    /**
     * Get master materials list with caching
     */
    protected function getMasterMaterials()
    {
        return Cache::remember('master_materials_list', 60 * 60, function () {
            // Use toBase() to return lightweight stdClass objects instead of Eloquent models
            // This significantly reduces the serialized size in cache
            return Material::select('id', 'name', 'unit')->toBase()->get();
        });
    }

    /**
     * Find the best matching material from master data using fuzzy matching.
     *
     * @param string $aiMaterialName
     * @return array{id: ?int, score: int, material: ?Material}
     */
    public function findBestMatch(string $aiMaterialName): array
    {
        $masterMaterials = $this->getMasterMaterials();

        $bestMatchId = null;
        $highestScore = 0;
        $bestMaterial = null;

        // Loop and compare using Fuzzy Logic
        foreach ($masterMaterials as $master) {
            $score = 0;

            // similiar_text calculates percentage similarity
            similar_text(
                strtolower($aiMaterialName),
                strtolower($master->name),
                $score
            );

            if ($score > $highestScore) {
                $highestScore = $score;
                $bestMatchId = $master->id;
                $bestMaterial = $master; // Keep reference to model
            }
        }

        // Threshold: If similarity is below 75%, consider it as no match
        if ($bestMaterial && $highestScore >= 75) {
            return [
                'id' => $bestMatchId,
                'score' => (int) $highestScore,
                'material' => $bestMaterial,
            ];
        }

        return [
            'id' => null,
            'score' => (int) $highestScore,
            'material' => null,
            'best_guess' => $bestMaterial, // Useful for suggestion
        ];
    }

    /**
     * Clear all caches used by this service
     */
    public function clearCache(): void
    {
        Cache::forget('master_materials_list');
        Cache::forget('master_materials_dropdown');
    }
}
