<?php

namespace App\Livewire;

use App\Models\Material;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class PurchaseOrderManager extends Component
{
    use WithPagination;

    public Project $project;

    // Filter
    public string $search = '';
    public string $statusFilter = '';

    // Modal
    public bool $showModal = false;

    // Form
    public string $supplierId = '';
    public string $orderDate = '';
    public string $expectedDelivery = '';
    public string $paymentTerms = '';
    public string $notes = '';
    public float $taxAmount = 0;
    public float $discountAmount = 0;
    public array $prIds = [];
    public array $items = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->orderDate = now()->format('Y-m-d');
        $this->expectedDelivery = now()->addDays(7)->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->reset(['supplierId', 'orderDate', 'expectedDelivery', 'paymentTerms', 'notes', 'taxAmount', 'discountAmount', 'prIds', 'items']);
        $this->orderDate = now()->format('Y-m-d');
        $this->expectedDelivery = now()->addDays(7)->format('Y-m-d');
        $this->items = [['material_id' => '', 'quantity' => 1, 'unit_price' => 0, 'notes' => '']];
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function addItem()
    {
        $this->items[] = ['material_id' => '', 'quantity' => 1, 'unit_price' => 0, 'notes' => ''];
    }

    public function removeItem(int $index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function loadFromPr(int $prId)
    {
        $pr = PurchaseRequest::with('items')->find($prId);
        if (!$pr)
            return;

        $this->prIds[] = $prId;
        foreach ($pr->items as $item) {
            $this->items[] = [
                'material_id' => $item->material_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->estimated_price ?? 0,
                'notes' => $item->notes ?? '',
            ];
        }
    }

    public function save()
    {
        $this->validate([
            'supplierId' => 'required|exists:suppliers,id',
            'orderDate' => 'required|date',
            'expectedDelivery' => 'required|date|after_or_equal:orderDate',
            'paymentTerms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'taxAmount' => 'nullable|numeric|min:0',
            'discountAmount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::transaction(function () {
            $subtotal = 0;
            foreach ($this->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }
            $total = $subtotal + $this->taxAmount - $this->discountAmount;

            $po = PurchaseOrder::create([
                'project_id' => $this->project->id,
                'supplier_id' => $this->supplierId,
                'purchase_request_id' => count($this->prIds) === 1 ? $this->prIds[0] : null,
                'order_date' => $this->orderDate,
                'expected_delivery' => $this->expectedDelivery,
                'status' => 'sent',
                'payment_terms' => $this->paymentTerms ?: null,
                'notes' => $this->notes ?: null,
                'subtotal' => $subtotal,
                'tax_amount' => $this->taxAmount,
                'discount_amount' => $this->discountAmount,
                'total_amount' => $total,
                'created_by' => auth()->id(),
            ]);

            foreach ($this->items as $item) {
                $po->items()->create([
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'notes' => $item['notes'] ?: null,
                ]);
            }

            if (!empty($this->prIds)) {
                PurchaseRequest::whereIn('id', $this->prIds)->update(['status' => 'completed']);
            }
        });

        session()->flash('success', 'Purchase Order berhasil dibuat.');
        $this->closeModal();
    }

    public function deletePo(int $id)
    {
        $po = PurchaseOrder::find($id);
        if (!in_array($po->status, ['draft', 'sent'])) {
            session()->flash('error', 'Hanya PO status Draft atau Sent yang dapat dihapus.');
            return;
        }
        $po->delete();
        session()->flash('success', 'Purchase Order berhasil dihapus.');
    }

    public function render()
    {
        $query = $this->project->purchaseOrders()->with(['supplier', 'createdBy'])->latest();

        if ($this->search) {
            $query->where('po_number', 'like', '%' . $this->search . '%');
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $orders = $query->paginate(10);
        $suppliers = Supplier::active()->orderBy('name')->get();
        $materials = Material::active()->orderBy('name')->get();
        $approvedPrs = $this->project->purchaseRequests()->where('status', 'approved')->with('items')->get();

        return view('livewire.purchase-order-manager', [
            'orders' => $orders,
            'suppliers' => $suppliers,
            'materials' => $materials,
            'approvedPrs' => $approvedPrs,
        ]);
    }
}
