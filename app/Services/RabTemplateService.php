<?php

namespace App\Services;

use App\Models\AhspCategory;
use App\Models\AhspWorkType;
use App\Models\Project;
use App\Models\RabItem;
use App\Models\RabSection;
use App\Models\AhspPriceSnapshot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RabTemplateService
{
    protected AhspCalculatorService $calculator;

    public function __construct(AhspCalculatorService $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Preview structure that will be generated from selected categories
     * Includes parent categories that will be auto-created
     */
    public function previewStructure(array $categoryIds): array
    {
        $result = [];

        // Get all selected categories with work types
        $selectedCategories = AhspCategory::with([
            'workTypes' => function ($q) {
                $q->active()->orderBy('code');
            }
        ])
            ->whereIn('id', $categoryIds)
            ->get();

        // Collect all category IDs including ancestors
        $allCategoryIds = collect($categoryIds);
        foreach ($selectedCategories as $category) {
            $ancestors = $this->getAncestorChain($category);
            foreach ($ancestors as $ancestor) {
                $allCategoryIds->push($ancestor->id);
            }
        }
        $allCategoryIds = $allCategoryIds->unique()->toArray();

        // Load all categories (selected + ancestors)
        $allCategories = AhspCategory::with([
            'workTypes' => function ($q) {
                $q->active()->orderBy('code');
            }
        ])
            ->whereIn('id', $allCategoryIds)
            ->get()
            ->sortBy('code', SORT_NATURAL);

        foreach ($allCategories as $category) {
            $isSelected = in_array($category->id, $categoryIds);

            $categoryData = [
                'id' => $category->id,
                'code' => $category->code,
                'name' => $category->name,
                'full_code' => $category->full_code ?? $category->code,
                'is_parent_only' => !$isSelected, // True if auto-added as parent
                'work_types' => [],
            ];

            // Only include work types for selected categories
            if ($isSelected) {
                foreach ($category->workTypes->sortBy('code', SORT_NATURAL) as $workType) {
                    $categoryData['work_types'][] = [
                        'id' => $workType->id,
                        'code' => $workType->code,
                        'name' => $workType->name,
                        'unit' => $workType->unit,
                    ];
                }
            }

            $result[] = $categoryData;
        }

        return $result;
    }

    /**
     * Get all ancestor categories for a given category (from root to immediate parent)
     */
    protected function getAncestorChain(AhspCategory $category): array
    {
        $ancestors = [];
        $current = $category;

        while ($current->parent_id) {
            $parent = AhspCategory::find($current->parent_id);
            if ($parent) {
                array_unshift($ancestors, $parent); // Add at beginning (root first)
                $current = $parent;
            } else {
                break;
            }
        }

        return $ancestors;
    }

    /**
     * Ensure section exists for a category, creating parent sections as needed
     * Returns the section for the given category
     */
    protected function ensureSectionExists(
        Project $project,
        AhspCategory $category,
        array &$sectionMap,
        int &$sectionOrder,
        int &$sectionsCreated
    ): RabSection {
        // If section already exists, return it
        if (isset($sectionMap[$category->code])) {
            return $sectionMap[$category->code];
        }

        // Get ancestor chain (from root to immediate parent)
        $ancestors = $this->getAncestorChain($category);

        // Create ancestor sections first (if they don't exist)
        foreach ($ancestors as $ancestor) {
            if (!isset($sectionMap[$ancestor->code])) {
                $sectionOrder++;

                // Find parent section ID
                $parentSectionId = null;
                if ($ancestor->parent_id) {
                    $ancestorParent = AhspCategory::find($ancestor->parent_id);
                    if ($ancestorParent && isset($sectionMap[$ancestorParent->code])) {
                        $parentSectionId = $sectionMap[$ancestorParent->code]->id;
                    }
                }

                // Calculate level based on code depth
                $level = substr_count($ancestor->code, '.');

                // Create section for ancestor (container only, no items)
                $ancestorSection = RabSection::create([
                    'project_id' => $project->id,
                    'parent_id' => $parentSectionId,
                    'ahsp_category_id' => $ancestor->id,
                    'code' => $ancestor->code,
                    'name' => $ancestor->name,
                    'sort_order' => $sectionOrder,
                    'level' => $level,
                ]);

                $sectionMap[$ancestor->code] = $ancestorSection;
                $sectionsCreated++;
            }
        }

        // Now create the section for the target category
        $sectionOrder++;

        // Find parent section ID
        $parentSectionId = null;
        if ($category->parent_id) {
            $parent = AhspCategory::find($category->parent_id);
            if ($parent && isset($sectionMap[$parent->code])) {
                $parentSectionId = $sectionMap[$parent->code]->id;
            }
        }

        // Calculate level
        $level = substr_count($category->code, '.');

        // Create section for the category
        $section = RabSection::create([
            'project_id' => $project->id,
            'parent_id' => $parentSectionId,
            'ahsp_category_id' => $category->id,
            'code' => $category->code,
            'name' => $category->name,
            'sort_order' => $sectionOrder,
            'level' => $level,
        ]);

        $sectionMap[$category->code] = $section;
        $sectionsCreated++;

        return $section;
    }

    /**
     * Generate RAB sections and items from AHSP categories
     * Auto-creates parent sections when a child category is selected
     */
    public function generateFromCategories(
        Project $project,
        array $categoryIds,
        string $regionCode,
        bool $clearExisting = false
    ): array {
        $sectionsCreated = 0;
        $itemsCreated = 0;

        DB::transaction(function () use ($project, $categoryIds, $regionCode, $clearExisting, &$sectionsCreated, &$itemsCreated) {
            // Clear existing RAB if requested
            if ($clearExisting) {
                $project->rabItems()->delete();
                $project->rabSections()->delete();
            }

            // Load selected categories with work types
            $categories = AhspCategory::with([
                'workTypes' => function ($q) {
                    $q->active()->orderBy('code');
                }
            ])
                ->whereIn('id', $categoryIds)
                ->get()
                ->sortBy('code', SORT_NATURAL);

            // Map to track created sections by category code
            $sectionMap = [];

            // Load existing sections by code
            $existingSections = $project->rabSections()->get();
            foreach ($existingSections as $section) {
                $sectionMap[$section->code] = $section;
            }

            $sectionOrder = $project->rabSections()->max('sort_order') ?? -1;

            // Process each selected category
            foreach ($categories as $category) {
                // Ensure section exists (and create parent sections if needed)
                $section = $this->ensureSectionExists(
                    $project,
                    $category,
                    $sectionMap,
                    $sectionOrder,
                    $sectionsCreated
                );

                // Add work items for this category
                $itemOrder = $section->items()->max('sort_order') ?? -1;

                foreach ($category->workTypes->sortBy('code', SORT_NATURAL) as $workType) {
                    // Check if this work type already exists in this section
                    $existingItem = RabItem::where('rab_section_id', $section->id)
                        ->where('ahsp_work_type_id', $workType->id)
                        ->first();

                    if (!$existingItem) {
                        $itemOrder++;

                        // Calculate unit price from AHSP
                        $calculation = $this->calculator->calculateUnitPrice($workType, $regionCode);

                        // Create RabItem with volume 0 (placeholder)
                        $rabItem = RabItem::create([
                            'project_id' => $project->id,
                            'rab_section_id' => $section->id,
                            'ahsp_work_type_id' => $workType->id,
                            'code' => $workType->code,
                            'work_name' => $workType->name,
                            'description' => $workType->description,
                            'volume' => 0,
                            'unit' => $workType->unit,
                            'unit_price' => $calculation['unit_price'],
                            'total_price' => 0,
                            'sort_order' => $itemOrder,
                            'source' => 'ahsp',
                        ]);

                        // Create price snapshot
                        AhspPriceSnapshot::createFromCalculation($rabItem, $workType, $regionCode, $calculation);

                        $itemsCreated++;
                    }
                }
            }
        });

        // Recalculate project weights
        $project->calculateTotalWeight();

        return [
            'sections_created' => $sectionsCreated,
            'items_created' => $itemsCreated,
        ];
    }

    /**
     * Get categories tree for selection UI
     */
    public function getCategoriesTree(): Collection
    {
        return AhspCategory::with([
            'allChildren',
            'workTypes' => function ($q) {
                $q->active()->select('id', 'ahsp_category_id', 'code', 'name', 'unit');
            }
        ])
            ->roots()
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Get flat list of categories with work type counts
     */
    public function getCategoriesWithCounts(): Collection
    {
        return AhspCategory::withCount([
            'workTypes' => function ($q) {
                $q->active();
            }
        ])
            ->active()
            ->get()
            ->sortBy('code', SORT_NATURAL)
            ->values();
    }
}
