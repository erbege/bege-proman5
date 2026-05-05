<?php

namespace App\Livewire;

use App\Models\ProgressReport;
use App\Models\Project;
use App\Models\RabItem;
use App\Services\ProgressReportService;
use App\Services\WeatherService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ProgressReportManager extends Component
{
    use WithFileUploads, WithPagination;

    public Project $project;

    // Filter
    public string $search = '';

    public string $viewMode = 'list';

    // Modal
    public bool $showModal = false;
    public bool $quickEntryMode = true;

    public bool $showDeleteModal = false;

    public bool $showDetailModal = false;
    public bool $showReviewModal = false;

    public ?int $deleteId = null;

    public ?ProgressReport $selectedReport = null;
    public ?int $reviewTargetId = null;
    public string $reviewAction = 'approve';
    public string $reviewNotes = '';

    // Form
    public ?int $editingId = null;

    public string $rabItemId = '';

    public string $reportDate = '';

    public float $progressPercentage = 0;

    public string $description = '';

    public string $issues = '';

    public string $weather = '';

    public string $weatherDuration = '';

    public int $workerCount = 0;

    public $photos = [];

    public string $nextDayPlan = '';

    public array $equipmentDetails = [];
    public array $materialUsageSummary = [];
    public array $materialSearchResults = [];
    public ?int $activeMaterialRow = null;
    public array $materialSearchQueries = [];
    public array $safetyDetails = ['incidents' => 0, 'near_miss' => 0, 'apd_compliance' => true, 'notes' => ''];

    protected $weatherOptions = [
        'sunny' => 'Cerah',
        'cloudy' => 'Berawan',
        'rainy' => 'Hujan',
        'stormy' => 'Badai',
    ];

    public function mount(Project $project)
    {
        $this->authorize('progress.view');
        $this->project = $project;
        $this->reportDate = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function fetchWeather()
    {
        if (empty($this->reportDate)) {
            $this->addError('weather', 'Tanggal laporan harus diisi untuk auto-fetch cuaca.');

            return;
        }

        if (empty($this->project->location)) {
            $this->addError('weather', 'Lokasi proyek belum ditentukan. Silakan isi lokasi di pengaturan proyek.');

            return;
        }

        $weatherService = app(WeatherService::class);
        $weatherData = $weatherService->getHistoricalWeather($this->project, $this->reportDate);

        if ($weatherData && isset($weatherData['condition'])) {
            $this->weather = $weatherData['condition'];
            session()->flash('weather_success', 'Cuaca berhasil diambil untuk lokasi: ' . $this->project->location);
        } else {
            $this->addError('weather', 'Gagal mengambil data cuaca. Pastikan lokasi valid atau isi manual.');
        }
    }

    /**
     * Copy data from yesterday's report for efficient entry
     * Feature: "Copy from Yesterday" button reduces data entry time by ~30%
     */
    public function copyFromYesterday()
    {
        if (empty($this->rabItemId)) {
            $this->addError('rabItemId', 'Pilih item pekerjaan terlebih dahulu untuk copy laporan kemarin.');

            return;
        }

        $yesterday = now()->subDay()->format('Y-m-d');

        $previousReport = ProgressReport::where('rab_item_id', $this->rabItemId)
            ->where('report_date', $yesterday)
            ->whereNotIn('status', [ProgressReport::STATUS_REJECTED])
            ->latest()
            ->first();

        if (! $previousReport) {
            session()->flash('info', 'Tidak ada laporan untuk tanggal kemarin. Silakan isi manual.');

            return;
        }

        // Copy repetitive fields from yesterday's report
        $this->description = $previousReport->description;
        $this->nextDayPlan = $previousReport->next_day_plan;
        $this->weather = $previousReport->weather;
        $this->weatherDuration = $previousReport->weather_duration;
        $this->workerCount = $previousReport->workers_count ?? 0;
        $this->equipmentDetails = $previousReport->equipment_details ?? [];
        $this->materialUsageSummary = array_map(function($m) {
            return [
                'material_id' => $m['material_id'] ?? null,
                'material_name' => $m['material_name'] ?? ($m['material'] ?? ''),
                'qty_used' => $m['qty_used'] ?? 0,
                'unit' => $m['unit'] ?? '',
                'is_manual' => $m['is_manual'] ?? (!isset($m['material_id']))
            ];
        }, $previousReport->material_usage_summary ?? []);

        // Initialize safety details from yesterday
        if ($previousReport->safety_details) {
            $this->safetyDetails = array_merge($this->safetyDetails, $previousReport->safety_details);
        }

        session()->flash('success', '✅ Data laporan kemarin berhasil dicopy. Silakan update progress dan rencana kerja untuk hari ini.');
    }

    public function openModal($id = null)
    {
        $this->authorize('progress.create');
        $this->resetValidation();
        $this->reset(['editingId', 'rabItemId', 'reportDate', 'progressPercentage', 'description', 'issues', 'weather', 'weatherDuration', 'workerCount', 'photos', 'nextDayPlan', 'equipmentDetails', 'materialUsageSummary', 'materialSearchResults', 'activeMaterialRow', 'materialSearchQueries']);
        $this->safetyDetails = ['incidents' => 0, 'near_miss' => 0, 'apd_compliance' => true, 'notes' => ''];
        $this->reportDate = now()->format('Y-m-d');

        if ($id) {
            $report = ProgressReport::findOrFail($id);
            if (! $report->is_editable) {
                session()->flash('error', 'Laporan yang sudah diajukan tidak dapat diubah.');

                return;
            }
            $this->authorize('progress.update');
            $this->editingId = $report->id;
            $this->rabItemId = $report->rab_item_id ?? '';
            $this->reportDate = $report->report_date->format('Y-m-d');
            $this->progressPercentage = $report->progress_percentage;
            $this->description = $report->description ?? '';
            $this->issues = $report->issues ?? '';
            $this->weather = $report->weather ?? '';
            $this->weatherDuration = $report->weather_duration ?? '';
            $this->workerCount = $report->workers_count ?? 0;
            $this->nextDayPlan = $report->next_day_plan ?? '';
            $this->equipmentDetails = $report->equipment_details ?? [];
            $this->materialUsageSummary = array_map(function($m) {
                return [
                    'material_id' => $m['material_id'] ?? null,
                    'material_name' => $m['material_name'] ?? ($m['material'] ?? ''),
                    'qty_used' => $m['qty_used'] ?? 0,
                    'unit' => $m['unit'] ?? '',
                    'is_manual' => $m['is_manual'] ?? (!isset($m['material_id']))
                ];
            }, $report->material_usage_summary ?? []);
            if ($report->safety_details) {
                $this->safetyDetails = array_merge($this->safetyDetails, $report->safety_details);
            }
        }

        $this->showModal = true;
    }

    public function toggleQuickEntryMode(): void
    {
        $this->quickEntryMode = ! $this->quickEntryMode;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    #[Computed]
    public function selectedRabItem(): ?RabItem
    {
        if (empty($this->rabItemId)) {
            return null;
        }

        return $this->project->rabItems()->find($this->rabItemId);
    }

    #[Computed]
    public function projectedCumulative(): float
    {
        $selectedRab = $this->selectedRabItem;
        $baseProgress = $selectedRab ? (float) $selectedRab->actual_progress : 0;

        return min(100, $baseProgress + (float) $this->progressPercentage);
    }

    public function showDetail($id)
    {
        $this->authorize('progress.view');
        $this->selectedReport = ProgressReport::with(['rabItem', 'reporter', 'reviewer', 'rejector', 'publisher'])->find($id);

        if ($this->selectedReport) {
            $this->selectedReport->precomputed_photo_urls = $this->selectedReport->photo_urls;
        }

        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedReport = null;
    }

    public function openReviewModal(int $id, string $action): void
    {
        $this->authorize('progress.approve');
        $this->reviewTargetId = $id;
        $this->reviewAction = $action;
        $this->reviewNotes = '';
        $this->showReviewModal = true;
    }

    public function closeReviewModal(): void
    {
        $this->showReviewModal = false;
        $this->reviewTargetId = null;
        $this->reviewAction = 'approve';
        $this->reviewNotes = '';
    }

    public function processReview(): void
    {
        $this->authorize('progress.approve');
        $this->validate([
            'reviewAction' => 'required|in:approve,reject',
            'reviewNotes' => 'nullable|string|max:2000',
        ]);

        if (! $this->reviewTargetId) {
            return;
        }

        $report = ProgressReport::findOrFail($this->reviewTargetId);
        if ($this->reviewAction === 'approve') {
            app(ProgressReportService::class)->approve($report, auth()->id(), $this->reviewNotes ?: null);
            session()->flash('success', "Laporan {$report->report_code} berhasil diverifikasi.");
        } else {
            app(ProgressReportService::class)->reject($report, auth()->id(), $this->reviewNotes ?: null);
            session()->flash('success', "Laporan {$report->report_code} ditolak untuk revisi.");
        }

        if ($this->selectedReport && $this->selectedReport->id === $report->id) {
            $this->showDetail($report->id);
        }

        $this->closeReviewModal();
    }

    public function save()
    {
        if ($this->editingId) {
            $this->authorize('progress.update');
        } else {
            $this->authorize('progress.create');
        }
        $this->validate([
            'rabItemId' => 'nullable|exists:rab_items,id',
            'reportDate' => 'required|date',
            'progressPercentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'issues' => 'nullable|string',
            'weather' => 'nullable|in:sunny,cloudy,rainy,stormy',
            'weatherDuration' => 'nullable|string|max:100',
            'workerCount' => 'nullable|integer|min:0',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|max:5120',
            'nextDayPlan' => 'nullable|string',
            'equipmentDetails' => 'nullable|array',
            'equipmentDetails.*.name' => 'required_with:equipmentDetails|string',
            'equipmentDetails.*.qty' => 'required_with:equipmentDetails|integer|min:1',
            'equipmentDetails.*.condition' => 'nullable|string',
            'equipmentDetails.*.hours' => 'nullable|numeric|min:0',
            'materialUsageSummary' => 'nullable|array',
            'materialUsageSummary.*.material_id' => 'nullable|exists:materials,id',
            'materialUsageSummary.*.material_name' => 'required|string',
            'materialUsageSummary.*.qty_used' => 'required|numeric|min:0',
            'materialUsageSummary.*.unit' => 'nullable|string',
            'materialUsageSummary.*.is_manual' => 'nullable|boolean',
            'safetyDetails.incidents' => 'nullable|integer|min:0',
            'safetyDetails.near_miss' => 'nullable|integer|min:0',
            'safetyDetails.apd_compliance' => 'nullable|boolean',
            'safetyDetails.notes' => 'nullable|string',
        ]);

        $data = [
            'rab_item_id' => $this->rabItemId ?: null,
            'report_date' => $this->reportDate,
            'progress_percentage' => $this->progressPercentage,
            'description' => $this->description ?: null,
            'issues' => $this->issues ?: null,
            'weather' => $this->weather ?: null,
            'weather_duration' => $this->weatherDuration ?: null,
            'workers_count' => (int) ($this->workerCount ?: 0),
            'labor_details' => null,
            'next_day_plan' => $this->nextDayPlan ?: null,
            'equipment_details' => ! empty($this->equipmentDetails) ? array_values(array_filter($this->equipmentDetails, fn($e) => ! empty($e['name']))) : null,
            'material_usage_summary' => ! empty($this->materialUsageSummary) ? array_values(array_filter($this->materialUsageSummary, fn($m) => ! empty($m['material_name']))) : null,
            'safety_details' => $this->safetyDetails,
        ];

        $photoFiles = [];
        if (! empty($this->photos) && is_array($this->photos)) {
            $photoFiles = array_filter($this->photos, function ($photo) {
                return $photo instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
            });
        }

        /** @var ProgressReportService $service */
        $service = app(ProgressReportService::class);

        if ($this->editingId) {
            $report = ProgressReport::findOrFail($this->editingId);
            
            if (! $report->is_editable) {
                session()->flash('error', 'Laporan yang sudah diajukan tidak dapat diubah.');
                $this->closeModal();
                return;
            }

            $service->updateReport($report, $this->project, $data, $photoFiles);
            session()->flash('success', 'Laporan progress berhasil diperbarui.');
        } else {
            $service->create($this->project, $data, $photoFiles);
            session()->flash('success', 'Laporan progress berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id)
    {
        $this->authorize('progress.delete');
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function delete()
    {
        $this->authorize('progress.delete');
        $report = ProgressReport::find($this->deleteId);

        if (! $report) {
            session()->flash('error', 'Laporan tidak ditemukan.');
            $this->closeDeleteModal();

            return;
        }

        if (! $report->canDelete) {
            session()->flash('error', "Laporan dengan status '{$report->status_label}' tidak dapat dihapus.");
            $this->closeDeleteModal();

            return;
        }

        /** @var ProgressReportService $service */
        $service = app(ProgressReportService::class);
        $service->delete($report, $this->project);

        session()->flash('success', 'Laporan progress berhasil dihapus.');
        $this->closeDeleteModal();
    }

    // ========================
    // Workflow Actions
    // ========================

    public function submitReport(int $id): void
    {
        $this->authorize('progress.update');
        $report = ProgressReport::findOrFail($id);

        try {
            app(ProgressReportService::class)->submit($report);
            session()->flash('success', "Laporan {$report->report_code} berhasil diajukan.");
        } catch (\Exception $e) {
            session()->flash('error', "Gagal: {$e->getMessage()}");
        }
    }

    public function approveReport(int $id): void
    {
        $this->authorize('progress.approve');

        try {
            $report = ProgressReport::findOrFail($id);
            app(ProgressReportService::class)->approve($report, auth()->id());
            session()->flash('success', "Laporan {$report->report_code} berhasil diverifikasi.");
        } catch (\Exception $e) {
            session()->flash('error', "Gagal: {$e->getMessage()}");
        }
    }

    public function rejectReport(int $id, ?string $notes = null): void
    {
        $this->authorize('progress.approve');

        try {
            $report = ProgressReport::findOrFail($id);
            app(ProgressReportService::class)->reject($report, auth()->id(), $notes);
            session()->flash('success', "Laporan {$report->report_code} ditolak untuk revisi.");
        } catch (\Exception $e) {
            session()->flash('error', "Gagal: {$e->getMessage()}");
        }
    }

    public function publishReport(int $id): void
    {
        $this->authorize('progress.publish');

        try {
            $report = ProgressReport::findOrFail($id);
            app(ProgressReportService::class)->publish($report, auth()->id());
            session()->flash('success', "Laporan {$report->report_code} berhasil dipublikasikan.");
        } catch (\Exception $e) {
            session()->flash('error', "Gagal: {$e->getMessage()}");
        }
    }

    // ========================
    // Material Selection
    // ========================

    public function updatedMaterialSearchQueries($value, $key)
    {
        $this->activeMaterialRow = (int) $key;
        $query = $value;

        if (strlen($query) < 2) {
            $this->materialSearchResults = [];
            return;
        }

        $this->materialSearchResults = \App\Models\Inventory::where('project_id', $this->project->id)
            ->with('material')
            ->whereHas('material', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('code', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(function ($inv) {
                return [
                    'id' => $inv->material_id,
                    'name' => $inv->material->name,
                    'unit' => $inv->material->unit,
                    'stock' => (float) $inv->quantity,
                ];
            })
            ->toArray();
    }

    public function startMaterialSearch($index)
    {
        $this->activeMaterialRow = $index;
        $this->materialSearchQueries[$index] = $this->materialUsageSummary[$index]['material_name'] ?? '';
        $this->materialSearchResults = [];
    }

    public function selectMaterial($materialId, $name, $unit)
    {
        if ($this->activeMaterialRow !== null) {
            $this->materialUsageSummary[$this->activeMaterialRow]['material_id'] = $materialId;
            $this->materialUsageSummary[$this->activeMaterialRow]['material_name'] = $name;
            $this->materialUsageSummary[$this->activeMaterialRow]['unit'] = $unit;
            $this->materialUsageSummary[$this->activeMaterialRow]['is_manual'] = false;
            
            // Sync search query
            $this->materialSearchQueries[$this->activeMaterialRow] = $name;
        }
        $this->activeMaterialRow = null;
        $this->materialSearchResults = [];
    }

    public function setManualMaterial()
    {
        if ($this->activeMaterialRow !== null) {
            $this->materialUsageSummary[$this->activeMaterialRow]['material_id'] = null;
            $this->materialUsageSummary[$this->activeMaterialRow]['is_manual'] = true;
            $this->materialUsageSummary[$this->activeMaterialRow]['material_name'] = $this->materialSearchQueries[$this->activeMaterialRow] ?? '';
        }
        $this->activeMaterialRow = null;
        $this->materialSearchResults = [];
    }

    public function addMaterialRow()
    {
        $this->materialUsageSummary[] = [
            'material_id' => null,
            'material_name' => '',
            'qty_used' => 0,
            'unit' => '',
            'is_manual' => false
        ];
    }

    public function render()
    {
        $query = $this->project->progressReports()->with(['rabItem', 'reporter', 'reviewer'])->orderByDesc('report_date');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('rabItem', function ($q2) {
                        $q2->where('work_name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        $reports = $query->paginate(20);
        $rabItems = $this->project->rabItems()->with('section')->orderBy('sort_order')->get();

        return view('livewire.progress-report-manager', [
            'reports' => $reports,
            'rabItems' => $rabItems,
            'weatherOptions' => $this->weatherOptions,
        ]);
    }
}
