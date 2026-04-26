<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectSchedule;
use App\Models\RabItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScheduleCalculator
{
    /**
     * Generate weekly schedule data for a project
     */
    public function generateSchedule(Project $project): void
    {
        DB::transaction(function () use ($project) {
            // Clear existing schedule
            $project->schedules()->delete();

            // Get project start
            $startDate = $project->start_date;
            $endDate = $project->end_date;

            // Determine total weeks (incorporating actual progress dates if later than end date)
            $lastReportDate = $project->progressReports()->max('report_date');
            $effectiveEndDate = $endDate;

            if ($lastReportDate) {
                $lastReportCarbon = Carbon::parse($lastReportDate);
                if ($lastReportCarbon->gt($endDate)) {
                    $effectiveEndDate = $lastReportCarbon;
                }
            }

            $totalWeeks = (int) ceil($startDate->diffInDays($effectiveEndDate) / 7);

            if ($totalWeeks <= 0) {
                return;
            }

            // Get all RAB items with schedule
            $items = $project->rabItems()
                ->whereNotNull('planned_start')
                ->whereNotNull('planned_end')
                ->get();

            // Initialize weekly data (project-relative, not calendar-based)
            $weeklyData = [];
            for ($week = 1; $week <= $totalWeeks; $week++) {
                // Week starts from project start date, adding 7 days per week
                $weekStart = $startDate->copy()->addDays(($week - 1) * 7);
                $weekEnd = $weekStart->copy()->addDays(6);

                $weeklyData[$week] = [
                    'week_number' => $week,
                    'week_start' => $weekStart,
                    'week_end' => $weekEnd,
                    'planned_weight' => 0,
                    'actual_weight' => 0,
                ];
            }

            // 1. Calculate PLANNED Weight Distribution
            foreach ($items as $item) {
                $this->distributePlannedWeight($item, $weeklyData, $startDate, $totalWeeks);
            }

            // 2. Calculate ACTUAL Weight Distribution (Based on Reports)
            $reports = $project->progressReports()->with('rabItem')->get();

            foreach ($reports as $report) {
                if (!$report->rabItem)
                    continue;

                // Calculate which week this report belongs to
                $reportDate = Carbon::parse($report->report_date);
                // Difference in days from start / 7 => week index (0-based)
                // But we use 1-based index
                // Week 1: Days 0-6. Week 2: 7-13.
                // Using diffInDays relative to Project Start.
                // Note: If report is BEFORE project start, clamp to Week 1 or ignore?
                // Assume valid dates.

                $daysDiff = $startDate->diffInDays($reportDate, false); // false = allows negative

                if ($daysDiff < 0) {
                    $weekIndex = 1; // Fallback to week 1
                } else {
                    $weekIndex = (int) floor($daysDiff / 7) + 1;
                }

                if (isset($weeklyData[$weekIndex])) {
                    // Weight Gain = (Delta Percentage / 100) * Item Weight
                    // progress_percentage is the Delta
                    $weightGain = ($report->progress_percentage / 100) * $report->rabItem->weight_percentage;
                    $weeklyData[$weekIndex]['actual_weight'] += $weightGain;
                }
            }

            // Calculate cumulative values and prepare bulk insert data
            $plannedCumulative = 0;
            $actualCumulative = 0;
            $scheduleData = [];
            $now = now();

            foreach ($weeklyData as $week => $data) {
                $plannedCumulative += $data['planned_weight'];
                $actualCumulative += $data['actual_weight'];

                $scheduleData[] = [
                    'project_id' => $project->id,
                    'week_number' => $data['week_number'],
                    'week_start' => $data['week_start']->format('Y-m-d'),
                    'week_end' => $data['week_end']->format('Y-m-d'),
                    'planned_weight' => $data['planned_weight'],
                    'actual_weight' => $data['actual_weight'],
                    'planned_cumulative' => $plannedCumulative,
                    'actual_cumulative' => $actualCumulative,
                    'deviation' => $actualCumulative - $plannedCumulative,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Bulk insert all schedules at once (much faster than N individual inserts)
            if (!empty($scheduleData)) {
                ProjectSchedule::insert($scheduleData);
            }
        });
    }

    /**
     * Distribute item weight across its planned weeks
     */
    protected function distributePlannedWeight($item, array &$weeklyData, Carbon $projectStart, int $totalWeeks): void
    {
        $itemStart = $item->planned_start;
        $itemEnd = $item->planned_end;
        $weight = $item->weight_percentage;
        $originalEndDate = Carbon::parse($projectStart)->addWeeks($totalWeeks); // Approximation for boundary

        // Calculate which weeks this item spans
        $startWeek = max(1, (int) floor($projectStart->diffInDays($itemStart) / 7) + 1);
        $endWeek = (int) floor($projectStart->diffInDays($itemEnd) / 7) + 1;

        // Safety cap
        if ($endWeek > $totalWeeks)
            $endWeek = $totalWeeks;
        if ($startWeek > $endWeek)
            $startWeek = $endWeek;

        $weeksCount = max(1, $endWeek - $startWeek + 1);
        $weightPerWeek = $weight / $weeksCount;

        // Distribute within planned range
        for ($week = $startWeek; $week <= $endWeek; $week++) {
            if (isset($weeklyData[$week])) {
                $weeklyData[$week]['planned_weight'] += $weightPerWeek;
            }
        }
    }

    /**
     * Get S-Curve data for charting
     */
    public function getScurveData(Project $project): array
    {
        $schedules = $project->schedules()->orderBy('week_number')->get();

        $labels = [];
        $planned = [];
        $actual = [];

        foreach ($schedules as $schedule) {
            $labels[] = 'M' . $schedule->week_number;
            $planned[] = round($schedule->planned_cumulative, 2);
            $actual[] = round($schedule->actual_cumulative, 2);
        }

        return [
            'labels' => $labels,
            'planned' => $planned,
            'actual' => $actual,
        ];
    }

    /**
     * Update schedule when progress is reported
     */
    public function updateFromProgress(Project $project): void
    {
        // Recalculate actual weights based on current progress
        $this->generateSchedule($project);
    }

    /**
     * Generate automatic schedule based on RAB weights
     * Supports parallel scheduling for items marked with can_parallel = true
     * 
     * @param Project $project
     * @return array Contains 'items' with calculated schedules and 'summary' with duration info
     */
    public function autoSchedule(Project $project): array
    {
        $startDate = $project->start_date;
        $endDate = $project->end_date;
        $projectDays = $startDate->diffInDays($endDate);

        // Get RAB items ordered by section and sort_order
        $items = $project->rabItems()
            ->with('section')
            ->orderBy('rab_section_id')
            ->orderBy('sort_order')
            ->get();

        if ($items->isEmpty()) {
            return [
                'items' => [],
                'summary' => [
                    'total_items' => 0,
                    'calculated_end_date' => $endDate,
                    'project_end_date' => $endDate,
                    'days_difference' => 0,
                    'has_mismatch' => false,
                ],
            ];
        }

        // Calculate schedules with overlap and parallel support
        $scheduledItems = [];
        $currentDate = $startDate->copy();
        $previousItemStart = $startDate->copy();
        $previousItemEnd = $startDate->copy();
        $overlapPercent = 0.25; // 25% overlap between sequential items

        foreach ($items as $index => $item) {
            // Estimate duration based on weight (minimum 7 days = 1 week)
            $duration = $this->estimateItemDuration($item, $projectDays);

            // Determine start date based on parallel flag
            if ($item->can_parallel && $index > 0) {
                // Parallel item: start at the same time as previous item
                $itemStart = $previousItemStart->copy();
            } else {
                // Sequential item: start after overlap period of previous
                $itemStart = $currentDate->copy();
            }

            $itemEnd = $itemStart->copy()->addDays($duration - 1);

            $scheduledItems[] = [
                'id' => $item->id,
                'code' => $item->code,
                'work_name' => $item->work_name,
                'section_name' => $item->section?->name,
                'weight_percentage' => $item->weight_percentage,
                'can_parallel' => $item->can_parallel,
                'planned_start' => $itemStart->format('Y-m-d'),
                'planned_end' => $itemEnd->format('Y-m-d'),
                'duration_days' => $duration,
            ];

            // Update tracking for next iteration
            if (!$item->can_parallel || $index === 0) {
                // For sequential items, update the current date with overlap
                $overlapDays = (int) floor($duration * (1 - $overlapPercent));
                $currentDate = $itemStart->copy()->addDays(max(1, $overlapDays));
                $previousItemStart = $itemStart->copy();
                $previousItemEnd = $itemEnd->copy();
            } else {
                // For parallel items, extend current date if this item ends later
                if ($itemEnd->gt($previousItemEnd)) {
                    $previousItemEnd = $itemEnd->copy();
                    $overlapDays = (int) floor($duration * (1 - $overlapPercent));
                    $currentDate = $itemStart->copy()->addDays(max(1, $overlapDays));
                }
            }
        }

        // Calculate the actual end date from scheduled items (find the latest end date)
        $calculatedEndDate = Carbon::parse($scheduledItems[0]['planned_end']);
        foreach ($scheduledItems as $scheduled) {
            $itemEndDate = Carbon::parse($scheduled['planned_end']);
            if ($itemEndDate->gt($calculatedEndDate)) {
                $calculatedEndDate = $itemEndDate;
            }
        }

        $daysDifference = $endDate->diffInDays($calculatedEndDate, false);

        return [
            'items' => $scheduledItems,
            'summary' => [
                'total_items' => count($scheduledItems),
                'calculated_end_date' => $calculatedEndDate->format('Y-m-d'),
                'project_end_date' => $endDate->format('Y-m-d'),
                'days_difference' => $daysDifference,
                'has_mismatch' => abs($daysDifference) > 7, // Only flag if > 1 week difference
            ],
        ];

    }

    /**
     * Estimate item duration based on its weight percentage
     */
    protected function estimateItemDuration(RabItem $item, int $projectDays): int
    {
        $weight = $item->weight_percentage;

        // Duration proportional to weight, minimum 7 days (1 week)
        $estimatedDays = (int) ceil(($weight / 100) * $projectDays);

        return max(7, $estimatedDays);
    }

    /**
     * Apply auto-scheduled dates to RAB items
     */
    public function applyAutoSchedule(Project $project, string $mode = 'keep'): array
    {
        $schedule = $this->autoSchedule($project);

        // If mode is 'compress' and there's a mismatch, recalculate with compression
        if ($mode === 'compress' && $schedule['summary']['has_mismatch'] && $schedule['summary']['days_difference'] > 0) {
            $schedule = $this->compressSchedule($project);
        }

        DB::transaction(function () use ($project, $schedule, $mode) {
            // Batch update RAB items (update existing records only)
            // Using individual updates within a transaction is still fast and avoids upsert issues
            foreach ($schedule['items'] as $itemData) {
                RabItem::where('id', $itemData['id'])->update([
                    'planned_start' => $itemData['planned_start'],
                    'planned_end' => $itemData['planned_end'],
                ]);
            }

            // Update project end date if mode is 'extend'
            if ($mode === 'extend' && $schedule['summary']['has_mismatch']) {
                $project->update([
                    'end_date' => $schedule['summary']['calculated_end_date'],
                ]);
            }
        });

        // Regenerate the project schedule (already optimized with bulk insert)
        $this->generateSchedule($project);

        return $schedule;
    }

    /**
     * Compress schedule to fit within project end date
     */
    public function compressSchedule(Project $project): array
    {
        $startDate = $project->start_date;
        $endDate = $project->end_date;
        $projectDays = $startDate->diffInDays($endDate);

        // Get RAB items ordered by section and sort_order
        $items = $project->rabItems()
            ->with('section')
            ->orderBy('rab_section_id')
            ->orderBy('sort_order')
            ->get();

        if ($items->isEmpty()) {
            return [
                'items' => [],
                'summary' => [
                    'total_items' => 0,
                    'calculated_end_date' => $endDate->format('Y-m-d'),
                    'project_end_date' => $endDate->format('Y-m-d'),
                    'days_difference' => 0,
                    'has_mismatch' => false,
                ],
            ];
        }

        // Calculate total weight-based duration
        $totalWeightedDays = 0;
        foreach ($items as $item) {
            $totalWeightedDays += $this->estimateItemDuration($item, $projectDays);
        }

        // Calculate compression ratio
        $overlapPercent = 0.25;
        $effectiveDays = $projectDays * (1 + $overlapPercent); // Account for overlap
        $compressionRatio = min(1, $effectiveDays / max(1, $totalWeightedDays));

        // Apply compressed durations
        $scheduledItems = [];
        $currentDate = $startDate->copy();

        foreach ($items as $item) {
            // Compressed duration (minimum 3 days)
            $baseDuration = $this->estimateItemDuration($item, $projectDays);
            $duration = max(3, (int) ceil($baseDuration * $compressionRatio));

            $itemStart = $currentDate->copy();
            $itemEnd = $itemStart->copy()->addDays($duration - 1);

            // Ensure we don't exceed project end date
            if ($itemEnd->gt($endDate)) {
                $itemEnd = $endDate->copy();
            }

            $scheduledItems[] = [
                'id' => $item->id,
                'code' => $item->code,
                'work_name' => $item->work_name,
                'section_name' => $item->section?->name,
                'weight_percentage' => $item->weight_percentage,
                'planned_start' => $itemStart->format('Y-m-d'),
                'planned_end' => $itemEnd->format('Y-m-d'),
                'duration_days' => $itemStart->diffInDays($itemEnd) + 1,
            ];

            // More aggressive overlap for compression
            $overlapDays = (int) floor($duration * 0.5); // 50% overlap when compressed
            $currentDate = $itemStart->copy()->addDays(max(1, $overlapDays));

            // Don't start after end date
            if ($currentDate->gt($endDate)) {
                $currentDate = $endDate->copy()->subDays(2);
            }
        }

        $calculatedEndDate = Carbon::parse(end($scheduledItems)['planned_end']);

        return [
            'items' => $scheduledItems,
            'summary' => [
                'total_items' => count($scheduledItems),
                'calculated_end_date' => $calculatedEndDate->format('Y-m-d'),
                'project_end_date' => $endDate->format('Y-m-d'),
                'days_difference' => $endDate->diffInDays($calculatedEndDate, false),
                'has_mismatch' => false,
                'compressed' => true,
            ],
        ];
    }
}
