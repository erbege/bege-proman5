<?php

namespace App\Livewire;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class MaterialRequestManager extends Component
{
    use WithPagination;

    public Project $project;

    // Filter
    public string $search = '';
    public string $statusFilter = '';

    // Modal
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showApprovalModal = false;
    public ?int $editingId = null;

    // Form
    public string $requestDate = '';
    public string $notes = '';
    public array $items = [];

    // Delete
    public ?int $deleteId = null;
    public string $deleteName = '';

    // Approval
    public ?int $approvalId = null;
    public string $approvalAction = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->requestDate = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    // Modal CRUD
    public function openModal(?int $id = null)
    {
        $this->resetValidation();
        $this->reset(['requestDate', 'notes', 'items', 'editingId']);
        $this->requestDate = now()->format('Y-m-d');

        if ($id) {
            $mr = MaterialRequest::with('items')->find($id);
            if ($mr->status !== 'pending') {
                session()->flash('error', 'Hanya MR status pending yang bisa diedit.');
                return;
            }
            $this->editingId = $id;
            $this->requestDate = $mr->request_date->format('Y-m-d');
            $this->notes = $mr->notes ?? '';
            $this->items = $mr->items->map(function ($item) {
                return [
                    'material_id' => $item->material_id,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'notes' => $item->notes ?? '',
                ];
            })->toArray();
        } else {
            $this->items = [
                ['material_id' => '', 'quantity' => 1, 'unit' => '', 'notes' => ''],
            ];
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['requestDate', 'notes', 'items', 'editingId']);
    }

    public function addItem()
    {
        $this->items[] = ['material_id' => '', 'quantity' => 1, 'unit' => '', 'notes' => ''];
    }

    public function removeItem(int $index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, $key)
    {
        // Auto-fill unit when material is selected
        if (str_ends_with($key, '.material_id') && $value) {
            $index = explode('.', $key)[0];
            $material = Material::find($value);
            if ($material) {
                $this->items[$index]['unit'] = $material->unit;
            }
        }
    }

    public function save()
    {
        $this->validate([
            'requestDate' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:20',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::transaction(function () {
            if ($this->editingId) {
                $mr = MaterialRequest::find($this->editingId);
                $mr->update([
                    'request_date' => $this->requestDate,
                    'notes' => $this->notes ?: null,
                ]);
                $mr->items()->delete();
            } else {
                $count = MaterialRequest::where('project_id', $this->project->id)->count() + 1;
                $code = 'MR-' . $this->project->code . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

                $mr = MaterialRequest::create([
                    'project_id' => $this->project->id,
                    'requested_by' => auth()->id(),
                    'code' => $code,
                    'request_date' => $this->requestDate,
                    'status' => 'pending',
                    'notes' => $this->notes ?: null,
                ]);
            }

            foreach ($this->items as $item) {
                $mr->items()->create([
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'notes' => $item['notes'] ?: null,
                ]);
            }
        });

        session()->flash('success', $this->editingId ? 'Material Request berhasil diperbarui.' : 'Material Request berhasil dibuat.');
        $this->closeModal();
    }

    // Delete
    public function confirmDelete(int $id, string $code)
    {
        $this->deleteId = $id;
        $this->deleteName = $code;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->reset(['deleteId', 'deleteName']);
    }

    public function delete()
    {
        $mr = MaterialRequest::find($this->deleteId);
        if ($mr->status !== 'pending') {
            session()->flash('error', 'Hanya MR status pending yang bisa dihapus.');
            $this->closeDeleteModal();
            return;
        }
        $mr->delete();
        session()->flash('success', 'Material Request berhasil dihapus.');
        $this->closeDeleteModal();
    }

    // Approval
    public function openApprovalModal(int $id, string $action)
    {
        $this->approvalId = $id;
        $this->approvalAction = $action;
        $this->showApprovalModal = true;
    }

    public function closeApprovalModal()
    {
        $this->showApprovalModal = false;
        $this->reset(['approvalId', 'approvalAction']);
    }

    public function processApproval()
    {
        $mr = MaterialRequest::find($this->approvalId);
        $mr->update(['status' => $this->approvalAction]);
        session()->flash('success', 'Status MR berhasil diperbarui ke ' . ucfirst($this->approvalAction) . '.');
        $this->closeApprovalModal();
    }

    public function render()
    {
        $query = $this->project->materialRequests()->with(['user', 'items'])->latest();

        if ($this->search) {
            $query->where('code', 'like', '%' . $this->search . '%');
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $requests = $query->paginate(10);
        $materials = Material::active()->orderBy('name')->get();

        return view('livewire.material-request-manager', [
            'requests' => $requests,
            'materials' => $materials,
        ]);
    }
}
