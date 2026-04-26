<?php

namespace App\Services;

use App\Models\AhspPriceSnapshot;
use App\Models\AhspWorkType;
use App\Models\Project;
use App\Models\RabItem;
use App\Models\RabSection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RabGeneratorService
{
    protected AhspCalculatorService $calculator;

    public function __construct(AhspCalculatorService $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Generate a single RAB item from AHSP work type
     */
    public function generateFromAhsp(
        Project $project,
        RabSection $section,
        AhspWorkType $workType,
        float $volume,
        string $regionCode
    ): RabItem {
        // Calculate prices
        $calculation = $this->calculator->calculateUnitPrice($workType, $regionCode);

        // Create RAB item
        $rabItem = RabItem::create([
            'project_id' => $project->id,
            'rab_section_id' => $section->id,
            'ahsp_work_type_id' => $workType->id,
            'code' => $workType->code,
            'work_name' => $workType->name,
            'description' => $workType->description,
            'volume' => $volume,
            'unit' => $workType->unit,
            'unit_price' => $calculation['unit_price'],
            'total_price' => $calculation['unit_price'] * $volume,
            'sort_order' => $section->items()->count(),
            'source' => 'ahsp',
        ]);

        // Create price snapshot for tracking
        AhspPriceSnapshot::createFromCalculation($rabItem, $workType, $regionCode, $calculation);

        return $rabItem;
    }

    /**
     * Generate multiple RAB items from AHSP work types
     * 
     * @param array $items Array of ['work_type_id' => int, 'volume' => float]
     */
    public function batchGenerate(
        Project $project,
        RabSection $section,
        array $items,
        string $regionCode
    ): Collection {
        $result = collect();

        DB::transaction(function () use ($project, $section, $items, $regionCode, &$result) {
            foreach ($items as $item) {
                $workType = AhspWorkType::with('category')->find($item['work_type_id']);
                if ($workType) {
                    // Ensure parent sections exist for this work type's category
                    $targetSection = $this->ensureParentSectionsExist($project, $workType);

                    // Use target section if found, otherwise use provided section
                    $actualSection = $targetSection ?? $section;

                    $rabItem = $this->generateFromAhsp(
                        $project,
                        $actualSection,
                        $workType,
                        $item['volume'],
                        $regionCode
                    );
                    $result->push($rabItem);
                }
            }
        });

        // Recalculate project weights
        $project->calculateTotalWeight();

        return $result;
    }

    /**
     * Ensure all parent sections exist for a work type's category hierarchy
     * Returns the section ID where the item should be placed
     */
    protected function ensureParentSectionsExist(Project $project, AhspWorkType $workType): ?RabSection
    {
        if (!$workType->category) {
            return null;
        }

        // Get the full category chain from root to the work type's category
        $categoryChain = $this->getCategoryChainToRoot($workType->category);

        $parentSectionId = null;
        $level = 0;
        $lastSection = null;

        foreach ($categoryChain as $ahspCategory) {
            // Check if section already exists for this category
            $existingSection = RabSection::where('project_id', $project->id)
                ->where('ahsp_category_id', $ahspCategory->id)
                ->first();

            if ($existingSection) {
                $parentSectionId = $existingSection->id;
                $lastSection = $existingSection;
            } else {
                // Create new section
                $newSection = RabSection::create([
                    'project_id' => $project->id,
                    'ahsp_category_id' => $ahspCategory->id,
                    'code' => $ahspCategory->code,
                    'name' => $ahspCategory->name,
                    'parent_id' => $parentSectionId,
                    'level' => $level,
                    'sort_order' => $project->rabSections()->where('parent_id', $parentSectionId)->count(),
                ]);
                $parentSectionId = $newSection->id;
                $lastSection = $newSection;
            }
            $level++;
        }

        return $lastSection;
    }

    /**
     * Get the category chain from root to the given category (inclusive)
     */
    protected function getCategoryChainToRoot(\App\Models\AhspCategory $category): array
    {
        $chain = [];
        $current = $category;

        while ($current) {
            array_unshift($chain, $current);
            $current = $current->parent_id ? \App\Models\AhspCategory::find($current->parent_id) : null;
        }

        return $chain;
    }

    /**
     * Recalculate RAB prices from AHSP based on latest prices
     */
    public function recalculateRabPrices(Project $project, string $regionCode): array
    {
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($project, $regionCode, &$updated, &$skipped) {
            $rabItems = $project->rabItems()
                ->whereNotNull('ahsp_work_type_id')
                ->where('source', 'ahsp')
                ->get();

            foreach ($rabItems as $rabItem) {
                $workType = $rabItem->ahspWorkType;
                if (!$workType) {
                    $skipped++;
                    continue;
                }

                // Calculate new prices
                $calculation = $this->calculator->calculateUnitPrice($workType, $regionCode);

                // Update RAB item
                $rabItem->update([
                    'unit_price' => $calculation['unit_price'],
                    'total_price' => $calculation['unit_price'] * $rabItem->volume,
                ]);

                // Update or create price snapshot
                $rabItem->priceSnapshot()->delete();
                AhspPriceSnapshot::createFromCalculation($rabItem, $workType, $regionCode, $calculation);

                $updated++;
            }
        });

        // Recalculate project weights
        $project->calculateTotalWeight();

        return [
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    /**
     * Preview RAB generation without saving
     */
    public function previewGeneration(
        AhspWorkType $workType,
        float $volume,
        string $regionCode
    ): array {
        $calculation = $this->calculator->calculateUnitPrice($workType, $regionCode);

        return [
            'work_type' => [
                'id' => $workType->id,
                'code' => $workType->code,
                'name' => $workType->name,
                'unit' => $workType->unit,
            ],
            'volume' => $volume,
            'region_code' => $regionCode,
            'calculation' => $calculation,
            'total_price' => $calculation['unit_price'] * $volume,
        ];
    }

    /**
     * Get count of RAB items that can be recalculated
     */
    public function getRecalculableCount(Project $project): int
    {
        return $project->rabItems()
            ->whereNotNull('ahsp_work_type_id')
            ->where('source', 'ahsp')
            ->count();
    }
}
