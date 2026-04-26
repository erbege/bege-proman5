<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    /**
     * List all materials.
     * 
     * Get a paginated list of all materials.
     */
    public function index(Request $request)
    {
        $query = Material::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        return $query->latest()->paginate($request->per_page ?? 15);
    }

    /**
     * Get material details.
     * 
     * Get detailed information about a specific material.
     */
    public function show(Material $material)
    {
        return response()->json([
            'data' => $material
        ]);
    }
}
