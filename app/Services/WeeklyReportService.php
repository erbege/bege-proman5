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
    public function generateCumulativeData(Project $project, Carbon $periodEnd, int $weekNumber): array
    {
        $periodStart = $this->calculatePeriod($project, $weekNumber)['start'];
        $prevPeriodEnd = $periodStart->copy()->subDay();

        // Carry over from previous weekly report if exists
        $prevCumulatives = [];
        if ($weekNumber > 1) {
            $prevReport = WeeklyReport::where('project_id', $project->id)
                ->where('week_number', $weekNumber - 1)
                ->first();

            if ($prevReport && isset($prevReport->cumulative_data['sections'])) {
                $this->collectItemCumulatives($prevReport->cumulative_data['sections'], $prevCumulatives);
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
            'totals' => [
                'weight' => 0,
                'planned_prev' => 0,
                'planned_current' => 0,
                'planned_cumulative' => 0,
                'actual_prev' => 0,
                'actual_current' => 0,
                'actual_cumulative' => 0,
                'deviation_prev' => 0,
                'deviation_current' => 0,
                'deviation_cumulative' => 0,
            ],
        ];

        foreach ($sections as $section) {
            $sectionData = $this->processSectionForCumulative($section, $project, $periodStart, $periodEnd, $prevPeriodEnd, $prevCumulatives);
            if (!empty($sectionData['items']) || !empty($sectionData['children'])) {
                $result['sections'][] = $sectionData;
                $this->accumulateTotals($result['totals'], $sectionData);
            }
        }

        // Calculate deviations for totals
        $result['totals']['deviation_prev'] = $result['totals']['actual_prev'] - $result['totals']['planned_prev'];
        $result['totals']['deviation_current'] = $result['totals']['actual_current'] - $result['totals']['planned_current'];
        $result['totals']['deviation_cumulative'] = $result['totals']['actual_cumulative'] - $result['totals']['planned_cumulative'];

        return $result;
    }

    /**
     * Process a section recursively for cumulative data
     */
    protected function processSectionForCumulative(RabSection $section, Project $project, Carbon $periodStart, Carbon $periodEnd, Carbon $prevPeriodEnd, array $prevCumulatives): array
    {
        $sectionData = [
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
        }

        // Process child sections recursively
        if ($section->children && $section->children->count() > 0) {
            foreach ($section->children as $child) {
                $childData = $this->processSectionForCumulative($child, $project, $periodStart, $periodEnd, $prevPeriodEnd, $prevCumulatives);
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

        // Calculate actual progress
        // Priority: use value from previous weekly report if available, else fallback to ProgressReport table
        if (isset($prevCumulatives[$item->full_code])) {
            $actualPrev = (float) $prevCumulatives[$item->full_code];
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
            $cumulativeData = $this->generateCumulativeData($project, $period['end'], $weekNumber);
            $detailData = $this->generateDetailData($project, $period['start'], $period['end']);

            $existing->update([
                'cumulative_data' => $cumulativeData,
                'detail_data' => $detailData,
            ]);

            return $existing;
        }

        // Generate new report
        $cumulativeData = $this->generateCumulativeData($project, $period['end'], $weekNumber);
        $detailData = $this->generateDetailData($project, $period['start'], $period['end']);

        return WeeklyReport::create([
            'project_id' => $project->id,
            'week_number' => $weekNumber,
            'period_start' => $period['start'],
            'period_end' => $period['end'],
            'cover_title' => "Weekly Progress Report - Week {$weekNumber}",
            'cumulative_data' => $cumulativeData,
            'detail_data' => $detailData,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);
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
     * Update cumulative data actual progress for a report and cascade to future weeks
     */
    public function updateCumulativeActuals(WeeklyReport $report, array $itemUpdates): int
    {
        $data = $report->cumulative_data;
        if (!$data || !isset($data['sections'])) {
            return 0;
        }

        $totals = [
            'weight' => 0, 'planned_prev' => 0, 'planned_current' => 0, 'planned_cumulative' => 0,
            'actual_prev' => 0, 'actual_current' => 0, 'actual_cumulative' => 0,
            'deviation_prev' => 0, 'deviation_current' => 0, 'deviation_cumulative' => 0,
        ];

        foreach ($data['sections'] as &$section) {
            $this->updateSectionItems($section, $itemUpdates, $totals);
        }

        $totals['deviation_prev'] = $totals['actual_prev'] - $totals['planned_prev'];
        $totals['deviation_current'] = $totals['actual_current'] - $totals['planned_current'];
        $totals['deviation_cumulative'] = $totals['actual_cumulative'] - $totals['planned_cumulative'];

        $data['totals'] = $totals;
        $report->update(['cumulative_data' => $data]);

        // Cascade update to subsequent weeks
        return $this->cascadeToSubsequentWeeks($report->project, $report, $data);
    }

    protected function cascadeToSubsequentWeeks(Project $project, WeeklyReport $currentReport, array $currentData): int
    {
        $subsequentReports = WeeklyReport::where('project_id', $project->id)
            ->where('week_number', '>', $currentReport->week_number)
            ->orderBy('week_number')
            ->get();

        if ($subsequentReports->isEmpty()) return 0;

        $prevCumulatives = [];
        $this->collectItemCumulatives($currentData['sections'], $prevCumulatives);

        foreach ($subsequentReports as $nextReport) {
            $nextData = $nextReport->cumulative_data;
            if (!$nextData || !isset($nextData['sections'])) continue;

            $nextTotals = [
                'weight' => 0, 'planned_prev' => 0, 'planned_current' => 0, 'planned_cumulative' => 0,
                'actual_prev' => 0, 'actual_current' => 0, 'actual_cumulative' => 0,
                'deviation_prev' => 0, 'deviation_current' => 0, 'deviation_cumulative' => 0,
            ];

            foreach ($nextData['sections'] as &$section) {
                $this->cascadeSectionItems($section, $prevCumulatives, $nextTotals);
            }

            $nextTotals['deviation_prev'] = $nextTotals['actual_prev'] - $nextTotals['planned_prev'];
            $nextTotals['deviation_current'] = $nextTotals['actual_current'] - $nextTotals['planned_current'];
            $nextTotals['deviation_cumulative'] = $nextTotals['actual_cumulative'] - $nextTotals['planned_cumulative'];

            $nextData['totals'] = $nextTotals;
            $nextReport->update(['cumulative_data' => $nextData]);

            $prevCumulatives = [];
            $this->collectItemCumulatives($nextData['sections'], $prevCumulatives);
        }

        return $subsequentReports->count();
    }

    protected function cascadeSectionItems(array &$section, array $prevCumulatives, array &$totals): void
    {
        foreach ($section['items'] as &$item) {
            $code = $item['code'];

            if (isset($prevCumulatives[$code])) {
                $newUpToPrev = round((float) $prevCumulatives[$code], 4);
                $item['actual']['up_to_prev'] = $newUpToPrev;
                $item['actual']['cumulative'] = round($newUpToPrev + $item['actual']['current'], 4);
                $item['deviation']['up_to_prev'] = round($newUpToPrev - $item['planned']['up_to_prev'], 4);
                $item['deviation']['current'] = round($item['actual']['current'] - $item['planned']['current'], 4);
                $item['deviation']['cumulative'] = round($item['actual']['cumulative'] - $item['planned']['cumulative'], 4);
            }

            $totals['weight'] += $item['weight'] ?? 0;
            $totals['planned_prev'] += $item['planned']['up_to_prev'] ?? 0;
            $totals['planned_current'] += $item['planned']['current'] ?? 0;
            $totals['planned_cumulative'] += $item['planned']['cumulative'] ?? 0;
            $totals['actual_prev'] += $item['actual']['up_to_prev'] ?? 0;
            $totals['actual_current'] += $item['actual']['current'] ?? 0;
            $totals['actual_cumulative'] += $item['actual']['cumulative'] ?? 0;
        }

        if (isset($section['children'])) {
            foreach ($section['children'] as &$child) {
                $this->cascadeSectionItems($child, $prevCumulatives, $totals);
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

            $totals['weight'] += $item['weight'] ?? 0;
            $totals['planned_prev'] += $item['planned']['up_to_prev'] ?? 0;
            $totals['planned_current'] += $item['planned']['current'] ?? 0;
            $totals['planned_cumulative'] += $item['planned']['cumulative'] ?? 0;
            $totals['actual_prev'] += $item['actual']['up_to_prev'] ?? 0;
            $totals['actual_current'] += $item['actual']['current'] ?? 0;
            $totals['actual_cumulative'] += $item['actual']['cumulative'] ?? 0;
        }

        if (isset($section['children'])) {
            foreach ($section['children'] as &$child) {
                $this->updateSectionItems($child, $itemUpdates, $totals);
            }
        }
    }
}

