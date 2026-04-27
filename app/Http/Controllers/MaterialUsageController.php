<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialUsage;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MaterialUsageController extends Controller
{
    public function index(Project $project)
    {
        $usages = $project->materialUsages()
            ->with(['items.material', 'createdBy'])
            ->latest('usage_date')
            ->paginate(20);

        return view('projects.usage.index', compact('project', 'usages'));
    }

    public function create(Project $project)
    {
        // Get materials that have stock in this project
        $inventories = Inventory::where('project_id', $project->id)
            ->where('quantity', '>', 0)
            ->with('material')
            ->get();

        // Prepare stock list data for Alpine.js
        $stockListData = $inventories->map(function ($i) {
            return [
                'id' => $i->material_id,
                'name' => $i->material->name,
                'code' => $i->material->code,
                'quantity' => (float) $i->quantity,
                'unit' => $i->material->unit
            ];
        });

        // Get RAB Items for work reference
        $rabItems = $project->rabItems()
            ->orderBy('work_name')
            ->get();

        return view('projects.usage.create', compact('project', 'inventories', 'rabItems', 'stockListData'));
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'usage_date' => 'required|date',
            'rab_item_id' => 'nullable|exists:rab_items,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $project) {
                // 1. Create Usage Header
                $usage = MaterialUsage::create([
                    'project_id' => $project->id,
                    'rab_item_id' => $validated['rab_item_id'] ?? null,
                    'usage_date' => $validated['usage_date'],
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['items'] as $itemData) {
                    $materialId = $itemData['material_id'];
                    $qty = $itemData['quantity'];

                    // 2. Check and Deduct Stock
                    $inventory = Inventory::where('project_id', $project->id)
                        ->where('material_id', $materialId)
                        ->first();

                    if (!$inventory) {
                        throw ValidationException::withMessages([
                            'items' => "Material ID $materialId tidak ada di stok proyek ini."
                        ]);
                    }

                    if ($inventory->quantity < $qty) {
                        throw ValidationException::withMessages([
                            'items' => "Stok untuk material {$inventory->material->name} tidak mencukupi. Tersedia: {$inventory->quantity}"
                        ]);
                    }

                    $inventory->removeStock($qty, 'usage', $usage->id, $itemData['notes'] ?? null);

                    $unitCost = $inventory->average_cost;
                    $totalCost = $qty * $unitCost;

                    // 3. Create Item
                    $usage->items()->create([
                        'material_id' => $materialId,
                        'quantity' => $qty,
                        'unit_cost' => $unitCost,
                        'total_cost' => $totalCost,
                        'notes' => $itemData['notes'] ?? null,
                    ]);
                }
            });

            return redirect()->route('projects.usage.index', $project)
                ->with('success', 'Penggunaan material berhasil dicatat.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Project $project, MaterialUsage $usage)
    {
        $usage->load(['items.material', 'createdBy', 'rabItem']);
        return view('projects.usage.show', compact('project', 'usage'));
    }
}
