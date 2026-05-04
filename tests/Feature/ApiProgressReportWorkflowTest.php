<?php

namespace Tests\Feature;

use App\Models\ProgressReport;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ApiProgressReportWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_endpoint_moves_report_to_submitted(): void
    {
        $user = User::factory()->create();
        Permission::findOrCreate('progress.manage', 'web');
        Permission::findOrCreate('progress.approve', 'web');
        $user->givePermissionTo('progress.manage');
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['created_by' => $user->id]);
        $report = ProgressReport::create([
            'project_id' => $project->id,
            'report_date' => now()->toDateString(),
            'progress_percentage' => 10,
            'reported_by' => $user->id,
            'status' => ProgressReport::STATUS_DRAFT,
            'next_day_plan' => 'Lanjut pekerjaan pondasi',
            'safety_details' => ['incidents' => 0, 'near_miss' => 0],
        ]);

        $response = $this->postJson("/api/projects/{$project->id}/progress/{$report->id}/submit");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', ProgressReport::STATUS_SUBMITTED);
    }

    public function test_review_endpoint_approve_and_publish(): void
    {
        $reporter = User::factory()->create();
        $reviewer = User::factory()->create();
        $publisher = User::factory()->create();

        Permission::findOrCreate('progress.approve', 'web');
        Permission::findOrCreate('progress.publish', 'web');
        $reviewer->givePermissionTo('progress.approve');
        $publisher->givePermissionTo('progress.publish');

        $project = Project::factory()->create(['created_by' => $reporter->id]);
        $report = ProgressReport::create([
            'project_id' => $project->id,
            'report_date' => now()->toDateString(),
            'progress_percentage' => 12,
            'reported_by' => $reporter->id,
            'status' => ProgressReport::STATUS_SUBMITTED,
            'next_day_plan' => 'Lanjut bekisting',
            'safety_details' => ['incidents' => 0, 'near_miss' => 0],
        ]);

        Sanctum::actingAs($reviewer);
        $reviewResponse = $this->postJson("/api/projects/{$project->id}/progress/{$report->id}/review", [
            'action' => 'approve',
            'notes' => 'valid',
        ]);
        $reviewResponse->assertStatus(200)
            ->assertJsonPath('data.status', ProgressReport::STATUS_REVIEWED);

        Sanctum::actingAs($publisher);
        $publishResponse = $this->postJson("/api/projects/{$project->id}/progress/{$report->id}/publish");
        $publishResponse->assertStatus(200)
            ->assertJsonPath('data.status', ProgressReport::STATUS_PUBLISHED);
    }

    public function test_review_endpoint_reject_blocks_self_review(): void
    {
        $user = User::factory()->create();
        Permission::findOrCreate('progress.approve', 'web');
        $user->givePermissionTo('progress.approve');
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['created_by' => $user->id]);
        $report = ProgressReport::create([
            'project_id' => $project->id,
            'report_date' => now()->toDateString(),
            'progress_percentage' => 8,
            'reported_by' => $user->id,
            'status' => ProgressReport::STATUS_SUBMITTED,
            'next_day_plan' => 'Persiapan area berikutnya',
            'safety_details' => ['incidents' => 0, 'near_miss' => 0],
        ]);

        $response = $this->postJson("/api/projects/{$project->id}/progress/{$report->id}/review", [
            'action' => 'reject',
            'notes' => 'perlu revisi',
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('Pelapor tidak dapat menolak', (string) $response->json('message'));
    }
}

