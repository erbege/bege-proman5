<?php

namespace Tests\Feature;

use App\Livewire\ProgressReportManager;
use App\Models\ProgressReport;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProgressReportManagerWorkflowLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviewer_can_approve_report_from_review_modal(): void
    {
        Permission::findOrCreate('progress.view', 'web');
        Permission::findOrCreate('progress.approve', 'web');

        $reporter = User::factory()->create();
        $reviewer = User::factory()->create();
        $reviewer->givePermissionTo(['progress.view', 'progress.approve']);

        $project = Project::factory()->create(['created_by' => $reporter->id]);
        $project->team()->attach($reporter->id, ['role' => 'engineer', 'is_active' => true]);
        $project->team()->attach($reviewer->id, ['role' => 'project-manager', 'is_active' => true]);

        $report = ProgressReport::create([
            'project_id' => $project->id,
            'report_date' => now()->toDateString(),
            'progress_percentage' => 10,
            'reported_by' => $reporter->id,
            'status' => ProgressReport::STATUS_SUBMITTED,
            'next_day_plan' => 'Lanjut pekerjaan beton',
            'safety_details' => ['incidents' => 0, 'near_miss' => 0],
        ]);

        $this->actingAs($reviewer);

        Livewire::test(ProgressReportManager::class, ['project' => $project])
            ->call('openReviewModal', $report->id, 'approve')
            ->set('reviewNotes', 'OK lanjut')
            ->call('processReview')
            ->assertSet('showReviewModal', false);

        $this->assertDatabaseHas('progress_reports', [
            'id' => $report->id,
            'status' => ProgressReport::STATUS_REVIEWED,
            'reviewed_by' => $reviewer->id,
        ]);
    }

    public function test_reviewer_can_reject_report_from_review_modal(): void
    {
        Permission::findOrCreate('progress.view', 'web');
        Permission::findOrCreate('progress.approve', 'web');

        $reporter = User::factory()->create();
        $reviewer = User::factory()->create();
        $reviewer->givePermissionTo(['progress.view', 'progress.approve']);

        $project = Project::factory()->create(['created_by' => $reporter->id]);
        $project->team()->attach($reporter->id, ['role' => 'engineer', 'is_active' => true]);
        $project->team()->attach($reviewer->id, ['role' => 'project-manager', 'is_active' => true]);

        $report = ProgressReport::create([
            'project_id' => $project->id,
            'report_date' => now()->toDateString(),
            'progress_percentage' => 12,
            'reported_by' => $reporter->id,
            'status' => ProgressReport::STATUS_SUBMITTED,
            'next_day_plan' => 'Lanjut pekerjaan pasangan batu',
            'safety_details' => ['incidents' => 0, 'near_miss' => 1],
        ]);

        $this->actingAs($reviewer);

        Livewire::test(ProgressReportManager::class, ['project' => $project])
            ->call('openReviewModal', $report->id, 'reject')
            ->set('reviewNotes', 'Perlu revisi volume')
            ->call('processReview')
            ->assertSet('showReviewModal', false);

        $this->assertDatabaseHas('progress_reports', [
            'id' => $report->id,
            'status' => ProgressReport::STATUS_REJECTED,
            'rejected_by' => $reviewer->id,
        ]);
    }

    public function test_manager_can_publish_reviewed_report_from_component_action(): void
    {
        Permission::findOrCreate('progress.view', 'web');
        Permission::findOrCreate('progress.publish', 'web');

        $reporter = User::factory()->create();
        $reviewer = User::factory()->create();
        $publisher = User::factory()->create();
        $publisher->givePermissionTo(['progress.view', 'progress.publish']);

        $project = Project::factory()->create(['created_by' => $reporter->id]);
        $project->team()->attach($reporter->id, ['role' => 'engineer', 'is_active' => true]);
        $project->team()->attach($reviewer->id, ['role' => 'project-manager', 'is_active' => true]);
        $project->team()->attach($publisher->id, ['role' => 'project-manager', 'is_active' => true]);

        $report = ProgressReport::create([
            'project_id' => $project->id,
            'report_date' => now()->toDateString(),
            'progress_percentage' => 20,
            'reported_by' => $reporter->id,
            'status' => ProgressReport::STATUS_REVIEWED,
            'reviewed_by' => $reviewer->id,
            'next_day_plan' => 'Lanjut pekerjaan struktur',
            'safety_details' => ['incidents' => 0, 'near_miss' => 0],
        ]);

        $this->actingAs($publisher);

        Livewire::test(ProgressReportManager::class, ['project' => $project])
            ->call('publishReport', $report->id);

        $this->assertDatabaseHas('progress_reports', [
            'id' => $report->id,
            'status' => ProgressReport::STATUS_PUBLISHED,
            'published_by' => $publisher->id,
        ]);
    }
}
