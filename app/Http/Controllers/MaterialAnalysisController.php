<?php

namespace App\Http\Controllers;

use App\Jobs\AnalyzeRabItemMaterials;
use App\Models\MaterialForecast;
use App\Models\Project;
use App\Models\RabItem;
use App\Services\AiMaterialAnalyzer;
use App\Services\MaterialMatcherService;
use Illuminate\Http\Request;

class MaterialAnalysisController extends Controller
{
    public function __construct(
        protected AiMaterialAnalyzer $analyzer,
        protected MaterialMatcherService $matcher
    ) {
    }

    /**
     * Show material analysis page for a project
     * Optimized: Tab-based loading - only loads data for active tab
     */
    public function index(Request $request, Project $project)
    {
        $this->authorize('analysis.view');

        $activeTab = $request->get('tab', 'summary');

        // ... existing logic ...
        // Get counts for tab badges (lightweight queries)
        $analyzedCount = $project->rabItems()->where('is_analyzed', true)->count();
        $unanalyzedCount = $project->rabItems()->notAnalyzed()->count();
        $summaryCount = \App\Models\MaterialForecast::query()
            ->join('rab_items', 'material_forecasts.rab_item_id', '=', 'rab_items.id')
            ->where('rab_items.project_id', $project->id)
            ->distinct('material_forecasts.raw_material_name')
            ->count('material_forecasts.raw_material_name');

        // Initialize variables
        $summary = null;
        $analyzedItems = null;
        $unanalyzedItems = null;
        $masterMaterials = collect();

        // Load data based on active tab
        switch ($activeTab) {
            case 'summary':
                $summary = $this->analyzer->getProjectAnalysisSummary($project->id);
                break;

            case 'analyzed':
                $analyzedItems = $project->rabItems()
                    ->where('is_analyzed', true)
                    ->select('id', 'project_id', 'work_name', 'volume', 'unit')
                    ->with([
                        'materialForecasts' => fn($q) => $q
                            ->select('id', 'rab_item_id', 'material_id', 'raw_material_name', 'estimated_qty', 'unit', 'match_score', 'coefficient', 'analysis_source')
                            ->with(['material:id,name'])
                    ])
                    ->paginate(5);

                // Master materials only needed for analyzed tab
                $masterMaterials = cache()->remember(
                    'master_materials_dropdown',
                    3600,
                    fn() => \App\Models\Material::active()->orderBy('name')->limit(500)->pluck('name', 'id')
                );
                break;

            case 'unanalyzed':
                $unanalyzedItems = $project->rabItems()
                    ->notAnalyzed()
                    ->select('id', 'project_id', 'rab_section_id', 'code', 'work_name', 'volume', 'unit')
                    ->paginate(5);
                break;
        }

        // Get configured AI providers
        $providers = AiMaterialAnalyzer::getConfiguredProviders();
        $defaultProvider = config('ai.default_provider', 'openai');

        // Check if AI is disabled
        $activeProvider = \App\Services\AiMaterialAnalyzer::getActiveProvider();
        $aiDisabled = $activeProvider === 'none';

        return view('projects.analysis.index', compact(
            'project',
            'activeTab',
            'summary',
            'summaryCount',
            'unanalyzedItems',
            'unanalyzedCount',
            'analyzedItems',
            'analyzedCount',
            'providers',
            'defaultProvider',
            'masterMaterials',
            'aiDisabled'
        ));
    }

    /**
     * Update manual mapping for a material forecast
     */
    public function updateMapping(Request $request, Project $project, \App\Models\MaterialForecast $forecast)
    {
        $this->authorize('analysis.manage');

        $validated = $request->validate([
            'material_id' => 'nullable|exists:materials,id',
        ]);

        $forecast->update([
            'material_id' => $validated['material_id'],
            'match_score' => 100, // Manual override = 100% confidence
            'analysis_source' => 'manual_override',
        ]);

        return back()->with('success', 'Mapping material berhasil diperbarui.');
    }

    /**
     * Analyze a single RAB item using AI
     */
    public function analyze(Request $request, Project $project, RabItem $item)
    {
        $this->authorize('analysis.run-ai');

        $provider = $request->input('provider', config('ai.default_provider', 'openai'));

        // Check if AI is globally disabled
        if (AiMaterialAnalyzer::getActiveProvider() === 'none') {
            return back()->with('error', 'Fitur AI sedang dinonaktifkan di pengaturan sistem.');
        }

        // Check if provider is configured
        if (!AiMaterialAnalyzer::isProviderConfigured($provider)) {
            return back()->with('error', "Provider {$provider} belum dikonfigurasi. Silakan set API key di Pengaturan Sistem.");
        }

        $result = $this->analyzer->useProvider($provider)->analyzeRabItem($item);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', 'Gagal menganalisis: ' . ($result['message'] ?? 'Unknown error'));
    }

    /**
     * Analyze a single RAB item using local matching (no AI)
     * Uses source-based routing: ahsp_components for source='ahsp', otherwise materials table
     */
    public function analyzeLocal(Request $request, Project $project, RabItem $item)
    {
        $this->authorize('analysis.manage');

        // Find materials using source-based local matching
        $materials = $this->matcher->analyzeRabItemLocal($item);

        if (empty($materials)) {
            return back()->with('warning', 'Tidak ditemukan material yang cocok untuk item ini. Silakan coba analisis dengan AI atau mapping manual.');
        }

        // Save forecasts
        $this->saveLocalForecasts($item, $materials);

        return back()->with('success', 'Analisis lokal berhasil. Ditemukan ' . count($materials) . ' material yang cocok.');
    }

    /**
     * Analyze all unanalyzed items using local matching
     * Optimized: Uses chunking to prevent memory exhaustion
     */
    public function analyzeAllLocal(Request $request, Project $project)
    {
        \Log::info("analyzeAllLocal hit for project {$project->id} with method " . $request->method());
        $this->authorize('analysis.manage');

        $unanalyzedCount = $project->rabItems()->notAnalyzed()->count();

        if ($unanalyzedCount === 0) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Semua item sudah dianalisis.'], 400);
            }
            return back()->with('info', 'Semua item sudah dianalisis.');
        }

        // If SSE/EventSource request (Accept: text/event-stream) or GET request, return streaming progress
        $acceptHeader = $request->header('Accept', '');
        if (str_contains($acceptHeader, 'text/event-stream') || $request->isMethod('get')) {
            \Log::info("Starting local material analysis stream for project {$project->id}");
            $unanalyzedItems = $project->rabItems()->notAnalyzed()->get();
            return $this->streamLocalAnalysis($project, $unanalyzedItems);
        }

        $successCount = 0;
        $noMatchCount = 0;

        // Process in chunks of 100 to prevent memory exhaustion
        $project->rabItems()->notAnalyzed()->chunk(100, function ($items) use (&$successCount, &$noMatchCount) {
            foreach ($items as $item) {
                // Consistency: Use analyzeRabItemLocal which handles AHSP-source items better
                $materials = $this->matcher->analyzeRabItemLocal($item);

                if (!empty($materials)) {
                    $this->saveLocalForecasts($item, $materials);
                    $successCount++;
                } else {
                    $noMatchCount++;
                }
            }

            // Clear memory between chunks
            gc_collect_cycles();
        });

        $message = "Analisis lokal selesai. {$successCount} item berhasil dianalisis.";
        if ($noMatchCount > 0) {
            $message .= " {$noMatchCount} item tidak ditemukan material yang cocok.";
        }

        return back()->with('success', $message);
    }

    /**
     * Stream local analysis progress
     */
    protected function streamLocalAnalysis(Project $project, $items)
    {
        // Increase limits for potentially long-running process
        set_time_limit(600); // 10 minutes
        ini_set('memory_limit', '1G');

        // Close session to prevent locking other requests
        if (session_id()) {
            session_write_close();
        }

        return response()->stream(function () use ($items) {
            // Clear all previous buffers to ensure immediate output
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            $total = $items->count();
            $processed = 0;
            $successCount = 0;
            $noMatchCount = 0;

            // Send initial start event
            echo "data: " . json_encode([
                'progress' => 0,
                'message' => 'Memulai analisis...',
                'current' => 0,
                'total' => $total,
                'success' => 0,
                'noMatch' => 0,
            ]) . "\n\n";
            
            if (ob_get_level() > 0) ob_end_flush();
            flush();

            \Log::info("Stream started with {$total} items");
            try {
                foreach ($items as $item) {
                    try {
                        // Use source-based local matching
                        $materials = $this->matcher->analyzeRabItemLocal($item);

                        if (!empty($materials)) {
                            $this->saveLocalForecasts($item, $materials);
                            $successCount++;
                        } else {
                            $noMatchCount++;
                        }
                    } catch (\Exception $e) {
                        \Log::error("Error analyzing RAB item {$item->id}: " . $e->getMessage());
                        $noMatchCount++;
                    }

                    $processed++;
                    $percent = round(($processed / $total) * 100);

                    echo "data: " . json_encode([
                        'progress' => $percent,
                        'current' => $processed,
                        'total' => $total,
                        'item' => $item->work_name,
                        'success' => $successCount,
                        'noMatch' => $noMatchCount,
                    ]) . "\n\n";

                    if (connection_aborted()) {
                        break;
                    }

                    flush();
                }

                echo "data: " . json_encode([
                    'progress' => 100,
                    'complete' => true,
                    'message' => "Analisis lokal selesai. {$successCount} item berhasil, {$noMatchCount} tidak ditemukan.",
                ]) . "\n\n";

            } catch (\Exception $e) {
                \Log::error("Fatal error in local analysis stream: " . $e->getMessage());
                echo "data: " . json_encode([
                    'error' => true,
                    'message' => 'Terjadi kesalahan fatal: ' . $e->getMessage(),
                ]) . "\n\n";
            }

            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Save material forecasts from local matching
     */
    protected function saveLocalForecasts(RabItem $item, array $materials): void
    {
        // Internal helper, should be safe
        // Delete existing forecasts for this item
        $item->materialForecasts()->delete();

        foreach ($materials as $mat) {
            MaterialForecast::create([
                'rab_item_id' => $item->id,
                'raw_material_name' => $mat['material_name'],
                'material_id' => $mat['match_score'] >= 75 ? $mat['material_id'] : null,
                'coefficient' => $mat['coefficient'],
                'estimated_qty' => $mat['estimated_qty'],
                'unit' => $mat['unit'],
                'match_score' => $mat['match_score'],
                'analysis_source' => $mat['analysis_source'] ?? $mat['source'] ?? 'local_matching',
                'notes' => $mat['notes'] ?? null,
            ]);
        }

        // Mark item as analyzed
        $item->update(['is_analyzed' => true]);
    }

    /**
     * Queue analysis for multiple items using AI
     */
    public function analyzeAll(Request $request, Project $project)
    {
        $this->authorize('analysis.run-ai');

        $provider = $request->input('provider', config('ai.default_provider', 'openai'));

        // Check if AI is globally disabled
        if (AiMaterialAnalyzer::getActiveProvider() === 'none') {
            return back()->with('error', 'Fitur AI sedang dinonaktifkan di pengaturan sistem.');
        }

        // Check if provider is configured
        if (!AiMaterialAnalyzer::isProviderConfigured($provider)) {
            return back()->with('error', "Provider {$provider} belum dikonfigurasi. Silakan set API key di Pengaturan Sistem.");
        }

        $unanalyzedItems = $project->rabItems()->notAnalyzed()->get();

        if ($unanalyzedItems->isEmpty()) {
            return back()->with('info', 'Semua item sudah dianalisis.');
        }

        foreach ($unanalyzedItems as $item) {
            AnalyzeRabItemMaterials::dispatch($item, $provider);
        }

        return back()->with('success', "Memproses analisis untuk {$unanalyzedItems->count()} item menggunakan " . ucfirst($provider) . ". Proses berjalan di background.");
    }

    /**
     * Show material forecast details for a RAB item
     */
    public function showItem(Project $project, RabItem $item)
    {
        $this->authorize('analysis.view');

        $item->load('materialForecasts.material');

        return view('projects.analysis.item', compact('project', 'item'));
    }

    /**
     * Re-analyze a RAB item using AI
     */
    public function reanalyze(Request $request, Project $project, RabItem $item)
    {
        $this->authorize('analysis.run-ai');

        $provider = $request->input('provider', config('ai.default_provider', 'openai'));

        // Check if AI is globally disabled
        if (AiMaterialAnalyzer::getActiveProvider() === 'none') {
            return back()->with('error', 'Fitur AI sedang dinonaktifkan di pengaturan sistem.');
        }

        // Check if provider is configured
        if (!AiMaterialAnalyzer::isProviderConfigured($provider)) {
            return back()->with('error', "Provider {$provider} belum dikonfigurasi. Silakan set API key di Pengaturan Sistem.");
        }

        // Delete existing forecasts
        $item->materialForecasts()->delete();
        $item->update(['is_analyzed' => false]);

        // Re-run analysis
        $result = $this->analyzer->useProvider($provider)->analyzeRabItem($item);

        if ($result['success']) {
            return back()->with('success', 'Re-analisis berhasil: ' . $result['message']);
        }

        return back()->with('error', 'Gagal re-analisis: ' . ($result['message'] ?? 'Unknown error'));
    }

    /**
     * Re-analyze a RAB item using local matching
     */
    public function reanalyzeLocal(Request $request, Project $project, RabItem $item)
    {
        $this->authorize('analysis.manage');

        // Delete existing forecasts
        $item->materialForecasts()->delete();
        $item->update(['is_analyzed' => false]);

        // Re-run local analysis
        return $this->analyzeLocal($request, $project, $item);
    }

    /**
     * Delete a single material forecast
     */
    public function deleteForecast(Request $request, Project $project, MaterialForecast $forecast)
    {
        $this->authorize('analysis.manage');

        $rabItem = $forecast->rabItem;
        $materialName = $forecast->raw_material_name;

        $forecast->delete();

        // If no more forecasts for this item, mark as not analyzed
        if ($rabItem && $rabItem->materialForecasts()->count() === 0) {
            $rabItem->update(['is_analyzed' => false]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Material '{$materialName}' berhasil dihapus.",
            ]);
        }

        return back()->with('success', "Material '{$materialName}' berhasil dihapus.");
    }


    /**
     * Delete multiple material forecasts
     */
    public function bulkDeleteForecasts(Request $request, Project $project)
    {
        $this->authorize('analysis.manage');

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:rab_items,id'
        ]);

        $ids = $validated['ids'];

        // Delete forecasts for these items
        MaterialForecast::whereIn('rab_item_id', $ids)->delete();

        // Mark items as not analyzed
        $project->rabItems()->whereIn('id', $ids)->update(['is_analyzed' => false]);

        return back()->with('success', 'Berhasil menghapus analisis untuk ' . count($ids) . ' item.');
    }

    /**
     * Delete specific material forecasts by their IDs
     */
    public function bulkDeleteMaterials(Request $request, Project $project)
    {
        $this->authorize('analysis.manage');

        $validated = $request->validate([
            'forecast_ids' => 'required|array',
            'forecast_ids.*' => 'exists:material_forecasts,id'
        ]);

        $forecastIds = $validated['forecast_ids'];

        // Get the affected RabItem IDs before deletion
        $affectedItemIds = MaterialForecast::whereIn('id', $forecastIds)
            ->pluck('rab_item_id')
            ->unique()
            ->toArray();

        // Delete the selected forecasts
        $deletedCount = MaterialForecast::whereIn('id', $forecastIds)->delete();

        // Check if any affected items now have no forecasts and reset their is_analyzed status
        foreach ($affectedItemIds as $itemId) {
            $remainingForecasts = MaterialForecast::where('rab_item_id', $itemId)->count();
            if ($remainingForecasts === 0) {
                RabItem::where('id', $itemId)->update(['is_analyzed' => false]);
            }
        }

        return back()->with('success', "Berhasil menghapus {$deletedCount} material.");
    }
}
