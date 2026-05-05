<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Project;
use Illuminate\Http\Request;

class MaterialInventoryController extends Controller
{
    public function search(Request $request, Project $project)
    {
        $search = $request->input('q');

        $query = Inventory::where('project_id', $project->id)
            ->with('material')
            ->whereHas('material', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });

        $results = $query->limit(10)->get()->map(function ($inventory) {
            return [
                'id' => $inventory->material_id,
                'inventory_id' => $inventory->id,
                'name' => $inventory->material->name,
                'code' => $inventory->material->code,
                'stock' => (float) $inventory->quantity,
                'unit' => $inventory->material->unit,
            ];
        });

        return response()->json($results);
    }
}
