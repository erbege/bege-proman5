<?php

namespace App\Services;

use App\Models\ProgressReport;
use App\Models\Project;
use App\Models\RabItem;
use App\Models\SystemSetting;
use App\Notifications\ProgressReportCreatedNotification;
use App\Notifications\ProgressReportStatusNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProgressReportService
{
    protected ImageResizeService $imageResizer;

    protected ScheduleCalculator $scheduleCalculator;

    public function __construct(ImageResizeService $imageResizer, ScheduleCalculator $scheduleCalculator)
    {
        $this->imageResizer = $imageResizer;
        $this->scheduleCalculator = $scheduleCalculator;
    }

    /**
     * Create a new progress report with proper transaction, locking, and side effects.
     */
    public function create(Project $project, array $data, array $photoFiles = [], ?int $reporterId = null): ProgressReport
    {
        return DB::transaction(function () use ($project, $data, $photoFiles, $reporterId) {
            $data['project_id'] = $project->id;
            $data['reported_by'] = $reporterId ?? auth()->id();
            $data['status'] = ProgressReport::STATUS_DRAFT;
            $data['progress_percentage'] = (float) ($data['progress_percentage'] ?? 0);

            $rabItem = null;
            if (!empty($data['rab_item_id'])) {
                $rabItem = RabItem::lockForUpdate()->find($data['rab_item_id']);
                if ($rabItem) {
                    $data['cumulative_progress'] = $this->calculateCumulativeProgressForNewReport($rabItem, $data['progress_percentage']);
                }
            }

            if (!isset($data['cumulative_progress'])) {
                $data['cumulative_progress'] = $data['progress_percentage'];
            }

            if (!empty($photoFiles)) {
                $data['photos'] = $this->processPhotos($photoFiles, $project);
            }

            $report = ProgressReport::create($data);

            if ($rabItem) {
                $rabItem->update([
                    'actual_progress' => $report->cumulative_progress ?? $report->progress_percentage,
                ]);
                dispatch(new \App\Jobs\RecalculateProjectScheduleJob($project));
            }

            if (!empty($data['material_usage_summary'])) {
                $this->syncMaterials($report, $data['material_usage_summary']);
            }

            return $report;
        });
    }

    /**
     * Update an existing progress report.
     */
    public function updateReport(ProgressReport $report, Project $project, array $data, array $photoFiles = []): ProgressReport
    {
        return DB::transaction(function () use ($report, $project, $data, $photoFiles) {
            $oldRabItemId = $report->rab_item_id;

            if (!empty($photoFiles)) {
                $newPhotos = $this->processPhotos($photoFiles, $project);
                $existingPhotos = is_array($report->photos) ? $report->photos : [];
                $data['photos'] = array_merge($existingPhotos, $newPhotos);
            }

            $report->update($data);

            if ($oldRabItemId && $oldRabItemId != $report->rab_item_id) {
                $oldRabItem = RabItem::lockForUpdate()->find($oldRabItemId);
                if ($oldRabItem) {
                    $this->rebuildCumulativeProgress($oldRabItem);
                }
            }

            if ($report->rab_item_id) {
                $newRabItem = RabItem::lockForUpdate()->find($report->rab_item_id);
                if ($newRabItem) {
                    $this->rebuildCumulativeProgress($newRabItem);
                }
            }

            if (isset($data['material_usage_summary'])) {
                $this->syncMaterials($report, $data['material_usage_summary']);
            }

            dispatch(new \App\Jobs\RecalculateProjectScheduleJob($project));

            return $report;
        });
    }

    /**
     * Delete a progress report and recalculate related RAB item progress.
     */
    public function delete(ProgressReport $report, Project $project): void
    {
        if (!$report->canDelete) {
            throw new \Exception('Laporan dengan status ini tidak dapat dihapus.');
        }

        DB::transaction(function () use ($report, $project) {
            $rabItem = $report->rabItem;
            $hasRabItem = (bool) $report->rab_item_id;

            $this->deletePhotos($report);
            $report->delete();

            if ($hasRabItem && $rabItem) {
                $this->recalculateRabProgress($rabItem);
                dispatch(new \App\Jobs\RecalculateProjectScheduleJob($project));
            }
        });
    }

    /**
     * Recalculate RAB item's actual_progress based on its latest non-rejected progress report.
     */
    public function recalculateRabProgress(RabItem $rabItem): float
    {
        $latestReport = ProgressReport::where('rab_item_id', $rabItem->id)
            ->whereNotIn('status', [ProgressReport::STATUS_REJECTED])
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->first();

        $newProgress = $latestReport ? (float) $latestReport->cumulative_progress : 0;
        $rabItem->update(['actual_progress' => $newProgress]);

        return $newProgress;
    }

    /**
     * Rebuild all cumulative progress sequentially for a given RAB item (excluding rejected).
     */
    public function rebuildCumulativeProgress(RabItem $rabItem): void
    {
        $reports = ProgressReport::where('rab_item_id', $rabItem->id)
            ->whereNotIn('status', [ProgressReport::STATUS_REJECTED])
            ->orderBy('report_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $cumulative = 0;
        foreach ($reports as $report) {
            $cumulative = min(100, $cumulative + (float) $report->progress_percentage);
            $report->updateQuietly(['cumulative_progress' => $cumulative]);
        }

        $rabItem->update(['actual_progress' => $cumulative]);
    }

    protected function calculateCumulativeProgressForNewReport(RabItem $rabItem, float $progressPercentage): float
    {
        $latestCumulative = ProgressReport::where('rab_item_id', $rabItem->id)
            ->whereNotIn('status', [ProgressReport::STATUS_REJECTED])
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->value('cumulative_progress');

        return min(100, ((float) ($latestCumulative ?? 0)) + $progressPercentage);
    }

    // ========================
    // Workflow Transitions
    // ========================

    /**
     * Submit a progress report for review.
     * Transition: draft → submitted
     */
    public function submit(ProgressReport $report): ProgressReport
    {
        if (!$report->canSubmit) {
            throw new \Exception("Laporan dengan status '{$report->status_label}' tidak dapat diajukan.");
        }

        if ($report->reported_by != auth()->id() && !auth()->user()->hasRole(['Superadmin', 'super-admin'])) {
            throw new \Exception('Hanya pembuat laporan yang dapat mengajukan laporan ini.');
        }

        $this->validateSubmissionCompliance($report);

        $report->update(['status' => ProgressReport::STATUS_SUBMITTED]);
        $this->notifyWorkflow($report, 'submitted');

        return $report;
    }

    /**
     * Validate minimum compliance fields before a report can be submitted.
     */
    protected function validateSubmissionCompliance(ProgressReport $report): void
    {
        if (blank((string) $report->next_day_plan)) {
            throw new \Exception('Rencana kerja esok hari wajib diisi sebelum laporan diajukan.');
        }

        $safety = is_array($report->safety_details) ? $report->safety_details : [];
        if (!array_key_exists('incidents', $safety) || !array_key_exists('near_miss', $safety)) {
            throw new \Exception('Data K3 minimal harus memuat jumlah insiden dan near miss.');
        }

        if (!is_numeric($safety['incidents']) || !is_numeric($safety['near_miss'])) {
            throw new \Exception('Nilai insiden dan near miss pada data K3 harus berupa angka.');
        }

        $equipment = is_array($report->equipment_details) ? $report->equipment_details : [];
        foreach ($equipment as $index => $item) {
            if (!is_array($item)) {
                throw new \Exception('Format data peralatan tidak valid.');
            }

            if (blank((string) ($item['name'] ?? ''))) {
                throw new \Exception("Nama peralatan pada baris ke-" . ($index + 1) . ' wajib diisi.');
            }

            if (!isset($item['qty']) || !is_numeric($item['qty']) || (float) $item['qty'] <= 0) {
                throw new \Exception("Jumlah peralatan pada baris ke-" . ($index + 1) . ' harus lebih dari 0.');
            }
        }
    }

    /**
     * Approve a progress report.
     * Transition: submitted → reviewed
     */
    public function approve(ProgressReport $report, int $reviewerId, ?string $notes = null): ProgressReport
    {
        if ($report->status !== ProgressReport::STATUS_SUBMITTED) {
            throw new \Exception("Hanya laporan berstatus 'Diajukan' yang dapat diverifikasi.");
        }

        if ($reviewerId === $report->reported_by) {
            throw new \RuntimeException('Pelapor tidak dapat menyetujui laporan miliknya sendiri.');
        }

        $report->update([
            'status' => ProgressReport::STATUS_REVIEWED,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        event(new \App\Events\ProgressReportApproved($report));

        $this->notifyWorkflow($report, 'approved');

        return $report;
    }

    /**
     * Reject a progress report for revision.
     * Transition: submitted → rejected
     */
    public function reject(ProgressReport $report, int $reviewerId, ?string $notes = null): ProgressReport
    {
        if ($report->status !== ProgressReport::STATUS_SUBMITTED) {
            throw new \Exception("Hanya laporan berstatus 'Diajukan' yang dapat ditolak.");
        }

        if ($reviewerId === $report->reported_by) {
            throw new \RuntimeException('Pelapor tidak dapat menolak laporan miliknya sendiri.');
        }

        $report->update([
            'status' => ProgressReport::STATUS_REJECTED,
            'rejected_by' => $reviewerId,
            'rejected_at' => now(),
            'rejected_notes' => $notes,
        ]);
        $this->notifyWorkflow($report, 'rejected');

        return $report;
    }

    /**
     * Publish a progress report (make visible to owner).
     * Transition: reviewed → published
     */
    public function publish(ProgressReport $report, int $publisherId): ProgressReport
    {
        if ($report->status !== ProgressReport::STATUS_REVIEWED) {
            throw new \Exception("Hanya laporan berstatus 'Diverifikasi' yang dapat dipublikasikan.");
        }

        if ($publisherId === $report->reported_by) {
            throw new \RuntimeException('Pelapor tidak dapat memublikasikan laporan miliknya sendiri.');
        }

        $report->update([
            'status' => ProgressReport::STATUS_PUBLISHED,
            'published_by' => $publisherId,
            'published_at' => now(),
        ]);
        $this->notifyWorkflow($report, 'published');

        return $report;
    }

    /**
     * Bulk approve submitted reports.
     */
    public function bulkApprove(array $reportIds, int $reviewerId): int
    {
        $reports = ProgressReport::whereIn('id', $reportIds)
            ->where('status', ProgressReport::STATUS_SUBMITTED)
            ->where('reported_by', '!=', $reviewerId)
            ->get();

        $count = 0;
        foreach ($reports as $report) {
            $report->update([
                'status' => ProgressReport::STATUS_REVIEWED,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
            ]);

            event(new \App\Events\ProgressReportApproved($report));
            $count++;
        }

        return $count;
    }

    // ========================
    // Notifications
    // ========================

    public function notifyTeam(ProgressReport $report, ?int $excludeUserId = null): void
    {
        $this->notifyApprovers($report, $excludeUserId);
    }

    public function notifyApprovers(ProgressReport $report, ?int $excludeUserId = null): void
    {
        $report->loadMissing(['project', 'reporter']);

        // Get users in the same project who have 'progress.approve' permission
        $approvers = $report->project->team()
            ->permission('progress.approve')
            ->where('users.id', '!=', $excludeUserId ?? $report->reported_by)
            ->get();

        // Also include Superadmins as they usually have all permissions and should know
        $admins = \App\Models\User::role(['Superadmin', 'super-admin', 'administrator'])
            ->where('id', '!=', $excludeUserId ?? $report->reported_by)
            ->get();

        $recipients = $approvers->merge($admins)->unique('id');

        if ($recipients->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::send($recipients, new \App\Notifications\ProgressReportCreatedNotification($report));
        }
    }

    protected function notifyWorkflow(ProgressReport $report, string $action): void
    {
        $report->loadMissing(['project', 'reporter']);

        $notification = new ProgressReportStatusNotification($report, $action);
        $excludeUserId = auth()->id() ?? $report->reported_by;

        if ($action === 'submitted') {
            $this->notifyApprovers($report, $excludeUserId);
            return;
        }

        if ($report->reporter) {
            NotificationHelper::sendToUser($report->reporter, $notification, true, $excludeUserId);
        }
    }

    // ========================
    // Photo Helpers
    // ========================

    protected function processPhotos(array $photoFiles, Project $project): array
    {
        $validPhotos = array_filter($photoFiles, function ($photo) {
            return $photo instanceof UploadedFile
                || $photo instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
        });

        if (empty($validPhotos)) {
            return [];
        }

        $disk = SystemSetting::getStorageDisk();

        return $this->imageResizer->processMultiple(
            $validPhotos,
            "progress/{$project->id}",
            $disk
        );
    }

    protected function deletePhotos(ProgressReport $report): void
    {
        if (!$report->photos || !is_array($report->photos)) {
            return;
        }

        $disk = SystemSetting::getStorageDisk();
        foreach ($report->photos as $photo) {
            Storage::disk($disk)->delete($photo);
        }
    }

    // ========================
    // Analytics & KPIs
    // ========================

    /**
     * Calculate Progress Variance (Actual - Planned)
     * Positive means ahead of schedule, negative means delayed.
     */
    public function calculateProgressVariance(Project $project): float
    {
        $actual = $project->progress_percentage ?? 0;

        // Target progress from latest schedule or estimated planned progress
        // Here we assume project schedules might have planned progress if implemented
        $planned = $project->planned_progress ?? $actual; // Fallback to actual if no planned is set

        return (float) ($actual - $planned);
    }

    /**
     * Calculate Productivity Index (Progress % per worker on average for the current week)
     */
    public function calculateProductivityIndex(Project $project): float
    {
        $reports = ProgressReport::where('project_id', $project->id)
            ->where('report_date', '>=', now()->subDays(7))
            ->whereNotIn('status', [ProgressReport::STATUS_REJECTED])
            ->get();

        if ($reports->isEmpty())
            return 0.0;

        $totalProgress = $reports->sum('progress_percentage');
        $totalWorkers = $reports->sum('workers_count');

        if ($totalWorkers <= 0)
            return 0.0;

        return round($totalProgress / $totalWorkers, 4);
    }

    /**
     * Calculate Safety Score (Percentage of reports without incidents)
     */
    public function calculateSafetyScore(Project $project): float
    {
        $totalReports = ProgressReport::where('project_id', $project->id)->count();
        if ($totalReports === 0)
            return 100.0;

        $reportsWithIncidents = ProgressReport::where('project_id', $project->id)
            ->whereRaw('JSON_EXTRACT(safety_details, "$.incidents") > 0')
            ->count();

        $safeReports = $totalReports - $reportsWithIncidents;
        return round(($safeReports / $totalReports) * 100, 1);
    }

    /**
     * Sync material usage summary to progress_report_materials table.
     */
    protected function syncMaterials(ProgressReport $report, array $materials): void
    {
        $report->progressReportMaterials()->delete();

        foreach ($materials as $item) {
            if (empty($item['material_name'])) {
                continue;
            }

            $report->progressReportMaterials()->create([
                'material_id' => $item['material_id'] ?? null,
                'material_name' => $item['material_name'],
                'quantity' => $item['qty_used'] ?? 0,
                'unit' => $item['unit'] ?? null,
                'notes' => ($item['is_manual'] ?? false) ? 'Manual Entry' : 'Inventory Link',
            ]);
        }
    }
}
