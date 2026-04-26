<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryLog;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * List inventory items.
     * 
     * Get a paginated list of inventory items.
     */
    public function index(Request $request)
    {
        $query = Inventory::with(['project:id,name', 'material:id,code,name,unit']);

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('material_id')) {
            $query->where('material_id', $request->material_id);
        }

        return $query->latest()->paginate($request->per_page ?? 15);
    }

    /**
     * Get inventory item details.
     * 
     * Get detailed information about a specific inventory item.
     */
    public function show(Inventory $inventory)
    {
        return response()->json([
            'data' => $inventory->load(['project', 'material'])
        ]);
    }

    /**
     * Get inventory history/logs.
     * 
     * Get a paginated list of inventory transaction logs.
     */
    public function history(Request $request)
    {
        $query = InventoryLog::with(['inventory.material', 'inventory.project', 'user:id,name']);

        if ($request->has('project_id')) {
            $query->whereHas('inventory', fn($q) => $q->where('project_id', $request->project_id));
        }

        if ($request->has('material_id')) {
            $query->whereHas('inventory', fn($q) => $q->where('material_id', $request->material_id));
        }

        return $query->latest()->paginate($request->per_page ?? 15);
    }
}
