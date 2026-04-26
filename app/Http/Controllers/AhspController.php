<?php

namespace App\Http\Controllers;

use App\Models\AhspCategory;
use App\Models\AhspComponent;
use App\Models\AhspWorkType;
use App\Services\AhspCalculatorService;
use Illuminate\Http\Request;

class AhspController extends Controller
{
    protected AhspCalculatorService $calculator;

    public function __construct(AhspCalculatorService $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Display AHSP list with categories tree
     */
    public function index(Request $request)
    {
        $categories = AhspCategory::getFlatListWithIndent(true);

        $workTypes = AhspWorkType::with('category')
            ->active()
            ->when($request->search, fn($q, $s) => $q->search($s))
            ->when($request->category_id, fn($q, $c) => $q->where('ahsp_category_id', $c))
            ->get()
            ->sortBy('code', SORT_NATURAL)
            ->values();

        // Manual Pagination
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 20;
        $items = $workTypes->slice(($page - 1) * $perPage, $perPage)->all();

        $workTypes = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $workTypes->count(),
            $perPage,
            $page,
            [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        $regions = $this->calculator->getAvailableRegions();

        return view('ahsp.index', compact('categories', 'workTypes', 'regions'));
    }

    /**
     * Show AHSP work type detail with components
     */
    public function show(Request $request, AhspWorkType $ahspWorkType)
    {
        $ahspWorkType->load(['category', 'components']);

        $regionCode = $request->get('region', array_key_first($this->calculator->getAvailableRegions()) ?? 'DEFAULT');
        $calculation = $this->calculator->calculateUnitPrice($ahspWorkType, $regionCode);
        $regions = $this->calculator->getAvailableRegions();

        return view('ahsp.show', compact('ahspWorkType', 'calculation', 'regions', 'regionCode'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $categories = AhspCategory::getFlatListWithIndent(true);
        return view('ahsp.create', compact('categories'));
    }

    /**
     * Store new AHSP work type
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ahsp_category_id' => 'required|exists:ahsp_categories,id',
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'description' => 'nullable|string',
            'source' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:255',
            'overhead_percentage' => 'required|numeric|min:0|max:100',
            'components' => 'nullable|array',
            'components.*.component_type' => 'required|in:labor,material,equipment',
            'components.*.code' => 'nullable|string|max:20',
            'components.*.name' => 'required|string|max:255',
            'components.*.unit' => 'required|string|max:20',
            'components.*.coefficient' => 'required|numeric|min:0',
        ]);

        $workType = AhspWorkType::create($validated);

        // Create components if provided
        if (!empty($validated['components'])) {
            foreach ($validated['components'] as $index => $componentData) {
                $componentData['sort_order'] = $index;
                $workType->components()->create($componentData);
            }
        }

        return redirect()
            ->route('ahsp.show', $workType)
            ->with('success', 'Jenis pekerjaan AHSP berhasil ditambahkan.');
    }

    /**
     * Show edit form
     */
    public function edit(AhspWorkType $ahspWorkType)
    {
        $ahspWorkType->load('components');
        $categories = AhspCategory::getFlatListWithIndent(true);
        return view('ahsp.edit', compact('ahspWorkType', 'categories'));
    }

    /**
     * Update AHSP work type
     */
    public function update(Request $request, AhspWorkType $ahspWorkType)
    {
        $validated = $request->validate([
            'ahsp_category_id' => 'required|exists:ahsp_categories,id',
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'description' => 'nullable|string',
            'source' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:255',
            'overhead_percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $ahspWorkType->update($validated);

        return redirect()
            ->route('ahsp.show', $ahspWorkType)
            ->with('success', 'Jenis pekerjaan AHSP berhasil diperbarui.');
    }

    /**
     * Delete AHSP work type
     */
    public function destroy(AhspWorkType $ahspWorkType)
    {
        $ahspWorkType->delete();

        return redirect()
            ->route('ahsp.index')
            ->with('success', 'Jenis pekerjaan AHSP berhasil dihapus.');
    }

    /**
     * Calculate price via AJAX
     */
    public function calculate(Request $request, AhspWorkType $ahspWorkType)
    {
        $regionCode = $request->get('region', 'DEFAULT');
        $volume = $request->get('volume', 1);

        $calculation = $this->calculator->calculateUnitPrice($ahspWorkType, $regionCode);
        $total = $calculation['unit_price'] * $volume;

        return response()->json([
            'success' => true,
            'work_type' => [
                'id' => $ahspWorkType->id,
                'code' => $ahspWorkType->code,
                'name' => $ahspWorkType->name,
                'unit' => $ahspWorkType->unit,
            ],
            'volume' => $volume,
            'region_code' => $regionCode,
            'calculation' => $calculation,
            'total' => $total,
            'formatted_total' => 'Rp ' . number_format($total, 0, ',', '.'),
        ]);
    }

    /**
     * Add component to work type
     */
    /**
     * Add component to work type
     */
    public function storeComponent(Request $request, AhspWorkType $ahspWorkType)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'component_type' => 'required|in:labor,material,equipment',
            'code' => 'nullable|string|max:20',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'coefficient' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            // New fields for price creation
            'create_new_price' => 'nullable',
            'price' => 'nullable|required_if:create_new_price,true|required_if:create_new_price,1|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'region_code' => 'nullable|string|max:20',
            'region_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        // Enforce linkage to existing base price if not creating new
        if (!$request->boolean('create_new_price')) {
            $exists = \App\Models\AhspBasePrice::where('component_type', $validated['component_type'])
                ->where('name', $validated['name'])
                ->exists();

            if (!$exists) {
                // Try case-insensitive search to be helpful
                $exists = \App\Models\AhspBasePrice::where('component_type', $validated['component_type'])
                    ->where('name', 'LIKE', $validated['name'])
                    ->exists();
            }

            if (!$exists) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Komponen "' . $validated['name'] . '" tidak ditemukan di database harga dasar. Silakan pilih dari suggestion atau biarkan opsi "Buat komponen baru" aktif.');
            }
        }

        // Auto-create base price if requested
        if ($request->boolean('create_new_price') && $request->filled('price') && $request->filled('region_code')) {
            $basePrice = \App\Models\AhspBasePrice::firstOrCreate(
                [
                    'name' => $validated['name'],
                    'region_code' => $validated['region_code'],
                    'component_type' => $validated['component_type'],
                ],
                [
                    'code' => $validated['code'],
                    'unit' => $validated['unit'],
                    'price' => $validated['price'],
                    'category' => $validated['category'] ?? 'Umum',
                    'region_name' => $request->get('region_name', $validated['region_code']),
                    'effective_date' => now(),
                    'is_active' => true,
                    'source' => 'Auto-created from AHSP Detail',
                ]
            );
        }

        $validated['sort_order'] = $ahspWorkType->components()->count();
        $ahspWorkType->components()->create($validated);

        return redirect()
            ->route('ahsp.show', $ahspWorkType)
            ->with('success', 'Komponen berhasil ditambahkan.');
    }

    /**
     * Update component
     */
    public function updateComponent(Request $request, AhspWorkType $ahspWorkType, AhspComponent $component)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'component_type' => 'required|in:labor,material,equipment',
            'code' => 'nullable|string|max:20',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'coefficient' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            // New fields for price creation
            'create_new_price' => 'nullable',
            'price' => 'nullable|required_if:create_new_price,true|required_if:create_new_price,1|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'region_code' => 'nullable|string|max:20',
            'region_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        // Enforce linkage to existing base price if not creating new
        if (!$request->boolean('create_new_price')) {
            $exists = \App\Models\AhspBasePrice::where('component_type', $validated['component_type'])
                ->where('name', $validated['name'])
                ->exists();

            if (!$exists) {
                // Try case-insensitive search to be helpful
                $exists = \App\Models\AhspBasePrice::where('component_type', $validated['component_type'])
                    ->where('name', 'LIKE', $validated['name'])
                    ->exists();
            }

            if (!$exists) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Komponen "' . $validated['name'] . '" tidak ditemukan di database harga dasar. Silakan pilih dari suggestion atau biarkan opsi "Buat komponen baru" aktif.');
            }
        }

        // Auto-create base price if requested (e.g. name changed to something new)
        if ($request->boolean('create_new_price') && $request->filled('price') && $request->filled('region_code')) {
            \App\Models\AhspBasePrice::firstOrCreate(
                [
                    'name' => $validated['name'],
                    'region_code' => $validated['region_code'],
                    'component_type' => $validated['component_type'],
                ],
                [
                    'code' => $validated['code'],
                    'unit' => $validated['unit'],
                    'price' => $validated['price'],
                    'category' => $validated['category'] ?? 'Umum',
                    'region_name' => $request->get('region_name', $validated['region_code']),
                    'effective_date' => now(),
                    'is_active' => true,
                    'source' => 'Auto-created from AHSP Detail (Edit)',
                ]
            );
        }

        $component->update($validated);

        return redirect()
            ->route('ahsp.show', $ahspWorkType)
            ->with('success', 'Komponen berhasil diperbarui.');
    }

    /**
     * Delete component
     */
    public function destroyComponent(AhspWorkType $ahspWorkType, AhspComponent $component)
    {
        $component->delete();

        return redirect()
            ->route('ahsp.show', $ahspWorkType)
            ->with('success', 'Komponen berhasil dihapus.');
    }

    /**
     * Search AHSP work types (for AJAX autocomplete)
     */
    public function search(Request $request)
    {
        $term = $request->get('q', '');

        $workTypes = AhspWorkType::with('category')
            ->active()
            ->search($term)
            ->limit(20)
            ->get()
            ->map(fn($wt) => [
                'id' => $wt->id,
                'code' => $wt->code,
                'name' => $wt->name,
                'unit' => $wt->unit,
                'category' => $wt->category?->name,
            ]);

        return response()->json($workTypes);
    }
}
