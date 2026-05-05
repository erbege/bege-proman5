<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProgressReport;
use App\Models\ProjectFile;
use App\Models\RabItem;
use App\Models\RabSection;
use App\Models\MonthlyReport;
use App\Models\ApprovalLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Helpers\NotificationHelper;

class MonthlyReportService
{
    /**
     * Calculate period dates based on year and month
     */
    public function calculatePeriod(int $year, int $month): array
    {
        $periodStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        return [
            'start' => $periodStart,
            'end' => $periodEnd,
        ];
    }

    /**
     * Get the next available month for a project
     */
    public function getNextMonth(Project $project): array
    {
        $lastReport = MonthlyReport::where('project_id', $project->id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();

        if ($lastReport) {
            $date = Carbon::createFromDate($lastReport->year, $lastReport->month, 1)->addMonth();
            return ['year' => $date->year, 'month' => $date->month];
        }

        $projectStart = Carbon::parse($project->start_date);
        return ['year' => $projectStart->year, 'month' => $projectStart->month];
    }

    /**
     * Generate cumulative progress data for a given period
     */
    public function generateCumulativeData(Project $project, Carbon $periodEnd, int $year, int $month, ?MonthlyReport $report = null): array
    {
        $periodStart = $this->calculatePeriod($year, $month)['start'];
        $prevPeriodEnd = $periodStart->copy()->subDay();

        // Carry over from previous snapshot table if exists
        $prevCumulatives = [];
        $prevDate = $periodStart->copy()->subMonth();
        $prevReport = MonthlyReport::where('project_id', $project->id)
            ->where('year', $prevDate->year)
            ->where('month', $prevDate->month)
            ->first();

        if ($prevReport) {
            $prevCumulatives = \App\Models\ReportProgressSnapshot::where('report_type', 'monthly')
                ->where('report_id', $prevReport->id)
                ->pluck('actual_weight', 'rab_item_id')
                ->toArray();
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
            \App\Models\ReportProgressSnapshot::where('report_type', 'monthly')
                ->where('report_id', $report->id)
                ->delete();

            $snapshotData = [];
            foreach ($snapshots as $itemId => $data) {
                $snapshotData[] = [
                    'report_type' => 'monthly',
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
     * Calculate progress for a specific RAB Item
     */
    protected function calculateItemProgress(RabItem $item, Project $project, Carbon $periodStart, Carbon $periodEnd, Carbon $prevPeriodEnd, array $prevCumulatives): array
    {
        $weight = (float) ($item->weight_percentage ?? 0);

        // Calculate planned progress based on schedule (Linear)
        $plannedPrevWeight = $this->calculatePlannedProgress($item, $project, $prevPeriodEnd);
        $plannedCumWeight = $this->calculatePlannedProgress($item, $project, $periodEnd);
        $plannedCurrentWeight = $plannedCumWeight - $plannedPrevWeight;

        // Actual: Previous cumulative (either from previous snapshot or calculate from scratch)
        if (isset($prevCumulatives[$item->id])) {
            $actualPrevWeight = (float) $prevCumulatives[$item->id];
            $actualPrevProgress = $weight > 0 ? ($actualPrevWeight / $weight) * 100 : 0;
        } else {
            $actualPrevWeight = $this->calculateActualProgress($item, $prevPeriodEnd);
            $actualPrevProgress = $weight > 0 ? ($actualPrevWeight / $weight) * 100 : 0;
        }

        // Actual: Cumulative up to current period end
        $actualCumWeight = $this->calculateActualProgress($item, $periodEnd);
        
        // Ensure cumulative is at least as large as prev
        if ($actualCumWeight < $actualPrevWeight) {
            $actualCumWeight = $actualPrevWeight;
        }

        $actualCumProgress = $weight > 0 ? ($actualCumWeight / $weight) * 100 : 0;
        $actualCurrentWeight = $actualCumWeight - $actualPrevWeight;

        // Deviations
        $deviationPrev = $actualPrevWeight - $plannedPrevWeight;
        $deviationCurrent = $actualCurrentWeight - $plannedCurrentWeight;
        $deviationCum = $actualCumWeight - $plannedCumWeight;

        return [
            'id' => $item->id,
            'code' => $item->full_code,
            'work_name' => $item->work_name,
            'unit' => $item->unit,
            'volume' => $item->volume,
            'weight' => round($weight, 4),
            
            'planned' => [
                'up_to_prev' => round($plannedPrevWeight, 4),
                'current' => round($plannedCurrentWeight, 4),
                'cumulative' => round($plannedCumWeight, 4),
            ],
            
            'actual' => [
                'up_to_prev' => round($actualPrevWeight, 4),
                'current' => round($actualCurrentWeight, 4),
                'cumulative' => round($actualCumWeight, 4),
                'progress' => round($actualCumProgress, 2),
            ],
            
            'deviation' => [
                'up_to_prev' => round($deviationPrev, 4),
                'current' => round($deviationCurrent, 4),
                'cumulative' => round($deviationCum, 4),
            ],
        ];
    }

    /**
     * Calculate planned progress weight for an item up to a given date (Linear)
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
            return 0;
        }

        if ($asOfDate->gte($end)) {
            return $weight;
        }

        $totalDays = max(1, $start->diffInDays($end) + 1);
        $elapsedDays = $start->diffInDays($asOfDate) + 1;

        return ($elapsedDays / $totalDays) * $weight;
    }

    /**
     * Calculate actual progress weight for an item up to a given date
     */
    protected function calculateActualProgress(RabItem $item, Carbon $asOfDate): float
    {
        $weight = (float) ($item->weight_percentage ?? 0);

        $latestReport = \App\Models\ProgressReport::where('rab_item_id', $item->id)
            ->where('report_date', '<=', $asOfDate)
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->first();

        if (!$latestReport) {
            return 0;
        }

        $itemProgress = (float) ($latestReport->cumulative_progress ?? $latestReport->progress_percentage ?? 0);

        return ($itemProgress / 100) * $weight;
    }

    /**
     * Recursively collect actual cumulatives from a previous report's data
     */
    protected function collectItemCumulatives(array $sections, array &$cumulatives): void
    {
        foreach ($sections as $section) {
            if (isset($section['items']) && is_array($section['items'])) {
                foreach ($section['items'] as $item) {
                    $cumulatives[$item['id']] = [
                        'weight' => $item['actual']['cumulative'] ?? 0,
                        'progress' => $item['actual']['progress'] ?? 0,
                    ];
                }
            }

            if (isset($section['children']) && is_array($section['children'])) {
                $this->collectItemCumulatives($section['children'], $cumulatives);
            }
        }
    }

    /**
     * Accumulate totals from a section into the global totals
     */
    protected function accumulateTotals(array &$totals, array $sectionData): void
    {
        if (isset($sectionData['items']) && is_array($sectionData['items'])) {
            foreach ($sectionData['items'] as $item) {
                $totals['weight'] += $item['weight'];
                $totals['planned_prev'] += $item['planned']['up_to_prev'];
                $totals['planned_current'] += $item['planned']['current'];
                $totals['planned_cumulative'] += $item['planned']['cumulative'];
                $totals['actual_prev'] += $item['actual']['up_to_prev'];
                $totals['actual_current'] += $item['actual']['current'];
                $totals['actual_cumulative'] += $item['actual']['cumulative'];
            }
        }

        if (isset($sectionData['children']) && is_array($sectionData['children'])) {
            foreach ($sectionData['children'] as $child) {
                $this->accumulateTotals($totals, $child);
            }
        }
    }

    /**
     * Generate daily detail data for the period
     */
    public function generateDetailData(Project $project, Carbon $periodStart, Carbon $periodEnd): array
    {
        $reports = \App\Models\ProgressReport::where('project_id', $project->id)
            ->whereBetween('report_date', [$periodStart, $periodEnd])
            ->with(['rabItem', 'reporter'])
            ->orderBy('report_date')
            ->get();

        return $reports->map(function ($report) {
            return [
                'id' => $report->id,
                'date' => $report->report_date->format('Y-m-d'),
                'date_label' => $report->report_date->format('d M Y'),
                'rab_item' => $report->rabItem ? [
                    'code' => $report->rabItem->full_code,
                    'work_name' => $report->rabItem->work_name,
                ] : null,
                'progress_percentage' => (float) $report->progress_percentage,
                'cumulative_progress' => (float) $report->cumulative_progress,
                'description' => $report->description,
                'issues' => $report->issues,
                'weather' => $report->weather_label,
                'workers_count' => $report->workers_count,
                'photos' => $report->photo_urls,
                'reporter' => $report->reporter ? $report->reporter->name : 'Unknown',
            ];
        })->toArray();
    }

    public function updateCumulativeActuals(MonthlyReport $report, array $itemUpdates): array
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

        $cascadedCount = $this->cascadeToSubsequentMonths($report->project, $report, $data);

        $message = 'Data realisasi berhasil disimpan.';
        if ($cascadedCount > 0) {
            $message .= " ({$cascadedCount} bulan berikutnya juga diperbarui)";
        }

        return [
            'data' => $data,
            'cascaded_count' => $cascadedCount,
            'message' => $message,
        ];
    }

    protected function cascadeToSubsequentMonths(Project $project, MonthlyReport $currentReport, array $currentData): int
    {
        $currentDate = Carbon::createFromDate($currentReport->year, $currentReport->month, 1);
        $subsequentReports = MonthlyReport::where('project_id', $project->id)
            ->where(function($q) use ($currentDate) {
                $q->where('year', '>', $currentDate->year)
                  ->orWhere(function($sq) use ($currentDate) {
                      $sq->where('year', $currentDate->year)->where('month', '>', $currentDate->month);
                  });
            })
            ->orderBy('year')->orderBy('month')
            ->get();

        if ($subsequentReports->isEmpty()) return 0;

        // 1. Collect item cumulatives from the current report (which was just updated)
        $currentSnapshots = \App\Models\ReportProgressSnapshot::where('report_type', 'monthly')
            ->where('report_id', $currentReport->id)
            ->pluck('actual_weight', 'rab_item_id')
            ->toArray();

        foreach ($subsequentReports as $nextReport) {
            // 2. For each item, update the 'up_to_prev' and 'cumulative' in snapshot
            // This is complex for bulk SQL if we don't store 'current' actual.
            // For now, we'll iterate and update snapshots then sync.
            
            $nextSnapshots = \App\Models\ReportProgressSnapshot::where('report_type', 'monthly')
                ->where('report_id', $nextReport->id)
                ->get();

            foreach ($nextSnapshots as $snapshot) {
                if (isset($currentSnapshots[$snapshot->rab_item_id])) {
                    $prevCumulative = $currentSnapshots[$snapshot->rab_item_id];
                    // currentActual = currentCumulative - oldUpToPrev
                    // In our case, we want to maintain the 'currentActual' of the next month
                    // but update its 'up_to_prev' to the 'prevCumulative'.
                    
                    $oldUpToPrev = $snapshot->actual_weight - $this->getItemActualCurrent($nextReport, $snapshot->rab_item_id);
                    $currentActual = $snapshot->actual_weight - $oldUpToPrev;
                    
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

    protected function getItemActualCurrent(MonthlyReport $report, int $rabItemId): float
    {
        // Pull from JSON as a fallback to find what the 'current' actual was
        $sections = $report->cumulative_data['sections'] ?? [];
        return $this->findItemActualCurrent($sections, $rabItemId);
    }

    protected function findItemActualCurrent(array $sections, int $rabItemId): float
    {
        foreach ($sections as $section) {
            foreach ($section['items'] ?? [] as $item) {
                // Since we don't have ID in some JSON versions, use code as fallback
                $rabItem = RabItem::find($rabItemId);
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

    /**
     * Sync JSON cumulative_data with the snapshots in database.
     */
    public function syncReportWithSnapshots(MonthlyReport $report): void
    {
        $snapshots = \App\Models\ReportProgressSnapshot::where('report_type', 'monthly')
            ->where('report_id', $report->id)
            ->pluck('actual_weight', 'rab_item_id')
            ->toArray();

        $data = $report->cumulative_data;
        if (!$data || !isset($data['sections'])) return;

        $totals = $this->makeEmptyTotals();
        foreach ($data['sections'] as &$section) {
            $this->syncSectionWithSnapshots($section, $snapshots, $totals);
        }

        $this->calculateDeviationTotals($totals);
        $data['totals'] = $totals;
        
        $report->update(['cumulative_data' => $data]);
    }

    protected function syncSectionWithSnapshots(array &$section, array $snapshots, array &$totals): void
    {
        foreach ($section['items'] as &$item) {
            if (isset($snapshots[$item['id']])) {
                $newCumulative = (float) $snapshots[$item['id']];
                $weight = (float) $item['weight'];
                
                $item['actual']['cumulative'] = round($newCumulative, 4);
                $item['actual']['up_to_prev'] = round($newCumulative - $item['actual']['current'], 4);
                $item['actual']['progress'] = $weight > 0 ? round(($newCumulative / $weight) * 100, 2) : 0;
                
                $item['deviation']['up_to_prev'] = round($item['actual']['up_to_prev'] - $item['planned']['up_to_prev'], 4);
                $item['deviation']['cumulative'] = round($newCumulative - $item['planned']['cumulative'], 4);
            }
            $this->accumulateItemTotals($totals, $item);
        }

        if (isset($section['children'])) {
            foreach ($section['children'] as &$child) {
                $this->syncSectionWithSnapshots($child, $snapshots, $totals);
            }
        }
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

    protected function makeEmptyTotals(): array
    {
        return [
            'weight' => 0, 'planned_prev' => 0, 'planned_current' => 0, 'planned_cumulative' => 0,
            'actual_prev' => 0, 'actual_current' => 0, 'actual_cumulative' => 0,
            'deviation_prev' => 0, 'deviation_current' => 0, 'deviation_cumulative' => 0,
        ];
    }

    protected function calculateDeviationTotals(array &$totals): void
    {
        $totals['deviation_prev'] = $totals['actual_prev'] - $totals['planned_prev'];
        $totals['deviation_current'] = $totals['actual_current'] - $totals['planned_current'];
        $totals['deviation_cumulative'] = $totals['actual_cumulative'] - $totals['planned_cumulative'];
    }

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

    public function copyDataFromPreviousMonth(Project $project, MonthlyReport $report)
    {
        $currentDate = Carbon::createFromDate($report->year, $report->month, 1);
        $prevDate = $currentDate->copy()->subMonth();

        $prevReport = MonthlyReport::where('project_id', $project->id)
            ->where('year', $prevDate->year)
            ->where('month', $prevDate->month)
            ->first();

        if (!$prevReport) {
            throw new \Exception("Laporan bulan sebelumnya tidak ditemukan.");
        }

        $report->update([
            'documentation_ids' => $prevReport->documentation_ids,
            'documentation_uploads' => $prevReport->documentation_uploads,
            'cumulative_data' => $prevReport->cumulative_data,
        ]);

        return $report;
    }

    /**
     * Submit report for review
     */
    public function submitForReview(Project $project, MonthlyReport $report, $user)
    {
        if (!$report->can_submit) {
            throw new \Exception("Laporan tidak dalam status yang dapat diajukan.");
        }

        return DB::transaction(function () use ($project, $report, $user) {
            $report->update([
                'status' => MonthlyReport::STATUS_IN_REVIEW,
                'submitted_by' => $user->id,
                'submitted_at' => now(),
            ]);

            $this->logApprovalAction($report, $user, 'submitted', 'Laporan diajukan untuk review.');

            return $report;
        });
    }

    /**
     * Approve report
     */
    public function approve(Project $project, MonthlyReport $report, $user)
    {
        if (!$report->can_approve) {
            throw new \Exception("Laporan tidak dalam status yang dapat disetujui.");
        }

        return DB::transaction(function () use ($project, $report, $user) {
            $report->update([
                'status' => MonthlyReport::STATUS_APPROVED,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            $this->logApprovalAction($report, $user, 'approved', 'Laporan disetujui.');

            return $report;
        });
    }

    /**
     * Reject report
     */
    public function reject(Project $project, MonthlyReport $report, $user, string $reason)
    {
        if (!$report->can_approve) {
            throw new \Exception("Laporan tidak dalam status yang dapat ditolak.");
        }

        return DB::transaction(function () use ($project, $report, $user, $reason) {
            $report->update([
                'status' => MonthlyReport::STATUS_REJECTED,
                'rejection_reason' => $reason,
            ]);

            $this->logApprovalAction($report, $user, 'rejected', "Laporan ditolak. Alasan: {$reason}");

            return $report;
        });
    }

    /**
     * Publish report
     */
    public function publish(Project $project, MonthlyReport $report, $user)
    {
        if (!$report->can_publish) {
            throw new \Exception("Laporan belum disetujui, tidak dapat dipublish.");
        }

        return DB::transaction(function () use ($project, $report, $user) {
            $report->update([
                'status' => MonthlyReport::STATUS_PUBLISHED,
            ]);

            $this->logApprovalAction($report, $user, 'published', 'Laporan dipublish ke Owner.');

            return $report;
        });
    }

    /**
     * Create approval log
     */
    protected function logApprovalAction(MonthlyReport $report, $user, string $action, string $notes = null)
    {
        $report->approvalLogs()->create([
            'user_id' => $user->id,
            'status' => $action,
            'comment' => $notes,
        ]);
    }
}
