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
     */
    public function index(InventoryIndexRequest $request)
    {
        $this->authorize('inventory.view');
        $validated = $request->validated();
        $query = Inventory::with(['project:id,name', 'material:id,code,name,unit']);

        if (!empty($validated['project_id'])) {
            $query->where('project_id', $validated['project_id']);
        }

        if (!empty($validated['material_id'])) {
            $query->where('material_id', $validated['material_id']);
        }

        $items = $query->latest()->paginate($validated['per_page'] ?? 15);

        // Mask costs
        if (!auth()->user()->can('financials.view')) {
            $items->getCollection()->transform(function ($item) {
                $item->makeHidden(['average_cost', 'total_value']);
                return $item;
            });
        }

        return $this->paginatedResponse('Inventory items retrieved successfully.', $items);
    }

    /**
     * Get inventory item details.
     */
    public function show(Inventory $inventory)
    {
        $this->authorize('inventory.view');
        $data = $inventory->load(['project', 'material']);

        if (!auth()->user()->can('financials.view')) {
            $data->makeHidden(['average_cost', 'total_value']);
        }

        return $this->successResponse('Inventory item retrieved successfully.', $data);
    }

    /**
     * Get inventory history/logs.
     */
    public function history(InventoryHistoryRequest $request)
    {
        $this->authorize('inventory.view');
        $validated = $request->validated();
        $query = InventoryLog::with(['inventory.material', 'inventory.project', 'user:id,name']);

        if (!empty($validated['project_id'])) {
            $query->whereHas('inventory', fn($q) => $q->where('project_id', $validated['project_id']));
        }

        if (!empty($validated['material_id'])) {
            $query->whereHas('inventory', fn($q) => $q->where('material_id', $validated['material_id']));
        }

        $logs = $query->latest()->paginate($validated['per_page'] ?? 15);

        // Mask costs in logs if they exist
        if (!auth()->user()->can('financials.view')) {
            $logs->getCollection()->transform(function ($log) {
                $log->makeHidden(['unit_cost']);
                return $log;
            });
        }

        return $this->paginatedResponse('Inventory history retrieved successfully.', $logs);
    }
}
