<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Traits\GeneratesUniqueCode;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MaterialsImport;

class MaterialController extends Controller
{
    use GeneratesUniqueCode;

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new MaterialsImport, $request->file('file'));
            return redirect()->route('materials.index')->with('success', 'Data material berhasil diimpor.');
        } catch (\Exception $e) {
            return redirect()->route('materials.index')->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $materials = Material::orderBy('category')
            ->orderBy('name')
            ->paginate(20);

        $categories = Material::distinct()->pluck('category')->filter();
        $units = ['kg', 'm3', 'm2', 'm', 'bh', 'btg', 'zak', 'lbr', 'pail', 'set', 'unit', 'ls'];

        return view('materials.index', compact('materials', 'categories', 'units'));
    }

    public function create()
    {
        $categories = Material::distinct()->pluck('category')->filter();
        $units = ['kg', 'm3', 'm2', 'm', 'bh', 'btg', 'zak', 'lbr', 'pail', 'set', 'unit', 'ls'];

        return view('materials.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:materials,code',
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'unit' => 'required|string|max:20',
            'unit_price' => 'required|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        // Auto-generate code if empty
        if (empty($validated['code'])) {
            $validated['code'] = $this->generateUniqueCode(Material::class, 'MAT');
        }

        Material::create($validated);

        return redirect()
            ->route('materials.index')
            ->with('success', 'Material berhasil ditambahkan.');
    }

    public function edit(Material $material)
    {
        $categories = Material::distinct()->pluck('category')->filter();
        $units = ['kg', 'm3', 'm2', 'm', 'bh', 'btg', 'zak', 'lbr', 'pail', 'set', 'unit', 'ls'];

        return view('materials.edit', compact('material', 'categories', 'units'));
    }

    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'unit' => 'required|string|max:20',
            'unit_price' => 'required|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $material->update($validated);

        return redirect()
            ->route('materials.index')
            ->with('success', 'Material berhasil diperbarui.');
    }

    public function destroy(Material $material)
    {
        $material->delete();

        return redirect()
            ->route('materials.index')
            ->with('success', 'Material berhasil dihapus.');
    }
}
