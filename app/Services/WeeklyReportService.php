<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProgressReport;
use App\Models\ProjectFile;
use App\Models\RabItem;
use App\Models\RabSection;
use App\Models\WeeklyReport;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WeeklyReportService
{
    /**
     * Calculate period dates based on week number
     */
    public function calculatePeriod(Project $project, int $weekNumber): array
    {
        $projectStart = Carbon::parse($project->start_date)->startOfWeek();

        $periodStart = $projectStart->copy()->addWeeks($weekNumber - 1);
        $periodEnd = $periodStart->copy()->addDays(6);

        return [
            'start' => $periodStart,
            'end' => $periodEnd,
        ];
    }

    /**
     * Get the next available week number for a project
     */
    public function getNextWeekNumber(Project $project): int
    {
        $lastReport = WeeklyReport::where('project_id', $project->id)
            ->orderByDesc('week_number')
            ->first();

        return $lastReport ? $lastReport->week_number + 1 : 1;
    }

    /**
     * Get current project week based on today's date
     */
    public function getCurrentWeekNumber(Project $project): int
    {
        $projectStart = Carbon::parse($project->start_date)->startOfWeek();
        $now = Carbon::now();

        if ($now->lt($projectStart)) {
            return 1;
        }

        return (int) ceil($projectStart->diffInDays($now) / 7) + 1;
    }

    /**
     * Generate cumulative progress data for a given period
     */
    public function generateCumulativeData(Project $project, Carbon $periodEnd, int $weekNumber, ?WeeklyReport $report = null): array
    {
        $periodStart = $this->calculatePeriod($project, $weekNumber)['start'];
        $prevPeriodEnd = $periodStart->copy()->subDay();

        // Carry over from previous snapshot table if exists
        $prevCumulatives = [];
        if ($weekNumber > 1) {
            $prevReport = WeeklyReport::where('project_id', $project->id)
                ->where('week_number', $weekNumber - 1)
                ->first();

            if ($prevReport) {
                $prevCumulatives = \App\Models\ReportProgressSnapshot::where('report_type', 'weekly')
                    ->where('report_id', $prevReport->id)
                    ->pluck('actual_weight', 'rab_item_id')
                    ->toArray();
            }
        }

        // Get all RAB sections with items
        $sections = $project->rabSections()
            ->whereNull('parent_id')
            ->with(['recursiveChildren', 'items'])
            ->orderByRaw("CAST(SUBSTRING_INDEX(code, '.', 1) AS UNSIGNED)")
            ->get();

        $result = [
            'sections' => [],
            'totals' => $this->makeEmptyTotals(),
        ];

        $snapshots = [];

        foreach ($sections as $section) {
            $sectionData = $this->processSectionForCumulative($section, $project, $periodStart, $periodEnd, $prevPeriodEnd, $prevCumulatives, $snapshots);
            if (!empty($sectionData['items']) || !empty($sectionData['children'])) {
                $result['sections'][] = $sectionData;
                $this->accumulateTotals($result['totals'], $sectionData);
            }
        }

        // Save snapshots if report is provided
        if ($report) {
            \App\Models\ReportProgressSnapshot::where('report_type', 'weekly')
                ->where('report_id', $report->id)
                ->delete();

            $snapshotData = [];
            foreach ($snapshots as $itemId => $data) {
                $snapshotData[] = [
                    'report_type' => 'weekly',
                    'report_id' => $report->id,
                    'rab_item_id' => $itemId,
                    'planned_weight' => $data['planned'],
                    'actual_weight' => $data['actual'],
                    'deviation' => $data['actual'] - $data['planned'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            if (!empty($snapshotData)) {
                \App\Models\ReportProgressSnapshot::insert($snapshotData);
            }
        }

        // Calculate deviations for totals
        $this->calculateDeviationTotals($result['totals']);

        return $result;
    }

    /**
     * Process a section recursively for cumulative data
     */
    protected function processSectionForCumulative(RabSection $section, Project $project, Carbon $periodStart, Carbon $periodEnd, Carbon $prevPeriodEnd, array $prevCumulatives, array &$snapshots): array
    {
        $sectionData = [
            'id' => $section->id,
            'code' => $section->full_code,
            'name' => $section->name,
            'level' => $section->level ?? 0,
            'items' => [],
            'children' => [],
        ];

        // Process items in this section
        foreach ($section->items as $item) {
            $itemData = $this->calculateItemProgress($item, $project, $periodStart, $periodEnd, $prevPeriodEnd, $prevCumulatives);
            $sectionData['items'][] = $itemData;
            
            $snapshots[$item->id] = [
                'planned' => $itemData['planned']['cumulative'],
                'actual' => $itemData['actual']['cumulative'],
            ];
        }

        // Process child sections recursively
        if ($section->children && $section->children->count() > 0) {
            foreach ($section->children as $child) {
                $childData = $this->processSectionForCumulative($child, $project, $periodStart, $periodEnd, $prevPeriodEnd, $prevCumulatives, $snapshots);
                if (!empty($childData['items']) || !empty($childData['children'])) {
                    $sectionData['children'][] = $childData;
                }
            }
        }

        return $sectionData;
    }

    /**
     * Calculate progress for a single RAB item
     */
    protected function calculateItemProgress(RabItem $item, Project $project, Carbon $periodStart, Carbon $periodEnd, Carbon $prevPeriodEnd, array $prevCumulatives): array
    {
        $weight = (float) ($item->weight_percentage ?? 0);

        // Calculate planned progress based on schedule
        $plannedPrev = $this->calculatePlannedProgress($item, $project, $prevPeriodEnd);
        $plannedCumulative = $this->calculatePlannedProgress($item, $project, $periodEnd);
        $plannedCurrent = $plannedCumulative - $plannedPrev;

        // Priority: use value from previous snapshot if available
        if (isset($prevCumulatives[$item->id])) {
            $actualPrev = (float) $prevCumulatives[$item->id];
        } else {
            $actualPrev = $this->calculateActualProgress($item, $prevPeriodEnd);
        }

        $actualCumulative = $this->calculateActualProgress($item, $periodEnd);

        // Ensure cumulative is at least as large as prev if there were manual updates in previous reports
        if ($actualCumulative < $actualPrev) {
            $actualCumulative = $actualPrev;
        }

        $actualCurrent = $actualCumulative - $actualPrev;

        return [
            'code' => $item->full_code,
            'work_name' => $item->work_name,
            'volume' => $item->volume,
            'unit' => $item->unit,
            'weight' => round($weight, 4),
            'planned' => [
                'up_to_prev' => round($plannedPrev, 4),
                'current' => round($plannedCurrent, 4),
                'cumulative' => round($plannedCumulative, 4),
            ],
            'actual' => [
                'up_to_prev' => round($actualPrev, 4),
                'current' => round($actualCurrent, 4),
                'cumulative' => round($actualCumulative, 4),
            ],
            'deviation' => [
                'up_to_prev' => round($actualPrev - $plannedPrev, 4),
                'current' => round($actualCurrent - $plannedCurrent, 4),
                'cumulative' => round($actualCumulative - $plannedCumulative, 4),
            ],
            'remarks' => '',
        ];
    }

    /**
     * Calculate planned progress percentage for an item up to a given date
     */
    protected function calculatePlannedProgress(RabItem $item, Project $project, Carbon $asOfDate): float
    {
        if (!$item->planned_start || !$item->planned_end) {
            return 0;
        }

        $start = Carbon::parse($item->planned_start);
        $end = Carbon::parse($item->planned_end);
        $weight = (float) ($item->weight_percentage ?? 0);

        if ($asOfDate->lt($start)) {
            return 0; // Before item starts
        }

        if ($asOfDate->gte($end)) {
            return $weight; // After item ends, full weight
        }

        // Partial progress during item duration
        $totalDays = max(1, $start->diffInDays($end) + 1);
        $elapsedDays = $start->diffInDays($asOfDate) + 1;

        return ($elapsedDays / $totalDays) * $weight;
    }

    /**
     * Calculate actual progress percentage for an item up to a given date
     */
    protected function calculateActualProgress(RabItem $item, Carbon $asOfDate): float
    {
        $weight = (float) ($item->weight_percentage ?? 0);

        // Get the latest progress report before or on the given date
        $latestReport = ProgressReport::where('rab_item_id', $item->id)
            ->where('report_date', '<=', $asOfDate)
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->first();

        if (!$latestReport) {
            return 0;
        }

        // Convert item progress percentage to weighted progress
        $itemProgress = (float) ($latestReport->cumulative_progress ?? $latestReport->progress_percentage ?? 0);

        return ($itemProgress / 100) * $weight;
    }

    /**
     * Accumulate totals from section data
     */
    protected function accumulateTotals(array &$totals, array $sectionData): void
    {
        foreach ($sectionData['items'] as $item) {
            $totals['weight'] += $item['weight'];
            $totals['planned_prev'] += $item['planned']['up_to_prev'];
            $totals['planned_current'] += $item['planned']['current'];
            $totals['planned_cumulative'] += $item['planned']['cumulative'];
            $totals['actual_prev'] += $item['actual']['up_to_prev'];
            $totals['actual_current'] += $item['actual']['current'];
            $totals['actual_cumulative'] += $item['actual']['cumulative'];
        }

        foreach ($sectionData['children'] as $child) {
            $this->accumulateTotals($totals, $child);
        }
    }

    /**
     * Generate detail progress data (progress reports within period)
     */
    public function generateDetailData(Project $project, Carbon $periodStart, Carbon $periodEnd): array
    {
        $reports = ProgressReport::where('project_id', $project->id)
            ->whereBetween('report_date', [$periodStart, $periodEnd])
            ->with(['rabItem.section', 'reporter'])
            ->orderBy('report_date')
            ->get();

        return $reports->map(function ($report) {
            return [
                'id' => $report->id,
                'date' => $report->report_date->format('Y-m-d'),
                'date_label' => $report->report_date->format('d M Y'),
                'rab_item' => $report->rabItem ? [
                    'code' => $report->rabItem->full_code,
                    'name' => $report->rabItem->work_name,
                ] : null,
                'progress_percentage' => $report->progress_percentage,
                'cumulative_progress' => $report->cumulative_progress,
                'description' => $report->description,
                'issues' => $report->issues,
                'weather' => $report->weather_label,
                'workers_count' => $report->workers_count,
                'photos' => $report->photo_urls,
                'reporter' => $report->reporter ? $report->reporter->name : 'Unknown',
            ];
        })->toArray();
    }

    /**
     * Get available project images for documentation selection
     */
    public function getProjectImages(Project $project): Collection
    {
        return ProjectFile::where('project_id', $project->id)
            ->where('type', '!=', 'folder')
            ->where(function ($query) {
                $query->where('category', 'image')
                    ->orWhereIn('type', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
            })
            ->with('latestVersion')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Auto-generate weekly report for the current week
     */
    public function autoGenerate(Project $project, ?int $weekNumber = null): WeeklyReport
    {
        $weekNumber = $weekNumber ?? $this->getCurrentWeekNumber($project);
        $period = $this->calculatePeriod($project, $weekNumber);

        // Check if report already exists
        $existing = WeeklyReport::where('project_id', $project->id)
            ->where('week_number', $weekNumber)
            ->first();

        if ($existing) {
            // Update existing report data
            $cumulativeData = $this->generateCumulativeData($project, $period['end'], $weekNumber, $existing);
            $detailData = $this->generateDetailData($project, $period['start'], $period['end']);

            $existing->update([
                'cumulative_data' => $cumulativeData,
                'detail_data' => $detailData,
            ]);

            return $existing;
        }

        // Generate new report
        $report = WeeklyReport::create([
            'project_id' => $project->id,
            'week_number' => $weekNumber,
            'period_start' => $period['start'],
            'period_end' => $period['end'],
            'cover_title' => "Weekly Progress Report - Week {$weekNumber}",
            'cumulative_data' => [], // Will be filled below
            'detail_data' => [],     // Will be filled below
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        $cumulativeData = $this->generateCumulativeData($project, $period['end'], $weekNumber, $report);
        $detailData = $this->generateDetailData($project, $period['start'], $period['end']);

        $report->update([
            'cumulative_data' => $cumulativeData,
            'detail_data' => $detailData,
        ]);

        return $report;
    }

    /**
     * Auto-generate all missing weekly reports up to current week
     */
    public function autoGenerateAll(Project $project): array
    {
        $currentWeek = $this->getCurrentWeekNumber($project);
        $generated = [];

        for ($week = 1; $week <= $currentWeek; $week++) {
            $existing = WeeklyReport::where('project_id', $project->id)
                ->where('week_number', $week)
                ->first();

            if (!$existing) {
                $generated[] = $this->autoGenerate($project, $week);
            }
        }

        return $generated;
    }
    /**
     * Update cumulative data actual progress for a report and cascade to future weeks.
     *
     * @return array{data: array, cascaded_count: int, message: string}
     */
    public function updateCumulativeActuals(WeeklyReport $report, array $itemUpdates): array
    {
        $data = $report->cumulative_data;
        if (!$data || !isset($data['sections'])) {
            return [
                'data' => $data ?? [],
                'cascaded_count' => 0,
                'message' => 'No cumulative data found.',
            ];
        }

        $totals = $this->makeEmptyTotals();

        foreach ($data['sections'] as &$section) {
            $this->updateSectionItems($section, $itemUpdates, $totals);
        }

        $this->calculateDeviationTotals($totals);

        $data['totals'] = $totals;
        $report->update(['cumulative_data' => $data]);

        // Cascade update to subsequent weeks
        $cascadedCount = $this->cascadeToSubsequentWeeks($report->project, $report, $data);

        $message = 'Data realisasi berhasil disimpan.';
        if ($cascadedCount > 0) {
            $message .= " ({$cascadedCount} minggu berikutnya juga diperbarui)";
        }

        return [
            'data' => $data,
            'cascaded_count' => $cascadedCount,
            'message' => $message,
        ];
    }

    /**
     * Collect item code => actual_cumulative from sections recursively.
     */
    public function collectItemCumulatives(array $sections, array &$map): void
    {
        foreach ($sections as $section) {
            foreach ($section['items'] ?? [] as $item) {
                $map[$item['code']] = $item['actual']['cumulative'] ?? 0;
            }
            if (isset($section['children'])) {
                $this->collectItemCumulatives($section['children'], $map);
            }
        }
    }

    protected function cascadeToSubsequentWeeks(Project $project, WeeklyReport $currentReport, array $currentData): int
    {
        $subsequentReports = WeeklyReport::where('project_id', $project->id)
            ->where('week_number', '>', $currentReport->week_number)
            ->orderBy('week_number')
            ->get();

        if ($subsequentReports->isEmpty()) return 0;

        // 1. Collect item cumulatives from the current report (which was just updated)
        $currentSnapshots = \App\Models\ReportProgressSnapshot::where('report_type', 'weekly')
            ->where('report_id', $currentReport->id)
            ->pluck('actual_weight', 'rab_item_id')
            ->toArray();

        foreach ($subsequentReports as $nextReport) {
            // 2. For each item, update the snapshot
            $nextSnapshots = \App\Models\ReportProgressSnapshot::where('report_type', 'weekly')
                ->where('report_id', $nextReport->id)
                ->get();

            foreach ($nextSnapshots as $snapshot) {
                if (isset($currentSnapshots[$snapshot->rab_item_id])) {
                    $prevCumulative = $currentSnapshots[$snapshot->rab_item_id];
                    
                    $currentActual = $this->getItemActualCurrent($nextReport, $snapshot->rab_item_id);
                    $newCumulative = $prevCumulative + $currentActual;
                    
                    $snapshot->update([
                        'actual_weight' => $newCumulative,
                        'deviation' => $newCumulative - $snapshot->planned_weight,
                    ]);
                    
                    // Update for the next iteration
                    $currentSnapshots[$snapshot->rab_item_id] = $newCumulative;
                }
            }

            // 3. Sync JSON
            $this->syncReportWithSnapshots($nextReport);
        }

        return $subsequentReports->count();
    }

    protected function getItemActualCurrent(WeeklyReport $report, int $rabItemId): float
    {
        $sections = $report->cumulative_data['sections'] ?? [];
        return $this->findItemActualCurrent($sections, $rabItemId);
    }

    protected function findItemActualCurrent(array $sections, int $rabItemId): float
    {
        foreach ($sections as $section) {
            foreach ($section['items'] ?? [] as $item) {
                $rabItem = \App\Models\RabItem::find($rabItemId);
                if (($item['id'] ?? null) == $rabItemId || ($item['code'] ?? null) == ($rabItem->full_code ?? '')) {
                    return (float) ($item['actual']['current'] ?? 0);
                }
            }
            if (isset($section['children'])) {
                $val = $this->findItemActualCurrent($section['children'], $rabItemId);
                if ($val !== 0.0) return $val;
            }
        }
        return 0;
    }

    protected function updateSectionItems(array &$section, array $itemUpdates, array &$totals): void
    {
        foreach ($section['items'] as &$item) {
            $code = $item['code'];

            if (isset($itemUpdates[$code])) {
                $newActualCurrent = round((float) $itemUpdates[$code], 4);
                $item['actual']['current'] = $newActualCurrent;
                $item['actual']['cumulative'] = round($item['actual']['up_to_prev'] + $newActualCurrent, 4);
                $item['deviation']['current'] = round($newActualCurrent - $item['planned']['current'], 4);
                $item['deviation']['cumulative'] = round($item['actual']['cumulative'] - $item['planned']['cumulative'], 4);
            }

            $this->accumulateItemTotals($totals, $item);
        }

        if (isset($section['children'])) {
            foreach ($section['children'] as &$child) {
                $this->updateSectionItems($child, $itemUpdates, $totals);
            }
        }
    }

    /**
     * Create an empty totals array.
     */
    protected function makeEmptyTotals(): array
    {
        return [
            'weight' => 0, 'planned_prev' => 0, 'planned_current' => 0, 'planned_cumulative' => 0,
            'actual_prev' => 0, 'actual_current' => 0, 'actual_cumulative' => 0,
            'deviation_prev' => 0, 'deviation_current' => 0, 'deviation_cumulative' => 0,
        ];
    }

    /**
     * Calculate deviation fields on a totals array.
     */
    protected function calculateDeviationTotals(array &$totals): void
    {
        $totals['deviation_prev'] = $totals['actual_prev'] - $totals['planned_prev'];
        $totals['deviation_current'] = $totals['actual_current'] - $totals['planned_current'];
        $totals['deviation_cumulative'] = $totals['actual_cumulative'] - $totals['planned_cumulative'];
    }

    /**
     * Accumulate a single item's values into running totals.
     */
    protected function accumulateItemTotals(array &$totals, array $item): void
    {
        $totals['weight'] += $item['weight'] ?? 0;
        $totals['planned_prev'] += $item['planned']['up_to_prev'] ?? 0;
        $totals['planned_current'] += $item['planned']['current'] ?? 0;
        $totals['planned_cumulative'] += $item['planned']['cumulative'] ?? 0;
        $totals['actual_prev'] += $item['actual']['up_to_prev'] ?? 0;
        $totals['actual_current'] += $item['actual']['current'] ?? 0;
        $totals['actual_cumulative'] += $item['actual']['cumulative'] ?? 0;
    }

    // ========================
    // Workflow Transitions
    // ========================

    /**
     * Submit a weekly report for review.
     * Transition: draft|rejected → in_review
     */
    public function submitForReview(WeeklyReport $report, int $submitterId): WeeklyReport
    {
        if (!$report->can_submit) {
            throw new \Exception("Weekly report dengan status '{$report->status_label}' tidak dapat diajukan untuk review.");
        }

        $report->update([
            'status' => WeeklyReport::STATUS_IN_REVIEW,
            'submitted_by' => $submitterId,
            'submitted_at' => now(),
            'rejection_reason' => null, // Clear any previous rejection
        ]);

        \App\Models\ApprovalLog::create([
            'approvable_type' => WeeklyReport::class,
            'approvable_id' => $report->id,
            'level' => 1,
            'user_id' => $submitterId,
            'status' => 'submitted',
            'comment' => 'Weekly report diajukan untuk review.',
        ]);

        return $report;
    }

    /**
     * Approve a weekly report.
     * Transition: in_review → approved
     */
    public function approve(WeeklyReport $report, int $approverId, ?string $comment = null): WeeklyReport
    {
        if (!$report->can_approve) {
            throw new \Exception("Weekly report dengan status '{$report->status_label}' tidak dapat disetujui.");
        }

        $report->update([
            'status' => WeeklyReport::STATUS_APPROVED,
            'approved_by' => $approverId,
            'approved_at' => now(),
            'reviewed_by' => $report->reviewed_by ?? $approverId,
            'reviewed_at' => $report->reviewed_at ?? now(),
        ]);

        \App\Models\ApprovalLog::create([
            'approvable_type' => WeeklyReport::class,
            'approvable_id' => $report->id,
            'level' => 2,
            'user_id' => $approverId,
            'status' => 'approved',
            'comment' => $comment ?? 'Weekly report disetujui.',
        ]);

        return $report;
    }

    /**
     * Reject a weekly report.
     * Transition: in_review → rejected
     */
    public function reject(WeeklyReport $report, int $rejecterId, string $reason): WeeklyReport
    {
        if ($report->status !== WeeklyReport::STATUS_IN_REVIEW) {
            throw new \Exception("Hanya weekly report berstatus 'Sedang Review' yang dapat ditolak.");
        }

        $report->update([
            'status' => WeeklyReport::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'reviewed_by' => $rejecterId,
            'reviewed_at' => now(),
        ]);

        \App\Models\ApprovalLog::create([
            'approvable_type' => WeeklyReport::class,
            'approvable_id' => $report->id,
            'level' => 2,
            'user_id' => $rejecterId,
            'status' => 'rejected',
            'comment' => $reason,
        ]);

        return $report;
    }

    /**
     * Publish a weekly report (make visible to owners/stakeholders).
     * Transition: approved → published
     */
    public function publish(WeeklyReport $report, int $publisherId): WeeklyReport
    {
        if (!$report->can_publish) {
            throw new \Exception("Hanya weekly report berstatus 'Disetujui' yang dapat dipublish.");
        }

        $report->update([
            'status' => WeeklyReport::STATUS_PUBLISHED,
        ]);

        \App\Models\ApprovalLog::create([
            'approvable_type' => WeeklyReport::class,
            'approvable_id' => $report->id,
            'level' => 3,
            'user_id' => $publisherId,
            'status' => 'published',
            'comment' => 'Weekly report dipublish.',
        ]);

        return $report;
    }
}
