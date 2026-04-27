<?php

namespace App\Livewire;

use App\Models\AhspBasePrice;
use App\Models\AhspCategory;
use App\Models\AhspWorkType;
use App\Models\Project;
use App\Models\RabItem;
use App\Models\RabSection;
use App\Models\AhspPriceSnapshot;
use App\Services\AhspCalculatorService;
use App\Services\ScheduleCalculator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RabImport;

class RabManager extends Component
{
    use WithFileUploads;

    public Project $project;

    // Section modal
    public bool $showSectionModal = false;
    public ?int $editingSectionId = null;
    public ?int $selectedAhspCategoryId = null;
    public string $sectionCode = '';
    public string $sectionName = '';

    // Item modal
    public bool $showItemModal = false;
    public ?int $editingItemId = null;
    public ?int $itemSectionId = null;
    public ?int $itemAhspWorkTypeId = null;
    public string $itemCode = '';
    public string $itemWorkName = '';
    public string $itemDescription = '';
    public float $itemVolume = 0;
    public string $itemUnit = '';
    public float $itemUnitPrice = 0;
    public ?string $itemPlannedStart = null;
    public ?string $itemPlannedEnd = null;
    public string $regionCode = '';

    // Import modal
    public bool $showImportModal = false;
    public $importFile;
    public bool $clearExisting = false;

    // Delete confirmation
    public bool $showDeleteModal = false;
    public string $deleteType = '';
    public ?int $deleteId = null;
    public string $deleteName = '';

    // Bulk delete
    public array $selectedItems = [];
    public bool $showBulkDeleteModal = false;

    public function mount(Project $project)
    {
        $this->project = $project;
        // Set default region
        $regions = AhspBasePrice::getRegions();
        $this->regionCode = array_key_first($regions) ?? 'DEFAULT';
    }

    public function updatedSelectedAhspCategoryId($value)
    {
        if ($value) {
            $category = AhspCategory::find($value);
            if ($category) {
                $this->sectionCode = $category->code;
                $this->sectionName = $category->name;
            }
        }
    }



    // When AHSP work type is selected, auto-fill item fields
    public function updatedItemAhspWorkTypeId($value)
    {
        if ($value) {
            $workType = AhspWorkType::with('components')->find($value);
            if ($workType) {
                $this->itemCode = $workType->code;
                $this->itemWorkName = $workType->name;
                $this->itemDescription = $workType->description ?? '';
                $this->itemUnit = $workType->unit;

                // Calculate unit price from AHSP
                $calculation = $workType->calculateUnitPrice($this->regionCode);
                $this->itemUnitPrice = $calculation['unit_price'];
            }
        }
    }

    // Section CRUD
    public function openSectionModal(?int $sectionId = null)
    {
        $this->reset(['sectionCode', 'sectionName', 'editingSectionId', 'selectedAhspCategoryId']);

        if ($sectionId) {
            $section = RabSection::find($sectionId);
            $this->editingSectionId = $sectionId;
            $this->sectionCode = $section->code;
            $this->sectionName = $section->name;
            $this->selectedAhspCategoryId = $section->ahsp_category_id;
        }

        $this->showSectionModal = true;
    }

    public function closeSectionModal()
    {
        $this->showSectionModal = false;
        $this->reset(['sectionCode', 'sectionName', 'editingSectionId', 'selectedAhspCategoryId']);
    }

    public function saveSection()
    {
        $this->authorize('rab.manage');

        $this->validate([
            'sectionCode' => 'required|string|max:20',
            'sectionName' => 'required|string|max:255',
        ]);
        // ... rest of method ...

        // Helper to ensure parent sections exist recursively (from AHSP category)
        $ensureParentSectionFromAhsp = function ($ahspParentId) use (&$ensureParentSectionFromAhsp) {
            if (!$ahspParentId)
                return null;

            $ahspParent = AhspCategory::find($ahspParentId);
            if (!$ahspParent)
                return null;

            // Check if RAB section for this AHSP parent exists in this project
            $rabSection = RabSection::where('project_id', $this->project->id)
                ->where('ahsp_category_id', $ahspParentId)
                ->first();

            if ($rabSection)
                return $rabSection->id;

            // If not, ensure ITS parent exists first
            $grandParentRabId = $ensureParentSectionFromAhsp($ahspParent->parent_id);

            // Create this section
            $newSection = RabSection::create([
                'project_id' => $this->project->id,
                'ahsp_category_id' => $ahspParent->id,
                'code' => $ahspParent->code,
                'name' => $ahspParent->name,
                'sort_order' => $this->project->rabSections()->where('parent_id', $grandParentRabId)->count(),
                'level' => $ahspParent->level ?? 0,
                'parent_id' => $grandParentRabId
            ]);

            return $newSection->id;
        };

        // Helper to ensure parent sections exist based on code hierarchy (e.g., 2.2.2.1 -> 2.2.2 -> 2.2 -> 2)
        $ensureParentSectionFromCode = function ($code) use (&$ensureParentSectionFromCode) {
            // Get parent code by removing the last segment
            $parts = explode('.', $code);
            if (count($parts) <= 1) {
                return null; // Root level, no parent
            }

            array_pop($parts);
            $parentCode = implode('.', $parts);

            if (empty($parentCode)) {
                return null;
            }

            // Check if section with this code already exists
            $existingSection = RabSection::where('project_id', $this->project->id)
                ->where('code', $parentCode)
                ->first();

            if ($existingSection) {
                return $existingSection->id;
            }

            // Try to find matching AHSP category for this code
            $ahspCategory = AhspCategory::where('code', $parentCode)->first();

            // Recursively ensure grandparent exists
            $grandParentId = $ensureParentSectionFromCode($parentCode);
            $level = count(explode('.', $parentCode)) - 1;

            // Create parent section
            $newSection = RabSection::create([
                'project_id' => $this->project->id,
                'ahsp_category_id' => $ahspCategory?->id,
                'code' => $parentCode,
                'name' => $ahspCategory?->name ?? ('Section ' . $parentCode),
                'sort_order' => $this->project->rabSections()->where('parent_id', $grandParentId)->count(),
                'level' => $level,
                'parent_id' => $grandParentId
            ]);

            return $newSection->id;
        };

        $parentId = null;
        $level = 0;

        if ($this->selectedAhspCategoryId) {
            // Using AHSP category - ensure AHSP parent sections exist
            $ahspCat = AhspCategory::find($this->selectedAhspCategoryId);
            if ($ahspCat) {
                $parentId = $ensureParentSectionFromAhsp($ahspCat->parent_id);
                $level = $ahspCat->level;
            }
        } else {
            // Manual entry - parse code and create parent sections if needed
            $parentId = $ensureParentSectionFromCode($this->sectionCode);
            $level = count(explode('.', $this->sectionCode)) - 1;
        }

        if ($this->editingSectionId) {
            $section = RabSection::find($this->editingSectionId);

            // If we selected a new AHSP category, we might need to move this section
            if ($this->selectedAhspCategoryId && $section->ahsp_category_id != $this->selectedAhspCategoryId) {
                $ahspCat = AhspCategory::find($this->selectedAhspCategoryId);
                $parentId = $ensureParentSectionFromAhsp($ahspCat->parent_id);
                $level = $ahspCat->level;
            }

            $section->update([
                'ahsp_category_id' => $this->selectedAhspCategoryId,
                'parent_id' => $parentId,
                'level' => $level,
                'code' => $this->sectionCode,
                'name' => $this->sectionName,
            ]);
            session()->flash('success', 'Bagian berhasil diperbarui.');
        } else {
            RabSection::create([
                'project_id' => $this->project->id,
                'ahsp_category_id' => $this->selectedAhspCategoryId,
                'parent_id' => $parentId,
                'level' => $level,
                'code' => $this->sectionCode,
                'name' => $this->sectionName,
                'sort_order' => $this->project->rabSections()->where('parent_id', $parentId)->count(),
            ]);
            session()->flash('success', 'Bagian berhasil ditambahkan.');
        }

        $this->closeSectionModal();
        $this->project->refresh();
    }

    // Item CRUD
    public function openItemModal(?int $sectionId = null, ?int $itemId = null)
    {
        $this->reset(['itemCode', 'itemWorkName', 'itemDescription', 'itemVolume', 'itemUnit', 'itemUnitPrice', 'itemPlannedStart', 'itemPlannedEnd', 'editingItemId', 'itemSectionId', 'itemAhspWorkTypeId']);

        $this->itemSectionId = $sectionId;

        if ($itemId) {
            $item = RabItem::find($itemId);
            $this->editingItemId = $itemId;
            $this->itemSectionId = $item->rab_section_id;
            $this->itemAhspWorkTypeId = $item->ahsp_work_type_id;
            $this->itemCode = $item->code ?? '';
            $this->itemWorkName = $item->work_name;
            $this->itemDescription = $item->description ?? '';
            $this->itemVolume = $item->volume;
            $this->itemUnit = $item->unit;
            $this->itemUnitPrice = $item->unit_price;
            $this->itemPlannedStart = $item->planned_start?->format('Y-m-d');
            $this->itemPlannedEnd = $item->planned_end?->format('Y-m-d');
        }

        $this->showItemModal = true;
    }

    public function closeItemModal()
    {
        $this->showItemModal = false;
        $this->reset(['itemCode', 'itemWorkName', 'itemDescription', 'itemVolume', 'itemUnit', 'itemUnitPrice', 'itemPlannedStart', 'itemPlannedEnd', 'editingItemId', 'itemSectionId', 'itemAhspWorkTypeId']);
    }

    public function saveItem()
    {
        $this->authorize('rab.manage');

        $this->validate([
            'itemSectionId' => 'required|exists:rab_sections,id',
            'itemCode' => 'nullable|string|max:20',
            'itemWorkName' => 'required|string|max:255',
            'itemDescription' => 'nullable|string',
            'itemVolume' => 'required|numeric|min:0',
            'itemUnit' => 'required|string|max:20',
            'itemUnitPrice' => 'required|numeric|min:0',
            'itemPlannedStart' => 'nullable|date',
            'itemPlannedEnd' => 'nullable|date|after_or_equal:itemPlannedStart',
            'itemAhspWorkTypeId' => 'nullable|exists:ahsp_work_types,id',
        ]);

        $totalPrice = $this->itemVolume * $this->itemUnitPrice;
        $source = $this->itemAhspWorkTypeId ? 'ahsp' : 'manual';

        // For new items with AHSP work type, ensure parent sections exist
        $sectionId = $this->itemSectionId;
        if (!$this->editingItemId && $this->itemAhspWorkTypeId && $this->itemCode) {
            $workType = AhspWorkType::with('category')->find($this->itemAhspWorkTypeId);
            if ($workType && $workType->category) {
                // Check if the section matches the work type's category
                $currentSection = RabSection::find($this->itemSectionId);

                // If the work type's category differs from current section's category,
                // ensure all parent sections exist and get the correct section ID
                $createdSectionId = $this->ensureParentSectionsExist($workType->code);
                if ($createdSectionId) {
                    $sectionId = $createdSectionId;
                }
            }
        }

        if ($this->editingItemId) {
            $item = RabItem::find($this->editingItemId);
            $item->update([
                'rab_section_id' => $this->itemSectionId,
                'ahsp_work_type_id' => $this->itemAhspWorkTypeId,
                'code' => $this->itemCode ?: null,
                'work_name' => $this->itemWorkName,
                'description' => $this->itemDescription ?: null,
                'volume' => $this->itemVolume,
                'unit' => $this->itemUnit,
                'unit_price' => $this->itemUnitPrice,
                'total_price' => $totalPrice,
                'planned_start' => $this->itemPlannedStart ?: null,
                'planned_end' => $this->itemPlannedEnd ?: null,
                'source' => $source,
            ]);
            session()->flash('success', 'Item berhasil diperbarui.');
        } else {
            $section = RabSection::find($sectionId);
            $rabItem = RabItem::create([
                'project_id' => $this->project->id,
                'rab_section_id' => $sectionId,
                'ahsp_work_type_id' => $this->itemAhspWorkTypeId,
                'code' => $this->itemCode ?: null,
                'work_name' => $this->itemWorkName,
                'description' => $this->itemDescription ?: null,
                'volume' => $this->itemVolume,
                'unit' => $this->itemUnit,
                'unit_price' => $this->itemUnitPrice,
                'total_price' => $totalPrice,
                'planned_start' => $this->itemPlannedStart ?: null,
                'planned_end' => $this->itemPlannedEnd ?: null,
                'sort_order' => $section->items()->count(),
                'source' => $source,
            ]);

            // Create price snapshot if from AHSP
            if ($this->itemAhspWorkTypeId) {
                $workType = AhspWorkType::with('components')->find($this->itemAhspWorkTypeId);
                $calculation = $workType->calculateUnitPrice($this->regionCode);
                AhspPriceSnapshot::createFromCalculation($rabItem, $workType, $this->regionCode, $calculation);
            }

            session()->flash('success', 'Item berhasil ditambahkan.');
        }

        $this->project->calculateTotalWeight();
        $this->closeItemModal();
        $this->project->refresh();
    }

    // Delete
    public function confirmDelete(string $type, int $id, string $name)
    {
        $this->deleteType = $type;
        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->reset(['deleteType', 'deleteId', 'deleteName']);
    }

    public function delete()
    {
        $this->authorize('rab.manage');

        if ($this->deleteType === 'section') {
            $section = RabSection::find($this->deleteId);

            if ($section) {
                // Delete items of this section
                $section->items()->delete();

                // Find and delete child sections and their items
                $childSections = RabSection::where('parent_id', $this->deleteId)->get();
                foreach ($childSections as $child) {
                    $child->items()->delete();
                    $child->delete();
                }

                // Delete the section itself
                $section->delete();
            }
        } else {
            $item = RabItem::find($this->deleteId);
            if ($item) {
                $item->delete();
            }
        }

        $this->project->calculateTotalWeight();
        $this->closeDeleteModal();
        $this->project->refresh();
        session()->flash('success', ($this->deleteType === 'section' ? 'Bagian' : 'Item') . ' berhasil dihapus.');
    }

    // Bulk Delete
    public function toggleSelectAll($sectionId)
    {
        $section = RabSection::with('items')->find($sectionId);
        if (!$section)
            return;

        $itemIds = $section->items->pluck('id')->map(fn($id) => (string) $id)->toArray();
        $allSelected = count(array_intersect($this->selectedItems, $itemIds)) === count($itemIds);

        if ($allSelected) {
            // Deselect all items from this section
            $this->selectedItems = array_values(array_diff($this->selectedItems, $itemIds));
        } else {
            // Select all items from this section
            $this->selectedItems = array_values(array_unique(array_merge($this->selectedItems, $itemIds)));
        }
    }

    public function confirmBulkDelete()
    {
        if (count($this->selectedItems) > 0) {
            $this->showBulkDeleteModal = true;
        }
    }

    public function closeBulkDeleteModal()
    {
        $this->showBulkDeleteModal = false;
    }

    public function executeBulkDelete()
    {
        $this->authorize('rab.manage');

        $count = RabItem::whereIn('id', $this->selectedItems)->delete();

        $this->project->calculateTotalWeight();
        $this->selectedItems = [];
        $this->showBulkDeleteModal = false;
        $this->project->refresh();
        session()->flash('success', "{$count} item berhasil dihapus.");
    }

    // Import
    public function openImportModal()
    {
        $this->reset(['importFile', 'clearExisting']);
        $this->showImportModal = true;
    }

    public function closeImportModal()
    {
        $this->showImportModal = false;
        $this->reset(['importFile', 'clearExisting']);
    }

    public function import()
    {
        $this->authorize('rab.manage');

        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        if ($this->clearExisting) {
            $this->project->rabItems()->delete();
            $this->project->rabSections()->delete();
        }

        $import = new RabImport($this->project);
        Excel::import($import, $this->importFile->getRealPath());

        if ($this->project->start_date && $this->project->end_date) {
            $scheduleCalculator = new ScheduleCalculator();
            $scheduleCalculator->generateSchedule($this->project);
        }

        $this->closeImportModal();
        $this->project->refresh();
        session()->flash('success', "Import berhasil! {$import->getImportedCount()} item pekerjaan ditambahkan.");
    }

    /**
     * Get all descendant category IDs including the given category
     */
    protected function getAllDescendantCategoryIds(AhspCategory $category): array
    {
        $ids = [$category->id];

        $children = AhspCategory::where('parent_id', $category->id)->get();
        foreach ($children as $child) {
            $ids = array_merge($ids, $this->getAllDescendantCategoryIds($child));
        }

        return $ids;
    }

    /**
     * Ensure all parent sections exist for a given work type code
     * Creates parent sections from the work type's category hierarchy
     */
    protected function ensureParentSectionsExist(string $workTypeCode): ?int
    {
        // Get the work type and its category
        $workType = AhspWorkType::with('category')->where('code', $workTypeCode)->first();
        if (!$workType || !$workType->category) {
            return null;
        }

        // Get the category chain from root to the work type's parent category
        $categoryChain = $this->getCategoryChainToRoot($workType->category);

        $parentSectionId = null;
        $level = 0;

        foreach ($categoryChain as $ahspCategory) {
            // Check if section already exists for this category
            $existingSection = RabSection::where('project_id', $this->project->id)
                ->where('ahsp_category_id', $ahspCategory->id)
                ->first();

            if ($existingSection) {
                $parentSectionId = $existingSection->id;
            } else {
                // Create new section
                $newSection = RabSection::create([
                    'project_id' => $this->project->id,
                    'ahsp_category_id' => $ahspCategory->id,
                    'code' => $ahspCategory->code,
                    'name' => $ahspCategory->name,
                    'parent_id' => $parentSectionId,
                    'level' => $level,
                    'sort_order' => $this->project->rabSections()->where('parent_id', $parentSectionId)->count(),
                ]);
                $parentSectionId = $newSection->id;
            }
            $level++;
        }

        return $parentSectionId;
    }

    /**
     * Get the category chain from root to the given category (inclusive)
     */
    protected function getCategoryChainToRoot(AhspCategory $category): array
    {
        $chain = [];
        $current = $category;

        while ($current) {
            array_unshift($chain, $current);
            $current = $current->parent_id ? AhspCategory::find($current->parent_id) : null;
        }

        return $chain;
    }

    public function render()
    {
        $totalValue = $this->project->rabItems()->sum('total_price');
        $totalItemsCount = $this->project->rabItems()->count();

        // Load AHSP categories only when section modal is open (with caching)
        $ahspCategories = $this->showSectionModal
            ? cache()->remember('ahsp_categories_flat_list', 3600, function () {
                return AhspCategory::getFlatListWithIndent();
            })
            : collect();

        // Get suggested work types and all work types only when item modal is open
        $suggestedWorkTypes = collect();
        $allWorkTypes = collect();

        if ($this->showItemModal) {
            if ($this->itemSectionId) {
                $section = RabSection::find($this->itemSectionId);
                if ($section && $section->code) {
                    // Find AHSP category with matching code
                    $sectionCode = trim($section->code);
                    $ahspCategory = AhspCategory::where('code', $sectionCode)->first();

                    if ($ahspCategory) {
                        // Get all descendant category IDs (including self)
                        $categoryIds = $this->getAllDescendantCategoryIds($ahspCategory);

                        // Get work types from all descendant categories
                        $suggestedWorkTypes = AhspWorkType::whereIn('ahsp_category_id', $categoryIds)
                            ->with('category')
                            ->active()
                            ->get()
                            ->sortBy('code', SORT_NATURAL)
                            ->values();
                    }
                }
            }

            // Get all active work types only when modal is open
            $allWorkTypes = AhspWorkType::with('category')
                ->active()
                ->get()
                ->sortBy('code', SORT_NATURAL)
                ->values();
        }

        // Get regions for price calculation (cached)
        $regions = cache()->remember('ahsp_regions', 3600, function () {
            return AhspBasePrice::getRegions();
        });

        // Eager load sections with recursive children and items to avoid N+1
        $sections = $this->project->rabSections()
            ->whereNull('parent_id')
            ->with([
                'items',
                'children.items',
                'children.children.items',
                'children.children.children.items',
                'children.children.children.children.items',
                'children.children.children.children.children.items',
                'ahspCategory'
            ])
            ->get()
            ->sortBy('code', SORT_NATURAL);

        return view('livewire.rab-manager', [
            'totalValue' => $totalValue,
            'totalItemsCount' => $totalItemsCount,
            'sections' => $sections,
            'ahspCategories' => $ahspCategories,
            'suggestedWorkTypes' => $suggestedWorkTypes,
            'allWorkTypes' => $allWorkTypes,
            'regions' => $regions,
        ]);
    }
}
