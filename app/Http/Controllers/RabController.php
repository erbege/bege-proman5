<?php

namespace App\Http\Controllers;

use App\Imports\RabImport;
use App\Models\AhspBasePrice;
use App\Models\AhspCategory;
use App\Models\AhspWorkType;
use App\Models\Project;
use App\Models\RabItem;
use App\Models\RabSection;
use App\Services\RabGeneratorService;
use App\Services\ScheduleCalculator;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RabController extends Controller
{
    public function index(Project $project)
    {
        $project->load(['rabSections.items']);

        $totalValue = $project->rabItems()->sum('total_price');

        return view('projects.rab.index', compact('project', 'totalValue'));
    }

    public function import(Project $project)
    {
        return view('projects.rab.import', compact('project'));
    }

    public function processImport(Request $request, Project $project)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            'clear_existing' => 'nullable|boolean',
        ]);

        // Clear existing RAB if requested
        if ($request->boolean('clear_existing')) {
            $project->rabItems()->delete();
            $project->rabSections()->delete();
        }

        // Import the file
        $import = new RabImport($project);
        Excel::import($import, $request->file('file'));

        // Generate schedule if project has dates
        if ($project->start_date && $project->end_date) {
            $scheduleCalculator = new ScheduleCalculator();
            $scheduleCalculator->generateSchedule($project);
        }

        return redirect()
            ->route('projects.rab.index', $project)
            ->with('success', "Import berhasil! {$import->getImportedCount()} item pekerjaan ditambahkan.");
    }

    public function createSection(Project $project)
    {
        return view('projects.rab.create-section', compact('project'));
    }

    public function storeSection(Request $request, Project $project)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
        ]);

        $validated['project_id'] = $project->id;
        $validated['sort_order'] = $project->rabSections()->count();

        RabSection::create($validated);

        return redirect()
            ->route('projects.rab.index', $project)
            ->with('success', 'Bagian pekerjaan berhasil ditambahkan.');
    }

    public function createItem(Project $project, RabSection $section)
    {
        return view('projects.rab.create-item', compact('project', 'section'));
    }

    public function storeItem(Request $request, Project $project, RabSection $section)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:20',
            'work_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'volume' => 'required|numeric|min:0',
            'unit' => 'required|string|max:20',
            'unit_price' => 'required|numeric|min:0',
            'planned_start' => 'nullable|date',
            'planned_end' => 'nullable|date|after_or_equal:planned_start',
        ]);

        $validated['project_id'] = $project->id;
        $validated['rab_section_id'] = $section->id;
        $validated['total_price'] = $validated['volume'] * $validated['unit_price'];
        $validated['sort_order'] = $section->items()->count();

        RabItem::create($validated);

        // Recalculate weights
        $project->calculateTotalWeight();

        return redirect()
            ->route('projects.rab.index', $project)
            ->with('success', 'Item pekerjaan berhasil ditambahkan.');
    }

    public function editItem(Project $project, RabItem $item)
    {
        $sections = $project->rabSections;
        return view('projects.rab.edit-item', compact('project', 'item', 'sections'));
    }

    public function updateItem(Request $request, Project $project, RabItem $item)
    {
        $validated = $request->validate([
            'rab_section_id' => 'required|exists:rab_sections,id',
            'code' => 'nullable|string|max:20',
            'work_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'volume' => 'required|numeric|min:0',
            'unit' => 'required|string|max:20',
            'unit_price' => 'required|numeric|min:0',
            'planned_start' => 'nullable|date',
            'planned_end' => 'nullable|date|after_or_equal:planned_start',
        ]);

        $validated['total_price'] = $validated['volume'] * $validated['unit_price'];

        $item->update($validated);

        // Recalculate weights
        $project->calculateTotalWeight();

        return redirect()
            ->route('projects.rab.index', $project)
            ->with('success', 'Item pekerjaan berhasil diperbarui.');
    }

    public function destroyItem(Project $project, RabItem $item)
    {
        $item->delete();

        // Recalculate weights
        $project->calculateTotalWeight();

        return redirect()
            ->route('projects.rab.index', $project)
            ->with('success', 'Item pekerjaan berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        $templatePath = storage_path('app/templates/rab_template.xlsx');

        // If template doesn't exist, generate a simple one
        if (!file_exists($templatePath)) {
            return redirect()->back()->with('error', 'Template tidak tersedia.');
        }

        return response()->download($templatePath, 'template_rab.xlsx');
    }

    /**
     * Show AHSP work type selector for generating RAB items
     */
    public function showAhspSelector(Project $project, RabSection $section)
    {
        $regions = AhspBasePrice::getRegions();
        $defaultRegion = array_key_first($regions) ?? 'DEFAULT';

        // Find matching category by section code
        $matchingCategory = null;
        $categoriesWithWorkTypes = collect();

        if ($section->code) {
            $sectionCode = trim($section->code);
            $matchingCategory = AhspCategory::where('code', $sectionCode)->first();

            if ($matchingCategory) {
                // Get all descendant category IDs (including self)
                $categoryIds = $this->getAllDescendantCategoryIds($matchingCategory);

                // Load all matching categories with their work types
                $categoriesWithWorkTypes = AhspCategory::whereIn('id', $categoryIds)
                    ->with([
                        'workTypes' => function ($query) {
                            $query->active();
                        }
                    ])
                    ->get()
                    ->sortBy('code', SORT_NATURAL);

                // Sort work types within each category using SORT_NATURAL
                $categoriesWithWorkTypes = $categoriesWithWorkTypes->map(function ($category) {
                    $category->setRelation('workTypes', $category->workTypes->sortBy('code', SORT_NATURAL)->values());
                    return $category;
                });
            }
        }

        // Fallback: if no categories found, show all
        if ($categoriesWithWorkTypes->isEmpty()) {
            $categoriesWithWorkTypes = AhspCategory::with([
                'workTypes' => function ($query) {
                    $query->active();
                }
            ])
                ->whereNull('parent_id')
                ->get()
                ->sortBy('code', SORT_NATURAL);

            // Sort work types within each category using SORT_NATURAL
            $categoriesWithWorkTypes = $categoriesWithWorkTypes->map(function ($category) {
                $category->setRelation('workTypes', $category->workTypes->sortBy('code', SORT_NATURAL)->values());
                return $category;
            });
        }

        return view('projects.rab.ahsp-selector', compact(
            'project',
            'section',
            'categoriesWithWorkTypes',
            'matchingCategory',
            'regions',
            'defaultRegion'
        ));
    }

    /**
     * Get all descendant category IDs including the given category
     */
    protected function getAllDescendantCategoryIds(AhspCategory $category): array
    {
        $ids = [$category->id];

        $children = AhspCategory::where('parent_id', $category->id)->get();
        foreach ($children as $child) {
            $ids = array_merge($ids, $this->getAllDescendantCategoryIds($child));
        }

        return $ids;
    }

    /**
     * Search AHSP work types (AJAX)
     */
    public function searchAhsp(Request $request, Project $project)
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

    /**
     * Generate RAB items from selected AHSP work types
     */
    public function generateFromAhsp(Request $request, Project $project, RabSection $section, RabGeneratorService $generator)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.work_type_id' => 'required|exists:ahsp_work_types,id',
            'items.*.volume' => 'required|numeric|min:0.0001',
            'region_code' => 'required|string|max:20',
        ]);

        $generated = $generator->batchGenerate(
            $project,
            $section,
            $validated['items'],
            $validated['region_code']
        );

        // Regenerate schedule if project has dates
        if ($project->start_date && $project->end_date) {
            $scheduleCalculator = new ScheduleCalculator();
            $scheduleCalculator->generateSchedule($project);
        }

        return redirect()
            ->route('projects.rab.index', $project)
            ->with('success', "Berhasil menambahkan {$generated->count()} item dari AHSP.");
    }

    /**
     * Preview AHSP calculation (AJAX)
     */
    public function previewAhspCalculation(Request $request, Project $project)
    {
        $request->validate([
            'work_type_id' => 'required|exists:ahsp_work_types,id',
            'volume' => 'required|numeric|min:0',
            'region_code' => 'required|string',
        ]);

        $workType = AhspWorkType::with('components')->find($request->work_type_id);
        $calculation = $workType->calculateUnitPrice($request->region_code);

        return response()->json([
            'success' => true,
            'work_type' => [
                'id' => $workType->id,
                'code' => $workType->code,
                'name' => $workType->name,
                'unit' => $workType->unit,
            ],
            'volume' => $request->volume,
            'calculation' => $calculation,
            'total_price' => $calculation['unit_price'] * $request->volume,
            'formatted_total' => 'Rp ' . number_format($calculation['unit_price'] * $request->volume, 0, ',', '.'),
        ]);
    }

    /**
     * Show RAB Template Generator from AHSP Categories
     */
    public function showTemplateGenerator(Project $project, \App\Services\RabTemplateService $templateService)
    {
        $categories = $templateService->getCategoriesWithCounts();
        $categoriesTree = $templateService->getCategoriesTree();
        $regions = AhspBasePrice::getRegions();
        $defaultRegion = array_key_first($regions) ?? 'DEFAULT';

        return view('projects.rab.template-generator', compact(
            'project',
            'categories',
            'categoriesTree',
            'regions',
            'defaultRegion'
        ));
    }

    /**
     * Preview template structure (AJAX)
     */
    public function previewTemplate(Request $request, Project $project, \App\Services\RabTemplateService $templateService)
    {
        $request->validate([
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:ahsp_categories,id',
        ]);

        $preview = $templateService->previewStructure($request->category_ids);

        return response()->json([
            'success' => true,
            'preview' => $preview,
        ]);
    }

    /**
     * Generate RAB from template
     */
    public function generateFromTemplate(Request $request, Project $project, \App\Services\RabTemplateService $templateService)
    {
        $validated = $request->validate([
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:ahsp_categories,id',
            'region_code' => 'required|string|max:20',
            'clear_existing' => 'nullable|boolean',
        ]);

        $result = $templateService->generateFromCategories(
            $project,
            $validated['category_ids'],
            $validated['region_code'],
            $request->boolean('clear_existing')
        );

        // Regenerate schedule if project has dates
        if ($project->start_date && $project->end_date) {
            $scheduleCalculator = new ScheduleCalculator();
            $scheduleCalculator->generateSchedule($project);
        }

        return redirect()
            ->route('projects.rab.index', $project)
            ->with('success', "Berhasil generate RAB: {$result['sections_created']} bagian, {$result['items_created']} item pekerjaan dari AHSP.");
    }
    /**
     * Export RAB to Excel
     */
    public function exportExcel(Project $project)
    {
        return Excel::download(new \App\Exports\RabExport($project), 'RAB-' . $project->code . '.xlsx');
    }

    /**
     * Export RAB to PDF
     */
    public function exportPdf(Project $project)
    {
        // Load root sections with recursive children and items
        $sections = $project->rabSections()
            ->whereNull('parent_id')
            ->with(['recursiveChildren', 'items'])
            ->get()
            ->sortBy('code', SORT_NATURAL);

        // Calculate grand total
        $grandTotal = $project->rabItems()->sum('total_price');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('projects.rab.export', compact('project', 'sections', 'grandTotal'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('RAB-' . $project->code . '.pdf');
    }
}
