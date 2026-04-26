<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\Inventory;
use Illuminate\Http\Request;

class GoodsReceiptController extends Controller
{
    /**
     * List goods receipts.
     * 
     * Get a paginated list of goods receipts.
     */
    public function index(Request $request)
    {
        $query = GoodsReceipt::with(['project:id,name', 'purchaseOrder:id,po_number', 'receivedBy:id,name']);

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        return $query->latest()->paginate($request->per_page ?? 15);
    }

    /**
     * Get goods receipt details.
     * 
     * Get detailed information about a specific goods receipt.
     */
    public function show(GoodsReceipt $goodsReceipt)
    {
        return response()->json([
            'data' => $goodsReceipt->load(['project', 'purchaseOrder', 'items.material', 'receivedBy'])
        ]);
    }

    /**
     * Create new goods receipt.
     * 
     * Create a new goods receipt and update inventory.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $goodsReceipt = GoodsReceipt::create([
            'project_id' => $validated['project_id'],
            'purchase_order_id' => $validated['purchase_order_id'] ?? null,
            'received_by' => auth()->id(),
            'received_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            $goodsReceipt->items()->create($item);

            // Update inventory
            Inventory::updateOrCreate(
                ['project_id' => $validated['project_id'], 'material_id' => $item['material_id']],
                ['quantity' => \DB::raw("quantity + {$item['quantity']}")]
            );
        }

        return response()->json([
            'message' => 'Goods receipt created successfully',
            'data' => $goodsReceipt->load('items.material')
        ], 201);
    }
}
