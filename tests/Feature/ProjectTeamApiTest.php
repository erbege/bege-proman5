<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectTeamApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_project_team()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['created_by' => $user->id]);

        $teamMember = User::factory()->create();
        $project->team()->attach($teamMember->id, ['role' => 'Engineer']);

        // This should fail with 500 if the controller logic is flawed
        $response = $this->getJson("/api/projects/{$project->id}/team");

        $response->assertStatus(200);
    }
}
