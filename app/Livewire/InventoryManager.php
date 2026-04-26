<?php

namespace App\Livewire;

use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class InventoryManager extends Component
{
    use WithPagination;

    // Search & Filter
    public string $search = '';
    public string $projectFilter = '';
    public string $viewMode = 'stock'; // stock | history

    // Adjustment Modal
    public bool $showAdjustModal = false;
    public ?int $adjustingInventoryId = null;
    public ?string $adjustingMaterialName = null;
    public float $currentStock = 0;
    public string $adjustType = 'in';
    public float $adjustQuantity = 0;
    public string $adjustNotes = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'projectFilter' => ['except' => ''],
        'viewMode' => ['except' => 'stock'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingProjectFilter()
    {
        $this->resetPage();
    }

    public function setViewMode(string $mode)
    {
        $this->viewMode = $mode;
        $this->resetPage();
    }

    // Adjustment Modal
    public function openAdjustModal(int $inventoryId)
    {
        $inventory = Inventory::with('material')->find($inventoryId);
        $this->adjustingInventoryId = $inventoryId;
        $this->adjustingMaterialName = $inventory->material->name;
        $this->currentStock = $inventory->quantity;
        $this->adjustType = 'in';
        $this->adjustQuantity = 0;
        $this->adjustNotes = '';
        $this->showAdjustModal = true;
    }

    public function closeAdjustModal()
    {
        $this->showAdjustModal = false;
        $this->reset(['adjustingInventoryId', 'adjustingMaterialName', 'currentStock', 'adjustType', 'adjustQuantity', 'adjustNotes']);
    }

    public function saveAdjustment()
    {
        $this->validate([
            'adjustQuantity' => 'required|numeric|min:0.01',
            'adjustType' => 'required|in:in,out,adjustment',
            'adjustNotes' => 'required|string|min:3',
        ]);

        $inventory = Inventory::find($this->adjustingInventoryId);

        DB::transaction(function () use ($inventory) {
            $qty = $this->adjustQuantity;

            if ($this->adjustType === 'out') {
                $inventory->removeStock($qty, 'adjustment', null, $this->adjustNotes);
            } elseif ($this->adjustType === 'in') {
                $inventory->addStock($qty, 'adjustment', null, $this->adjustNotes);
            } else {
                // Set absolute value
                $oldStock = $inventory->quantity;
                $delta = $qty - $oldStock;
                if ($delta > 0) {
                    $inventory->addStock($delta, 'adjustment', null, $this->adjustNotes);
                } elseif ($delta < 0) {
                    $inventory->removeStock(abs($delta), 'adjustment', null, $this->adjustNotes);
                }
            }
        });

        session()->flash('success', 'Stok berhasil diperbarui.');
        $this->closeAdjustModal();
    }

    public function render()
    {
        $projects = Project::orderBy('name')->get();

        if ($this->viewMode === 'history') {
            $query = InventoryLog::with(['inventory.material', 'inventory.project', 'user'])->latest();

            if ($this->projectFilter) {
                $query->whereHas('inventory', function ($q) {
                    $q->where('project_id', $this->projectFilter);
                });
            }

            if ($this->search) {
                $query->whereHas('inventory.material', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            }

            $logs = $query->paginate(20);

            return view('livewire.inventory-manager', [
                'projects' => $projects,
                'inventories' => collect(),
                'logs' => $logs,
            ]);
        }

        // Stock view
        $query = Inventory::with(['material', 'project']);

        if ($this->projectFilter) {
            $query->where('project_id', $this->projectFilter);
        }

        if ($this->search) {
            $query->whereHas('material', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        $inventories = $query->paginate(20);

        return view('livewire.inventory-manager', [
            'projects' => $projects,
            'inventories' => $inventories,
            'logs' => collect(),
        ]);
    }
}
