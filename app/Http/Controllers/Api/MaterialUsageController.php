<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\MaterialUsage;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MaterialUsageController extends Controller
{
    /**
     * List material usages for a project.
     *
     * @param Request $request
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Project $project)
    {
        $usages = $project->materialUsages()
            ->with(['items.material', 'createdBy'])
            ->latest('usage_date')
            ->paginate($request->per_page ?? 20);

        return response()->json($usages);
    }

    /**
     * Create a new material usage.
     *
     * @param Request $request
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     */
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
            $usage = DB::transaction(function () use ($validated, $project) {
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

                    // 3. Create Item
                    $usage->items()->create([
                        'material_id' => $materialId,
                        'quantity' => $qty,
                        'notes' => $itemData['notes'] ?? null,
                    ]);
                }

                return $usage;
            });

            return response()->json([
                'message' => 'Material usage created successfully',
                'data' => $usage->load(['items.material', 'createdBy'])
            ], 201);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show material usage details.
     *
     * @param Project $project
     * @param MaterialUsage $materialUsage
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Project $project, MaterialUsage $materialUsage)
    {
        // Ensure usage belongs to project
        if ($materialUsage->project_id != $project->id) {
            return response()->json(['error' => 'Material Usage not found in this project'], 404);
        }

        $materialUsage->load(['items.material', 'createdBy', 'rabItem']);

        return response()->json($materialUsage);
    }
}
