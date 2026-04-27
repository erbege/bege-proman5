<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InventoryHistoryRequest;
use App\Http\Requests\Api\InventoryIndexRequest;
use App\Models\Inventory;
use App\Models\InventoryLog;

class InventoryController extends Controller
{
    use ApiResponse;

    /**
     * List inventory items.
     * 
     * Get a paginated list of inventory items.
     */
    public function index(InventoryIndexRequest $request)
    {
        $validated = $request->validated();
        $query = Inventory::with(['project:id,name', 'material:id,code,name,unit']);

        if (!empty($validated['project_id'])) {
            $query->where('project_id', $validated['project_id']);
        }

        if (!empty($validated['material_id'])) {
            $query->where('material_id', $validated['material_id']);
        }

        $items = $query->latest()->paginate($validated['per_page'] ?? 15);

        return $this->paginatedResponse('Inventory items retrieved successfully.', $items);
    }

    /**
     * Get inventory item details.
     * 
     * Get detailed information about a specific inventory item.
     */
    public function show(Inventory $inventory)
    {
        $data = $inventory->load(['project', 'material']);

        return $this->successResponse('Inventory item retrieved successfully.', $data);
    }

    /**
     * Get inventory history/logs.
     * 
     * Get a paginated list of inventory transaction logs.
     */
    public function history(InventoryHistoryRequest $request)
    {
        $validated = $request->validated();
        $query = InventoryLog::with(['inventory.material', 'inventory.project', 'user:id,name']);

        if (!empty($validated['project_id'])) {
            $query->whereHas('inventory', fn($q) => $q->where('project_id', $validated['project_id']));
        }

        if (!empty($validated['material_id'])) {
            $query->whereHas('inventory', fn($q) => $q->where('material_id', $validated['material_id']));
        }

        $logs = $query->latest()->paginate($validated['per_page'] ?? 15);

        return $this->paginatedResponse('Inventory history retrieved successfully.', $logs);
    }
}
