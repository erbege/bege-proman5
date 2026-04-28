<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Services\GoodsReceiptService;
use Illuminate\Http\Request;

class GoodsReceiptController extends Controller
{
    protected $grService;

    public function __construct(GoodsReceiptService $grService)
    {
        $this->grService = $grService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        $this->authorize('gr.view');

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
        $this->authorize('gr.create');

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
        $this->authorize('gr.create');

        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'receipt_date' => 'required|date',
            'delivery_note_number' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.received_qty' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string',
        ]);

        // Transform items for service
        $items = array_map(function($item) {
            return [
                'purchase_order_item_id' => $item['purchase_order_item_id'],
                'material_id' => $item['material_id'],
                'quantity' => $item['received_qty'],
                'notes' => $item['notes'],
            ];
        }, $validated['items']);

        try {
            $this->grService->createGoodsReceipt([
                'project_id' => $project->id,
                'purchase_order_id' => $validated['purchase_order_id'],
                'receipt_date' => $validated['receipt_date'],
                'delivery_note_number' => $validated['delivery_note_number'],
                'notes' => $validated['notes'],
                'items' => $items,
            ], auth()->id());

            return redirect()->route('projects.gr.index', $project)->with('success', 'Penerimaan Barang berhasil dicatat dan diajukan untuk approval.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, GoodsReceipt $gr)
    {
        $this->authorize('gr.view');

        $gr->load(['items.material', 'receivedBy', 'purchaseOrder.supplier', 'approvalLogs.user']);
        return view('projects.gr.show', compact('project', 'gr'));
    }

    /**
     * Update status (Approval).
     */
    public function updateStatus(Request $request, Project $project, GoodsReceipt $gr)
    {
        $this->authorize('gr.approve');

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'comment' => 'nullable|string|max:500'
        ]);

        try {
            if ($request->status === 'approved') {
                $this->grService->approvalService()->approve($gr, $request->comment);
                
                // If fully approved, finalize (update inventory)
                if ($gr->is_fully_approved) {
                    $this->grService->finalize($gr);
                }
            } else {
                $this->grService->approvalService()->reject($gr, $request->comment ?? 'Rejected by user');
            }

            return back()->with('success', 'Status GR berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
