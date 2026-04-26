<?php

namespace App\Http\Controllers;

use App\Imports\AhspPriceImport;
use App\Models\AhspBasePrice;
use App\Models\AhspPriceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\Material;
use Illuminate\Support\Facades\DB;

class AhspPriceController extends Controller
{
    /**
     * Sync AHSP Materials to Master Materials
     */
    public function syncMaterials()
    {
        // Increase time limit for large datasets
        set_time_limit(300);

        try {
            DB::beginTransaction();

            $ahspMaterials = AhspBasePrice::where('component_type', 'material')
                ->where('is_active', true)
                ->get();

            $synced = 0;
            $created = 0;

            foreach ($ahspMaterials as $ahsp) {
                $material = null;

                // 1. Try to find matching material
                if (!empty($ahsp->code)) {
                    // Match by Code AND Region
                    $material = Material::where('code', $ahsp->code)
                        ->where('region_code', $ahsp->region_code)
                        ->first();
                }

                // 2. Fallback: Match by Name AND Region (if code mismatch or empty code)
                if (!$material && !empty($ahsp->name)) {
                    $material = Material::where('name', $ahsp->name)
                        ->where('region_code', $ahsp->region_code)
                        ->first();
                }

                if ($material) {
                    $updateData = [
                        'name' => $ahsp->name,
                        'unit' => $ahsp->unit,
                        'unit_price' => $ahsp->price,
                        'effective_date' => $ahsp->effective_date,
                        'is_active' => true,
                    ];

                    // Update category only if AHSP has one
                    if (!empty($ahsp->category)) {
                        $updateData['category'] = $ahsp->category;
                    }

                    // Update existing
                    $material->update($updateData);
                    $synced++;
                } else {
                    // Generate Code if missing
                    $code = $ahsp->code;
                    if (empty($code)) {
                        // Generate a unique code based on AHSP ID to ensure uniqueness
                        $code = 'AHSP-M-' . str_pad($ahsp->id, 5, '0', STR_PAD_LEFT);
                    }

                    // Create new
                    $material = Material::create([
                        'code' => $code,
                        'name' => $ahsp->name,
                        'category' => $ahsp->category ?? 'AHSP', // Use AHSP category or default
                        'unit' => $ahsp->unit,
                        'unit_price' => $ahsp->price,
                        'region_code' => $ahsp->region_code,
                        'region_name' => $ahsp->region_name,
                        'effective_date' => $ahsp->effective_date,
                        'source' => 'AHSP Sync',
                        'is_active' => true,
                    ]);
                    $created++;
                }

                // Link back to AHSP
                if (!$ahsp->material_id) {
                    $ahsp->update(['material_id' => $material->id]);
                }
            }

            DB::commit();

            return redirect()
                ->route('ahsp.prices.index')
                ->with('success', "Sinkronisasi selesai. $created material baru ditambahkan, $synced material diperbarui.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('ahsp.prices.index')
                ->with('error', "Gagal melakukan sinkronisasi: " . $e->getMessage());
        }
    }

    /**
     * Display base prices list
     */
    public function index(Request $request)
    {
        $prices = AhspBasePrice::query()
            ->when($request->search, fn($q, $s) => $q->search($s))
            ->when($request->type, fn($q, $t) => $q->where('component_type', $t))
            ->when($request->region, fn($q, $r) => $q->where('region_code', $r))
            ->active()
            ->orderBy('component_type')
            ->orderBy('name')
            ->paginate(50);

        $regions = AhspBasePrice::getRegions();
        $types = [
            'labor' => 'Tenaga Kerja',
            'material' => 'Bahan',
            'equipment' => 'Peralatan',
        ];

        return view('ahsp.prices.index', compact('prices', 'regions', 'types'));
    }

    /**
     * Show import form
     */
    public function import()
    {
        $regions = AhspBasePrice::getRegions();
        return view('ahsp.prices.import', compact('regions'));
    }

    /**
     * Process import from Excel
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            'region_code' => 'required|string|max:20',
            'region_name' => 'nullable|string|max:255',
            'effective_date' => 'required|date',
        ]);

        $import = new AhspPriceImport(
            $request->region_code,
            $request->region_name ?? $request->region_code,
            $request->effective_date
        );

        Excel::import($import, $request->file('file'));

        return redirect()
            ->route('ahsp.prices.index')
            ->with('success', "Import berhasil! {$import->getImportedCount()} harga satuan dasar ditambahkan.");
    }

    /**
     * Show price detail with history
     */
    public function show(AhspBasePrice $price)
    {
        $price->load('priceHistories.changedByUser');
        return view('ahsp.prices.show', compact('price'));
    }

    /**
     * Update price (with history tracking)
     */
    public function update(Request $request, AhspBasePrice $price)
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $oldPrice = $price->price;
        $newPrice = $validated['price'];

        // Only create history if price changed
        if ($oldPrice != $newPrice) {
            AhspPriceHistory::create([
                'ahsp_base_price_id' => $price->id,
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'changed_by' => Auth::id(),
                'reason' => $validated['reason'] ?? null,
            ]);
        }

        $price->update([
            'price' => $newPrice,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Harga berhasil diperbarui.');
    }

    /**
     * Bulk update prices
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'prices' => 'required|array',
            'prices.*.id' => 'required|exists:ahsp_base_prices,id',
            'prices.*.price' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $updated = 0;
        foreach ($validated['prices'] as $priceData) {
            $price = AhspBasePrice::find($priceData['id']);
            if ($price && $price->price != $priceData['price']) {
                AhspPriceHistory::create([
                    'ahsp_base_price_id' => $price->id,
                    'old_price' => $price->price,
                    'new_price' => $priceData['price'],
                    'changed_by' => Auth::id(),
                    'reason' => $validated['reason'] ?? 'Bulk update',
                ]);

                $price->update(['price' => $priceData['price']]);
                $updated++;
            }
        }

        return response()->json([
            'success' => true,
            'updated' => $updated,
        ]);
    }

    /**
     * Get list of regions
     */
    public function regions()
    {
        $regions = AhspBasePrice::getRegions();
        return response()->json($regions);
    }

    /**
     * Search base prices (for AJAX autocomplete)
     */
    public function search(Request $request)
    {
        $term = $request->get('q', '');
        $type = $request->get('type');
        $region = $request->get('region');

        $prices = AhspBasePrice::query()
            ->search($term)
            ->when($type, function ($q) use ($type) {
                // Handle case inconsistencies in DB (e.g. 'material' vs 'Material')
                $q->where(function ($qq) use ($type) {
                    $qq->where('component_type', $type)
                        ->orWhere('component_type', ucfirst($type))
                        ->orWhere('component_type', strtoupper($type));
                });
            })
            ->when($region, fn($q) => $q->where('region_code', $region))
            ->active()
            ->orderByDesc('effective_date')
            ->limit(20)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
                'unit' => $p->unit,
                'price' => (float) $p->price,
                'category' => $p->category,
            ]);

        return response()->json($prices);
    }

    /**
     * Create new base price
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'component_type' => 'required|in:labor,material,equipment',
            'unit' => 'required|string|max:20',
            'region_code' => 'required|string|max:20',
            'region_name' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'effective_date' => 'required|date',
            'source' => 'nullable|string|max:255',
        ]);

        AhspBasePrice::create($validated);

        return redirect()
            ->route('ahsp.prices.index')
            ->with('success', 'Harga satuan dasar berhasil ditambahkan.');
    }

    /**
     * Delete base price
     */
    public function destroy(AhspBasePrice $price)
    {
        $price->delete();

        return redirect()
            ->route('ahsp.prices.index')
            ->with('success', 'Harga satuan dasar berhasil dihapus.');
    }
}
