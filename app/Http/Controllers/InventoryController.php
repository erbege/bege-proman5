<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Material;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with(['material', 'project']);

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('search')) {
            $query->whereHas('material', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        $inventories = $query->paginate(20);
        $projects = Project::all();

        return view('inventory.index', compact('inventories', 'projects'));
    }

    public function history(Request $request)
    {
        $query = InventoryLog::with(['inventory.material', 'inventory.project', 'user'])->latest();

        if ($request->filled('project_id')) {
            $query->whereHas('inventory', function ($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $logs = $query->paginate(20);
        $projects = Project::all();

        return view('inventory.history', compact('logs', 'projects'));
    }

    // Manual adjustment endpoint (Stock Opname)
    public function adjust(Request $request, Inventory $inventory)
    {
        $request->validate([
            'quantity' => 'required|numeric',
            'type' => 'required|in:in,out,adjustment',
            'notes' => 'required|string',
        ]);

        DB::transaction(function () use ($request, $inventory) {
            $oldStock = $inventory->quantity;
            $qty = $request->quantity;

            if ($request->type === 'out') {
                $inventory->removeStock($qty, 'adjustment', null, $request->notes);
            } elseif ($request->type === 'in') {
                $inventory->addStock($qty, 'adjustment', null, $request->notes);
            } else {
                // Adjustment (Set absolute value)
                $delta = $qty - $oldStock;
                if ($delta > 0) {
                    $inventory->addStock($delta, 'adjustment', null, $request->notes);
                } elseif ($delta < 0) {
                    $inventory->removeStock(abs($delta), 'adjustment', null, $request->notes);
                }
            }
        });

        return back()->with('success', 'Stok berhasil diperbarui.');
    }
}
