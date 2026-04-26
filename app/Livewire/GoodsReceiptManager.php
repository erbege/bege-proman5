<?php

namespace App\Livewire;

use App\Models\GoodsReceipt;
use App\Models\Inventory;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class GoodsReceiptManager extends Component
{
    use WithPagination;

    public Project $project;

    // Filter
    public string $search = '';

    // Modal
    public bool $showModal = false;
    public ?int $selectedPoId = null;

    // Form
    public string $receiptDate = '';
    public string $deliveryNoteNumber = '';
    public string $notes = '';
    public array $items = [];

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->receiptDate = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal(?int $poId = null)
    {
        $this->resetValidation();
        $this->reset(['receiptDate', 'deliveryNoteNumber', 'notes', 'items', 'selectedPoId']);
        $this->receiptDate = now()->format('Y-m-d');

        if ($poId) {
            $this->selectedPoId = $poId;
            $po = PurchaseOrder::with(['items.material'])->find($poId);
            if ($po && in_array($po->status, ['sent', 'partial'])) {
                $this->items = [];
                foreach ($po->items as $poItem) {
                    $remaining = $poItem->quantity - $poItem->received_qty;
                    if ($remaining > 0) {
                        $this->items[] = [
                            'purchase_order_item_id' => $poItem->id,
                            'material_name' => $poItem->material->name,
                            'unit' => $poItem->material->unit,
                            'ordered_qty' => $poItem->quantity,
                            'received_previously' => $poItem->received_qty,
                            'remaining_qty' => $remaining,
                            'received_qty' => $remaining,
                            'notes' => '',
                        ];
                    }
                }
            }
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['receiptDate', 'deliveryNoteNumber', 'notes', 'items', 'selectedPoId']);
    }

    public function save()
    {
        $this->validate([
            'selectedPoId' => 'required|exists:purchase_orders,id',
            'receiptDate' => 'required|date',
            'deliveryNoteNumber' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.received_qty' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string',
        ]);

        $po = PurchaseOrder::findOrFail($this->selectedPoId);

        DB::transaction(function () use ($po) {
            $gr = GoodsReceipt::create([
                'project_id' => $this->project->id,
                'purchase_order_id' => $po->id,
                'receipt_date' => $this->receiptDate,
                'delivery_note_number' => $this->deliveryNoteNumber,
                'notes' => $this->notes ?: null,
                'received_by' => auth()->id(),
            ]);

            foreach ($this->items as $item) {
                $poItem = PurchaseOrderItem::find($item['purchase_order_item_id']);

                $gr->items()->create([
                    'purchase_order_item_id' => $poItem->id,
                    'material_id' => $poItem->material_id,
                    'quantity' => $item['received_qty'],
                    'notes' => $item['notes'] ?: null,
                ]);

                // Update PO Item received qty
                $poItem->received_qty += $item['received_qty'];
                $poItem->save();

                // Update Inventory
                $inventory = Inventory::firstOrCreate(
                    ['project_id' => $this->project->id, 'material_id' => $poItem->material_id],
                    ['quantity' => 0, 'reserved_qty' => 0]
                );

                $inventory->addStock(
                    $item['received_qty'],
                    'GoodsReceipt',
                    $gr->id,
                    'GR: ' . $gr->gr_number,
                    auth()->id()
                );
            }

            // Update PO Status
            $po->updateReceiveStatus();
        });

        session()->flash('success', 'Penerimaan Barang berhasil dicatat.');
        $this->closeModal();
    }

    public function render()
    {
        $query = $this->project->goodsReceipts()->with(['purchaseOrder.supplier', 'receivedBy', 'items'])->latest();

        if ($this->search) {
            $query->where('gr_number', 'like', '%' . $this->search . '%')
                ->orWhere('delivery_note_number', 'like', '%' . $this->search . '%');
        }

        $receipts = $query->paginate(10);
        $activePOs = $this->project->purchaseOrders()->whereIn('status', ['sent', 'partial'])->with('supplier')->latest()->get();

        return view('livewire.goods-receipt-manager', [
            'receipts' => $receipts,
            'activePOs' => $activePOs,
        ]);
    }
}
