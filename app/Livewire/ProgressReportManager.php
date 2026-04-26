<?php

namespace App\Livewire;

use App\Models\ProgressReport;
use App\Models\Project;
use App\Models\RabItem;
use App\Services\ScheduleCalculator;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class ProgressReportManager extends Component
{
    use WithPagination, WithFileUploads;

    public Project $project;

    // Filter
    public string $search = '';
    public string $viewMode = 'list';

    // Modal
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showDetailModal = false;
    public ?int $deleteId = null;
    public ?ProgressReport $selectedReport = null;

    // Form
    public string $rabItemId = '';
    public string $reportDate = '';
    public float $progressPercentage = 0;
    public string $description = '';
    public string $issues = '';
    public string $weather = '';
    public int $workerCount = 0;
    public $photos = [];

    protected $weatherOptions = [
        'sunny' => 'Cerah',
        'cloudy' => 'Berawan',
        'rainy' => 'Hujan',
        'stormy' => 'Badai',
    ];

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->reportDate = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->reset(['rabItemId', 'reportDate', 'progressPercentage', 'description', 'issues', 'weather', 'workerCount', 'photos']);
        $this->reportDate = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function showDetail($id)
    {
        $this->selectedReport = ProgressReport::with(['rabItem', 'reporter'])->find($id);

        // Precompute photo URLs to avoid repeated accessor calls
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

    public function save()
    {
        $this->validate([
            'rabItemId' => 'nullable|exists:rab_items,id',
            'reportDate' => 'required|date',
            'progressPercentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'issues' => 'nullable|string',
            'weather' => 'nullable|in:sunny,cloudy,rainy,stormy',
            'workerCount' => 'nullable|integer|min:0',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|max:5120',
        ]);

        $data = [
            'project_id' => $this->project->id,
            'rab_item_id' => $this->rabItemId ?: null,
            'report_date' => $this->reportDate,
            'progress_percentage' => $this->progressPercentage,
            'description' => $this->description ?: null,
            'issues' => $this->issues ?: null,
            'weather' => $this->weather ?: null,
            'workers_count' => $this->workerCount ?: null,
            'reported_by' => auth()->id(),
        ];

        // Handle photo uploads with resize and WebP conversion
        if (!empty($this->photos) && is_array($this->photos)) {
            $disk = \App\Models\SystemSetting::getStorageDisk();
            $imageResizer = new \App\Services\ImageResizeService();

            // Filter valid TemporaryUploadedFile instances
            $validPhotos = array_filter($this->photos, function ($photo) {
                return $photo instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
            });

            // Process and resize images to WebP format
            $photoPaths = $imageResizer->processMultiple(
                $validPhotos,
                "progress/{$this->project->id}",
                $disk
            );

            if (!empty($photoPaths)) {
                $data['photos'] = $photoPaths;
            }
        }

        // Calculate cumulative progress
        if ($this->rabItemId) {
            $rabItem = RabItem::find($this->rabItemId);
            $data['cumulative_progress'] = min(100, $rabItem->actual_progress + $this->progressPercentage);
        }

        $report = ProgressReport::create($data);

        // Update RAB item progress
        if ($report->rab_item_id) {
            $report->rabItem->update([
                'actual_progress' => $report->cumulative_progress ?? $report->progress_percentage,
            ]);

            // Regenerate schedule
            $scheduleCalculator = new ScheduleCalculator();
            $scheduleCalculator->updateFromProgress($this->project);
        }

        session()->flash('success', 'Laporan progress berhasil ditambahkan.');
        $this->closeModal();
    }

    public function confirmDelete(int $id)
    {
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
        $report = ProgressReport::find($this->deleteId);

        if (!$report) {
            session()->flash('error', 'Laporan tidak ditemukan.');
            $this->closeDeleteModal();
            return;
        }

        $hasRabItem = $report->rab_item_id ? true : false;

        if ($report->photos) {
            $disk = \App\Models\SystemSetting::getStorageDisk();
            foreach ($report->photos as $photo) {
                Storage::disk($disk)->delete($photo);
            }
        }
        $report->delete();

        // Regenerate schedule if the deleted report was linked to a RAB item
        if ($hasRabItem) {
            $scheduleCalculator = new ScheduleCalculator();
            $scheduleCalculator->updateFromProgress($this->project);
        }

        session()->flash('success', 'Laporan progress berhasil dihapus.');
        $this->closeDeleteModal();
    }

    public function render()
    {
        $query = $this->project->progressReports()->with(['rabItem', 'reporter'])->orderByDesc('report_date');

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
