<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WeeklyReport;
use App\Models\MonthlyReport;
use App\Models\ReportProgressSnapshot;
use App\Models\RabItem;
use Illuminate\Support\Facades\DB;

class MigrateReportSnapshots extends Command
{
    protected $signature = 'reports:migrate-snapshots {--type=all : weekly, monthly, or all}';
    protected $description = 'Migrate historical report JSON data to relational snapshot table';

    public function handle()
    {
        $type = $this->option('type');

        if ($type === 'all' || $type === 'weekly') {
            $this->migrateWeekly();
        }

        if ($type === 'all' || $type === 'monthly') {
            $this->migrateMonthly();
        }

        $this->info('Migration completed!');
    }

    protected function migrateWeekly()
    {
        $reports = WeeklyReport::all();
        $this->info("Migrating {$reports->count()} weekly reports...");

        foreach ($reports as $report) {
            $this->processReport($report, 'weekly');
        }
    }

    protected function migrateMonthly()
    {
        $reports = MonthlyReport::all();
        $this->info("Migrating {$reports->count()} monthly reports...");

        foreach ($reports as $report) {
            $this->processReport($report, 'monthly');
        }
    }

    protected function processReport($report, $type)
    {
        $data = $report->cumulative_data;
        if (!$data || !isset($data['sections'])) {
            $this->warn("Skipping {$type} report #{$report->id}: No cumulative data.");
            return;
        }

        $snapshots = [];
        $this->extractSnapshots($data['sections'], $snapshots);

        if (empty($snapshots)) return;

        DB::transaction(function () use ($report, $type, $snapshots) {
            ReportProgressSnapshot::where('report_type', $type)
                ->where('report_id', $report->id)
                ->delete();

            $insertData = [];
            foreach ($snapshots as $code => $values) {
                $item = RabItem::where('project_id', $report->project_id)
                    ->where(DB::raw("REPLACE(code, ' ', '')"), str_replace(' ', '', $code))
                    ->first();

                if ($item) {
                    $insertData[] = [
                        'report_type' => $type,
                        'report_id' => $report->id,
                        'rab_item_id' => $item->id,
                        'planned_weight' => $values['planned'],
                        'actual_weight' => $values['actual'],
                        'deviation' => $values['actual'] - $values['planned'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($insertData)) {
                ReportProgressSnapshot::insert($insertData);
            }
        });

        $this->line("Migrated {$type} report #{$report->id}");
    }

    protected function extractSnapshots(array $sections, array &$snapshots)
    {
        foreach ($sections as $section) {
            foreach ($section['items'] ?? [] as $item) {
                $snapshots[$item['code']] = [
                    'planned' => (float) ($item['planned']['cumulative'] ?? 0),
                    'actual' => (float) ($item['actual']['cumulative'] ?? 0),
                ];
            }
            if (isset($section['children'])) {
                $this->extractSnapshots($section['children'], $snapshots);
            }
        }
    }
}
