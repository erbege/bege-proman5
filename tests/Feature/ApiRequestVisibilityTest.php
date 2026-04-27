<?php

namespace Tests\Feature;

use App\Models\MaterialRequest;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiRequestVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_member_cannot_view_other_project_material_request(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        Sanctum::actingAs($viewer);

        $project = Project::factory()->create(['created_by' => $owner->id]);
        $request = MaterialRequest::create([
            'project_id' => $project->id,
            'requested_by' => $owner->id,
            'request_date' => now(),
            'status' => 'pending',
        ]);

        $response = $this->getJson("/api/material-requests/{$request->id}");

        $response->assertStatus(403)
            ->assertJsonPath('error', 'Unauthorized');
    }

    public function test_project_member_can_view_project_purchase_request(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        Sanctum::actingAs($member);

        $project = Project::factory()->create(['created_by' => $owner->id]);
        $project->team()->attach($member->id, ['role' => 'engineer', 'is_active' => true]);

        $request = PurchaseRequest::create([
            'project_id' => $project->id,
            'requested_by' => $owner->id,
            'status' => 'pending',
            'request_date' => now(),
            'required_date' => now()->addDays(7),
        ]);

        $response = $this->getJson("/api/purchase-requests/{$request->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $request->id);
    }

    public function test_superadmin_can_view_any_material_request(): void
    {
        Role::findOrCreate('Superadmin', 'web');

        $owner = User::factory()->create();
        $superadmin = User::factory()->create();
        $superadmin->assignRole('Superadmin');
        Sanctum::actingAs($superadmin);

        $project = Project::factory()->create(['created_by' => $owner->id]);
        $request = MaterialRequest::create([
            'project_id' => $project->id,
            'requested_by' => $owner->id,
            'request_date' => now(),
            'status' => 'pending',
        ]);

        $response = $this->getJson("/api/material-requests/{$request->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $request->id);
    }
}
