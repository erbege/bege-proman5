<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\Inventory;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        $receipts = $project->goodsReceipts()
            ->with(['purchaseOrder.supplier', 'receivedBy', 'items'])
            ->latest()
            ->paginate(10);

        return view('projects.gr.index', compact('project', 'receipts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Project $project)
    {
        $poId = request('po_id');
        $po = null;
        $items = [];

        if ($poId) {
            $po = PurchaseOrder::with(['items.material', 'supplier'])->find($poId);
            if ($po && in_array($po->status, ['sent', 'partial'])) {
                // Prepare items for reception (only those needing receipt)
                foreach ($po->items as $poItem) {
                    $remaining = $poItem->quantity - $poItem->received_qty;
                    if ($remaining > 0) {
                        $items[] = [
                            'purchase_order_item_id' => $poItem->id,
                            'material_id' => $poItem->material_id,
                            'material_name' => $poItem->material->name,
                            'unit' => $poItem->material->unit,
                            'ordered_qty' => $poItem->quantity,
                            'received_previously' => $poItem->received_qty,
                            'remaining_qty' => $remaining,
                            'received_qty' => $remaining, // Default to full remaining
                            'notes' => '',
                        ];
                    }
                }
            } else {
                return redirect()->route('projects.gr.create', $project)->with('error', 'PO tidak valid atau sudah selesai.');
            }
            return view('projects.gr.create', compact('project', 'po', 'items'));
        }

        // List active POs for selection
        $activePos = $project->purchaseOrders()
            ->whereIn('status', ['sent', 'partial'])
            ->with('supplier')
            ->latest()
            ->get();

        return view('projects.gr.select_po', compact('project', 'activePos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'receipt_date' => 'required|date',
            'delivery_note_number' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.received_qty' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string',
        ]);

        $po = PurchaseOrder::findOrFail($validated['purchase_order_id']);

        DB::transaction(function () use ($validated, $project, $po) {
            
            $gr = GoodsReceipt::create([
                'project_id' => $project->id,
                'purchase_order_id' => $po->id,
                'receipt_date' => $validated['receipt_date'],
                'delivery_note_number' => $validated['delivery_note_number'],
                'notes' => $validated['notes'],
                'received_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $poItem = PurchaseOrderItem::find($item['purchase_order_item_id']);
                
                // Validate over-receiving?
                $remaining = $poItem->quantity - $poItem->received_qty;
                if ($item['received_qty'] > $remaining + 0.0001) { // Floating point tolerance
                     // For now allow simple warning or clamp, but here we proceed as trusting user (maybe over-delivery allowed?)
                     // Ideally strict check.
                }

                $gr->items()->create([
                    'purchase_order_item_id' => $poItem->id,
                    'material_id' => $poItem->material_id,
                    'quantity' => $item['received_qty'],
                    'notes' => $item['notes'] ?? null,
                ]);

                // Update PO Item
                $poItem->received_qty += $item['received_qty'];
                $poItem->save();

                // Update Inventory with Row Lock for atomic calculation
                $inventory = Inventory::firstOrCreate(
                    ['project_id' => $project->id, 'material_id' => $poItem->material_id],
                    ['quantity' => 0, 'reserved_qty' => 0, 'average_cost' => 0]
                );
                
                // Reload with lock for update
                $inventory = Inventory::where('id', $inventory->id)->lockForUpdate()->first();

                // Calculate Moving Average Cost
                $oldQty = (float) $inventory->quantity;
                $oldAvgCost = (float) $inventory->average_cost;
                $newQty = (float) $item['received_qty'];
                $unitPrice = (float) $poItem->unit_price;

                $totalNewQty = $oldQty + $newQty;
                if ($totalNewQty > 0) {
                    $inventory->average_cost = (($oldQty * $oldAvgCost) + ($newQty * $unitPrice)) / $totalNewQty;
                    $inventory->save();
                }

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

        return redirect()->route('projects.gr.index', $project)->with('success', 'Penerimaan Barang berhasil dicatat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, GoodsReceipt $gr)
    {
        $gr->load(['items.material', 'receivedBy', 'purchaseOrder.supplier']);
        return view('projects.gr.show', compact('project', 'gr'));
    }
}
