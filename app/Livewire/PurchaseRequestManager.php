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
            $mr = MaterialRequest::with('items.material')->find($mrId);
            if ($mr && $mr->status === 'approved') {
                $this->fromMrId = $mrId;
                $this->items = $mr->items
                    ->filter(fn($item) => $item->remaining_to_order > 0)
                    ->map(function ($item) {
                        return [
                            'material_id' => $item->material_id,
                            'material_request_item_id' => $item->id,
                            'quantity' => $item->remaining_to_order,
                            'estimated_price' => 0,
                            'notes' => $item->notes ?? '',
                        ];
                    })->toArray();
                
                if (empty($this->items)) {
                    session()->flash('error', 'Semua item di MR ini sudah diproses.');
                    return;
                }
            }
        } else {
            $this->items = [
                ['material_id' => '', 'material_request_item_id' => null, 'quantity' => 1, 'estimated_price' => 0, 'notes' => ''],
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
        $this->items[] = ['material_id' => '', 'material_request_item_id' => null, 'quantity' => 1, 'estimated_price' => 0, 'notes' => ''];
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
            'items.*.material_request_item_id' => 'nullable|exists:material_request_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.estimated_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        try {
            $service = app(\App\Services\PurchaseRequestService::class);
            $service->createPurchaseRequest([
                'project_id' => $this->project->id,
                'required_date' => $this->requiredDate,
                'priority' => $this->priority,
                'notes' => $this->notes,
                'items' => $this->items,
            ], auth()->id());

            session()->flash('success', 'Purchase Request berhasil dibuat.');
            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuat PR: ' . $e->getMessage());
        }
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
