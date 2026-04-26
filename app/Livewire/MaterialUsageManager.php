<?php

namespace App\Livewire;

use App\Models\Inventory;
use App\Models\MaterialUsage;
use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MaterialUsageManager extends Component
{
    use WithPagination;

    public Project $project;

    // Filter
    public string $search = '';

    // Modal
    public bool $showModal = false;
    public bool $showDetailModal = false;
    public ?MaterialUsage $selectedUsage = null;

    // Form
    public string $usageDate = '';
    public string $rabItemId = '';
    public string $notes = '';
    public array $items = [];

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->usageDate = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->reset(['usageDate', 'rabItemId', 'notes', 'items']);
        $this->usageDate = now()->format('Y-m-d');
        $this->items = [['material_id' => '', 'quantity' => 1, 'notes' => '', 'available' => 0, 'unit' => '']];
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function showDetail($id)
    {
        $this->selectedUsage = MaterialUsage::with(['items.material', 'createdBy', 'rabItem'])->find($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedUsage = null;
    }

    public function addItem()
    {
        $this->items[] = ['material_id' => '', 'quantity' => 1, 'notes' => '', 'available' => 0, 'unit' => ''];
    }

    public function removeItem(int $index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, $key)
    {
        // Update available stock when material is selected
        if (str_ends_with($key, '.material_id') && $value) {
            $index = explode('.', $key)[0];
            $inventory = Inventory::where('project_id', $this->project->id)
                ->where('material_id', $value)
                ->with('material')
                ->first();
            if ($inventory) {
                $this->items[$index]['available'] = $inventory->quantity;
                $this->items[$index]['unit'] = $inventory->material->unit;
            }
        }
    }

    public function save()
    {
        $this->validate([
            'usageDate' => 'required|date',
            'rabItemId' => 'nullable|exists:rab_items,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () {
                $usage = MaterialUsage::create([
                    'project_id' => $this->project->id,
                    'rab_item_id' => $this->rabItemId ?: null,
                    'usage_date' => $this->usageDate,
                    'notes' => $this->notes ?: null,
                    'created_by' => auth()->id(),
                ]);

                foreach ($this->items as $itemData) {
                    $materialId = $itemData['material_id'];
                    $qty = $itemData['quantity'];

                    $inventory = Inventory::where('project_id', $this->project->id)
                        ->where('material_id', $materialId)
                        ->first();

                    if (!$inventory) {
                        throw ValidationException::withMessages([
                            'items' => "Material tidak ada di stok proyek ini."
                        ]);
                    }

                    if ($inventory->quantity < $qty) {
                        throw ValidationException::withMessages([
                            'items' => "Stok untuk material {$inventory->material->name} tidak mencukupi. Tersedia: {$inventory->quantity}"
                        ]);
                    }

                    $inventory->removeStock($qty, 'usage', $usage->id, $itemData['notes'] ?? null);

                    $usage->items()->create([
                        'material_id' => $materialId,
                        'quantity' => $qty,
                        'notes' => $itemData['notes'] ?? null,
                    ]);
                }
            });

            session()->flash('success', 'Penggunaan material berhasil dicatat.');
            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $query = $this->project->materialUsages()->with(['items.material', 'createdBy', 'rabItem'])->latest('usage_date');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('usage_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('rabItem', function ($q2) {
                        $q2->where('work_name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        $usages = $query->paginate(20);

        // Get available stock - formatted for searchable-select
        $inventories = Inventory::where('project_id', $this->project->id)
            ->where('quantity', '>', 0)
            ->with('material')
            ->get();

        $materialOptions = $inventories->map(fn($inv) => [
            'id' => $inv->material_id,
            'name' => $inv->material->code . ' - ' . $inv->material->name . ' (' . number_format($inv->quantity, 2) . ' ' . $inv->material->unit . ')',
        ])->values()->toArray();

        // Get RAB items for reference - formatted for searchable-select
        $rabItems = $this->project->rabItems()->orderBy('work_name')->get();
        $rabItemOptions = $rabItems->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->work_name,
        ])->values()->toArray();

        return view('livewire.material-usage-manager', [
            'usages' => $usages,
            'inventories' => $inventories,
            'materialOptions' => $materialOptions,
            'rabItemOptions' => $rabItemOptions,
        ]);
    }
}
