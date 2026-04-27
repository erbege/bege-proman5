<?php

namespace App\Livewire;

use App\Models\AhspBasePrice;
use App\Models\Material;
use App\Traits\GeneratesUniqueCode;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MaterialsExport;

class MaterialManager extends Component
{
    use WithPagination, GeneratesUniqueCode;

    // Search & Filter
    public string $search = '';
    public string $categoryFilter = '';
    public string $regionFilter = '';
    public string $statusFilter = '';
    public bool $showTrashed = false;

    // Modal states
    public bool $showDetailModal = false;
    public bool $showDeleteModal = false;
    public bool $showAddModal = false;

    // Detail view data
    public ?Material $viewingMaterial = null;

    // Add form data
    public string $code = '';
    public string $name = '';
    public string $category = '';
    public string $unit = 'kg';
    public float $unitPrice = 0;
    public float $minStock = 0;
    public string $description = '';

    // Delete
    public ?int $deleteId = null;
    public string $deleteName = '';

    // Bulk selection
    public array $selectedIds = [];
    public bool $selectAll = false;
    public bool $showBulkDeleteModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'regionFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'showTrashed' => ['except' => false],
    ];

    public array $units = ['kg', 'm3', 'm2', 'm', 'bh', 'btg', 'zak', 'lbr', 'pail', 'set', 'unit', 'ls'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingRegionFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingShowTrashed()
    {
        $this->resetPage();
    }

    // Show Detail Modal
    public function showDetail(int $id)
    {
        $this->viewingMaterial = Material::withTrashed()->with('ahspBasePrice')->find($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->viewingMaterial = null;
    }

    // Inline edit min_stock
    public function updateMinStock(int $id, $value)
    {
        if (!auth()->user()->can('materials.manage')) {
            abort(403);
        }

        $material = Material::find($id);
        if ($material) {
            $material->update(['min_stock' => max(0, floatval($value))]);
            session()->flash('success', "Stok minimum untuk '{$material->name}' berhasil diperbarui.");
        }
    }

    // Bulk Selection
    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            // Get current page IDs
            $query = Material::query();
            if ($this->showTrashed) {
                $query->onlyTrashed();
            }
            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            }
            if ($this->categoryFilter) {
                $query->where('category', $this->categoryFilter);
            }
            if ($this->regionFilter) {
                $query->where('region_code', $this->regionFilter);
            }
            $this->selectedIds = $query->pluck('id')->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function updatedSelectedIds()
    {
        $this->selectAll = false;
    }

    public function confirmBulkDelete()
    {
        if (count($this->selectedIds) > 0) {
            $this->showBulkDeleteModal = true;
        }
    }

    public function closeBulkDeleteModal()
    {
        $this->showBulkDeleteModal = false;
    }

    public function bulkDelete()
    {
        if (!auth()->user()->can('materials.manage')) {
            abort(403);
        }

        $count = count($this->selectedIds);

        if ($count > 0) {
            // Delete linked AHSP base prices
            AhspBasePrice::whereIn('material_id', $this->selectedIds)->delete();

            // Soft delete materials
            Material::whereIn('id', $this->selectedIds)->delete();

            session()->flash('success', "{$count} material dan data AHSP terkait berhasil dihapus.");
        }

        $this->selectedIds = [];
        $this->selectAll = false;
        $this->closeBulkDeleteModal();
    }

    // Add Modal
    public function openAddModal()
    {
        $this->resetValidation();
        $this->reset(['code', 'name', 'category', 'unit', 'unitPrice', 'minStock', 'description']);
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->reset(['code', 'name', 'category', 'unit', 'unitPrice', 'minStock', 'description']);
    }

    public function save()
    {
        if (!auth()->user()->can('materials.manage')) {
            abort(403);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'unit' => 'required|string|max:20',
            'minStock' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'code' => 'nullable|string|max:50|unique:materials,code',
        ];

        if (auth()->user()->can('financials.view')) {
            $rules['unitPrice'] = 'required|numeric|min:0';
        }

        $this->validate($rules);

        Material::create([
            'code' => $this->code ?: $this->generateUniqueCode(Material::class, 'MAT'),
            'name' => $this->name,
            'category' => $this->category,
            'unit' => $this->unit,
            'unit_price' => auth()->user()->can('financials.view') ? $this->unitPrice : 0,
            'min_stock' => $this->minStock ?: 0,
            'description' => $this->description ?: null,
            'is_active' => true,
        ]);

        session()->flash('success', 'Material berhasil ditambahkan.');
        $this->closeAddModal();
    }

    // Delete with cascade to AHSP
    public function confirmDelete(int $id, string $name)
    {
        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->reset(['deleteId', 'deleteName']);
    }

    public function delete()
    {
        if (!auth()->user()->can('materials.manage')) {
            abort(403);
        }

        $material = Material::find($this->deleteId);

        if ($material) {
            // Also delete linked AHSP base prices
            AhspBasePrice::where('material_id', $material->id)->delete();

            // Soft delete the material
            $material->delete();

            session()->flash('success', 'Material dan data AHSP terkait berhasil dihapus.');
        }

        $this->closeDeleteModal();
    }

    public function restore(int $id)
    {
        if (!auth()->user()->can('materials.manage')) {
            abort(403);
        }

        $material = Material::withTrashed()->find($id);
        if ($material) {
            $material->restore();

            // Restore linked AHSP base prices if any
            AhspBasePrice::onlyTrashed()
                ->where('material_id', $material->id)
                ->restore();

            session()->flash('success', 'Material berhasil dipulihkan.');
        }
    }

    public function forceDelete(int $id)
    {
        if (!auth()->user()->can('materials.manage')) {
            abort(403);
        }

        $material = Material::withTrashed()->find($id);

        if ($material) {
            // Permanently delete linked AHSP base prices
            AhspBasePrice::withTrashed()
                ->where('material_id', $material->id)
                ->forceDelete();

            // Permanently delete material
            $material->forceDelete();

            session()->flash('success', 'Material dan data AHSP terkait dihapus permanen.');
        }
    }

    // Export
    public function export()
    {
        if (!auth()->user()->can('financials.view')) {
            abort(403);
        }

        return Excel::download(new MaterialsExport, 'materials-' . date('Y-m-d') . '.xlsx');
    }

    public function render()
    {
        $query = Material::query();

        if ($this->showTrashed) {
            $query->onlyTrashed();
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        if ($this->regionFilter) {
            $query->where('region_code', $this->regionFilter);
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        $materials = $query->orderBy('category')->orderBy('name')->paginate(15);
        $categories = Material::distinct()->pluck('category')->filter();
        $regions = Material::distinct()->whereNotNull('region_code')
            ->select('region_code', 'region_name')
            ->get()
            ->unique('region_code');

        return view('livewire.material-manager', [
            'materials' => $materials,
            'categories' => $categories,
            'regions' => $regions,
        ]);
    }
}

