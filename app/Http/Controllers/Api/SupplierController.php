<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * List all suppliers.
     * 
     * Get a paginated list of all active suppliers.
     */
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($request->per_page ?? 15);
    }

    /**
     * Get supplier details.
     * 
     * Get detailed information about a specific supplier.
     */
    public function show(Supplier $supplier)
    {
        return response()->json([
            'data' => $supplier
        ]);
    }
}
