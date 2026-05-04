<?php

namespace Tests\Feature;

use App\Models\ProgressReport;
use App\Models\Project;
use App\Models\RabItem;
use App\Models\RabSection;
use App\Models\User;
use App\Services\ProgressReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProgressReportWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_requires_next_day_plan_and_minimum_safety_data(): void
    {
        $reporter = User::factory()->create();
        $project = Project::factory()->create(['created_by' => $reporter->id]);

        $report = ProgressReport::create([
            'project_id' => $project->id,
            'report_date' => now()->toDateString(),
            'progress_percentage' => 10,
            'reported_by' => $reporter->id,
            'status' => ProgressReport::STATUS_DRAFT,
        ]);

        $service = app(ProgressReportService::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Rencana kerja esok hari wajib diisi');
        $service->submit($report);
    }

    public function test_submit_changes_status_to_submitted_when_compliance_is_valid(): void
    {
        Permission::findOrCreate('progress.approve', 'web');

        $reporter = User::factory()->create();
        $project = Project::factory()->create(['created_by' => $reporter->id]);

        $report = ProgressReport::create([
            'project_id' => $project->id,
            'report_date' => now()->toDateString(),
            'progress_percentage' => 10,
            'reported_by' => $reporter->id,
            'status' => ProgressReport::STATUS_DRAFT,
            'next_day_plan' => 'Lanjut pengecoran zona A',
            'safety_details' => ['incidents' => 0, 'near_miss' => 0, 'apd_compliance' => true],
            'equipment_details' => [['name' => 'Excavator', 'qty' => 1]],
        ]);

        $updated = app(ProgressReportService::class)->submit($report);

        $this->assertSame(ProgressReport::STATUS_SUBMITTED, $updated->fresh()->status);
    }

    public function test_self_approval_is_blocked(): void
    {
        $reporter = User::factory()->create();
        $project = Project::factory()->create(['created_by' => $reporter->id]);

        $report = ProgressReport::create([
            'project_id' => $project->id,
            'report_date' => now()->toDateString(),
            'progress_percentage' => 10,
            'reported_by' => $reporter->id,
            'status' => ProgressReport::STATUS_SUBMITTED,
            'next_day_plan' => 'Lanjut pengecoran zona A',
            'safety_details' => ['incidents' => 0, 'near_miss' => 0],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Pelapor tidak dapat menyetujui laporan miliknya sendiri.');

        app(ProgressReportService::class)->approve($report, $reporter->id, 'ok');
    }

    public function test_review_and_publish_happy_path(): void
    {
        $reporter = User::factory()->create();
        $reviewer = User::factory()->create();
        $publisher = User::factory()->create();
        $project = Project::factory()->create(['created_by' => $reporter->id]);

        $report = ProgressReport::create([
            'project_id' => $project->id,
            'report_date' => now()->toDateString(),
            'progress_percentage' => 15,
            'reported_by' => $reporter->id,
            'status' => ProgressReport::STATUS_SUBMITTED,
            'next_day_plan' => 'Persiapan bekisting lanjutan',
            'safety_details' => ['incidents' => 0, 'near_miss' => 1],
        ]);

        $service = app(ProgressReportService::class);
        $service->approve($report, $reviewer->id, 'siap publish');

        $report->refresh();
        $this->assertSame(ProgressReport::STATUS_REVIEWED, $report->status);
        $this->assertSame($reviewer->id, $report->reviewed_by);

        $service->publish($report, $publisher->id);
        $report->refresh();

        $this->assertSame(ProgressReport::STATUS_PUBLISHED, $report->status);
        $this->assertSame($publisher->id, $report->published_by);
    }

    public function test_create_with_rab_item_updates_cumulative_progress_and_rab_actual_progress(): void
    {
        $reporter = User::factory()->create();
        $project = Project::factory()->create(['created_by' => $reporter->id]);
        $section = RabSection::create(['project_id' => $project->id, 'code' => 'A', 'name' => 'Section A']);
        $rabItem = RabItem::create([
            'project_id' => $project->id,
            'rab_section_id' => $section->id,
            'work_name' => 'Pekerjaan Pondasi',
            'unit' => 'm3',
            'weight_percentage' => 30,
            'actual_progress' => 20,
        ]);

        ProgressReport::create([
            'project_id' => $project->id,
            'rab_item_id' => $rabItem->id,
            'report_date' => now()->subDay()->toDateString(),
            'progress_percentage' => 20,
            'cumulative_progress' => 20,
            'reported_by' => $reporter->id,
            'status' => ProgressReport::STATUS_SUBMITTED,
            'next_day_plan' => 'Lanjut beton',
            'safety_details' => ['incidents' => 0, 'near_miss' => 0],
        ]);

        $service = app(ProgressReportService::class);
        $newReport = $service->create($project, [
            'rab_item_id' => $rabItem->id,
            'report_date' => now()->toDateString(),
            'progress_percentage' => 10,
            'description' => 'Lanjut pekerjaan pondasi',
        ], [], $reporter->id);

        $this->assertSame(30.0, (float) $newReport->cumulative_progress);
        $this->assertSame(30.0, (float) $rabItem->fresh()->actual_progress);
    }

    public function test_delete_recalculates_rab_item_progress(): void
    {
        $reporter = User::factory()->create();
        $project = Project::factory()->create(['created_by' => $reporter->id]);
        $section = RabSection::create(['project_id' => $project->id, 'code' => 'A', 'name' => 'Section A']);
        $rabItem = RabItem::create([
            'project_id' => $project->id,
            'rab_section_id' => $section->id,
            'work_name' => 'Pekerjaan Pondasi',
            'unit' => 'm3',
            'weight_percentage' => 30,
            'actual_progress' => 20,
        ]);

        $firstReport = ProgressReport::create([
            'project_id' => $project->id,
            'rab_item_id' => $rabItem->id,
            'report_date' => now()->subDays(2)->toDateString(),
            'progress_percentage' => 20,
            'cumulative_progress' => 20,
            'reported_by' => $reporter->id,
            'status' => ProgressReport::STATUS_SUBMITTED,
            'next_day_plan' => 'Persiapan kerja',
            'safety_details' => ['incidents' => 0, 'near_miss' => 0],
        ]);

        $secondReport = ProgressReport::create([
            'project_id' => $project->id,
            'rab_item_id' => $rabItem->id,
            'report_date' => now()->subDay()->toDateString(),
            'progress_percentage' => 10,
            'cumulative_progress' => 30,
            'reported_by' => $reporter->id,
            'status' => ProgressReport::STATUS_DRAFT,
            'next_day_plan' => 'Lanjut pekerjaan',
            'safety_details' => ['incidents' => 0, 'near_miss' => 0],
        ]);

        $service = app(ProgressReportService::class);
        $service->delete($secondReport, $project);

        $this->assertSame(20.0, (float) $rabItem->fresh()->actual_progress);
        $this->assertDatabaseMissing('progress_reports', ['id' => $secondReport->id]);
    }
}
