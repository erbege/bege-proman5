<?php

namespace Tests\Feature;

use App\Models\ProgressReport;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProgressReportReviewPageAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviewer_can_access_progress_review_page(): void
    {
        Permission::findOrCreate('progress.approve', 'web');

        $reporter = User::factory()->create();
        $reviewer = User::factory()->create();
        $reviewer->givePermissionTo('progress.approve');

        $project = Project::factory()->create(['created_by' => $reporter->id]);
        $project->team()->attach($reporter->id, ['role' => 'engineer', 'is_active' => true]);
        $project->team()->attach($reviewer->id, ['role' => 'project-manager', 'is_active' => true]);

        $report = ProgressReport::create([
            'project_id' => $project->id,
            'report_date' => now()->toDateString(),
            'progress_percentage' => 10,
            'reported_by' => $reporter->id,
            'status' => ProgressReport::STATUS_SUBMITTED,
            'next_day_plan' => 'Lanjut pekerjaan dinding',
            'safety_details' => ['incidents' => 0, 'near_miss' => 0],
        ]);

        $response = $this->actingAs($reviewer)->get(route('projects.progress.review', [$project, $report]));

        $response->assertStatus(200);
        $response->assertSee('Review Progress Report');
    }

    public function test_user_without_permission_cannot_access_progress_review_page(): void
    {
        Permission::findOrCreate('progress.approve', 'web');

        $reporter = User::factory()->create();
        $memberWithoutPermission = User::factory()->create();

        $project = Project::factory()->create(['created_by' => $reporter->id]);
        $project->team()->attach($reporter->id, ['role' => 'engineer', 'is_active' => true]);
        $project->team()->attach($memberWithoutPermission->id, ['role' => 'engineer', 'is_active' => true]);

        $report = ProgressReport::create([
            'project_id' => $project->id,
            'report_date' => now()->toDateString(),
            'progress_percentage' => 10,
            'reported_by' => $reporter->id,
            'status' => ProgressReport::STATUS_SUBMITTED,
            'next_day_plan' => 'Lanjut pekerjaan dinding',
            'safety_details' => ['incidents' => 0, 'near_miss' => 0],
        ]);

        $response = $this->actingAs($memberWithoutPermission)
            ->get(route('projects.progress.review', [$project, $report]));

        $response->assertStatus(403);
    }
}

