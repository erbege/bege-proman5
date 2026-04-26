<?php

namespace App\Livewire;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class PurchaseRequestManager extends Component
{
    use WithPagination;

    public Project $project;

    // Filter
    public string $search = '';
    public string $statusFilter = '';

    // Modal
    public bool $showModal = false;
    public bool $showApprovalModal = false;
    public ?int $editingId = null;
    public ?int $fromMrId = null;

    // Form
    public string $requiredDate = '';
    public string $priority = 'normal';
    public string $notes = '';
    public array $items = [];

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
        $this->requiredDate = now()->addDays(7)->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openModal(?int $mrId = null)
    {
        $this->resetValidation();
        $this->reset(['requiredDate', 'priority', 'notes', 'items', 'editingId', 'fromMrId']);
        $this->requiredDate = now()->addDays(7)->format('Y-m-d');
        $this->priority = 'normal';

        if ($mrId) {
            $mr = MaterialRequest::with('items')->find($mrId);
            if ($mr && $mr->status === 'approved') {
                $this->fromMrId = $mrId;
                $this->items = $mr->items->map(function ($item) {
                    return [
                        'material_id' => $item->material_id,
                        'quantity' => $item->quantity,
                        'estimated_price' => 0,
                        'notes' => $item->notes ?? '',
                    ];
                })->toArray();
            }
        } else {
            $this->items = [
                ['material_id' => '', 'quantity' => 1, 'estimated_price' => 0, 'notes' => ''],
            ];
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['requiredDate', 'priority', 'notes', 'items', 'editingId', 'fromMrId']);
    }

    public function addItem()
    {
        $this->items[] = ['material_id' => '', 'quantity' => 1, 'estimated_price' => 0, 'notes' => ''];
    }

    public function removeItem(int $index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save()
    {
        $this->validate([
            'requiredDate' => 'required|date',
            'priority' => 'required|in:low,normal,high,urgent',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.estimated_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::transaction(function () {
            $pr = PurchaseRequest::create([
                'project_id' => $this->project->id,
                'request_date' => now(),
                'required_date' => $this->requiredDate,
                'status' => 'pending',
                'priority' => $this->priority,
                'notes' => $this->notes . ($this->fromMrId ? " (From MR ID: {$this->fromMrId})" : ""),
                'requested_by' => auth()->id(),
            ]);

            foreach ($this->items as $item) {
                $pr->items()->create([
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                    'estimated_price' => $item['estimated_price'] ?? 0,
                    'notes' => $item['notes'] ?: null,
                ]);
            }

            if ($this->fromMrId) {
                MaterialRequest::find($this->fromMrId)->update(['status' => 'processed']);
            }
        });

        session()->flash('success', 'Purchase Request berhasil dibuat.');
        $this->closeModal();
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
        $pr = PurchaseRequest::find($this->approvalId);
        $data = ['status' => $this->approvalAction];
        if ($this->approvalAction === 'approved') {
            $data['approved_by'] = auth()->id();
            $data['approved_at'] = now();
        }
        $pr->update($data);
        session()->flash('success', 'Status PR berhasil diperbarui.');
        $this->closeApprovalModal();
    }

    public function render()
    {
        $query = $this->project->purchaseRequests()->with(['requestedBy', 'items'])->latest();

        if ($this->search) {
            $query->where('pr_number', 'like', '%' . $this->search . '%');
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $prs = $query->paginate(10);
        $materials = Material::active()->orderBy('name')->get();
        $approvedMrs = $this->project->materialRequests()->where('status', 'approved')->get();

        return view('livewire.purchase-request-manager', [
            'prs' => $prs,
            'materials' => $materials,
            'approvedMrs' => $approvedMrs,
        ]);
    }
}
