<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    /**
     * List purchase orders.
     * 
     * Get a paginated list of purchase orders.
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['project:id,name', 'supplier:id,name']);

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return $query->latest()->paginate($request->per_page ?? 15);
    }

    /**
     * Get purchase order details.
     * 
     * Get detailed information about a specific purchase order.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        return response()->json([
            'data' => $purchaseOrder->load(['project', 'supplier', 'items.material', 'createdBy'])
        ]);
    }

    /**
     * Create new purchase order.
     * 
     * Create a new purchase order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $purchaseOrder = PurchaseOrder::create([
            'project_id' => $validated['project_id'],
            'supplier_id' => $validated['supplier_id'],
            'created_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
            'status' => 'draft',
            'total_amount' => collect($validated['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']),
        ]);

        foreach ($validated['items'] as $item) {
            $purchaseOrder->items()->create([
                'material_id' => $item['material_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return response()->json([
            'message' => 'Purchase order created successfully',
            'data' => $purchaseOrder->load('items.material')
        ], 201);
    }
}
