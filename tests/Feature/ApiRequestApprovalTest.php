<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\MaterialRequest;
use App\Models\PurchaseRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ApiRequestApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        $this->project = Project::factory()->create();
    }

    public function test_can_approve_material_request()
    {
        $mr = MaterialRequest::create([
            'project_id' => $this->project->id,
            'requested_by' => $this->user->id,
            'code' => 'MR-001',
            'request_date' => now(),
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/material-requests/{$mr->id}/approve");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Material request approved')
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('material_requests', [
            'id' => $mr->id,
            'status' => 'approved',
        ]);
    }

    public function test_can_reject_material_request()
    {
        $mr = MaterialRequest::create([
            'project_id' => $this->project->id,
            'requested_by' => $this->user->id,
            'code' => 'MR-001',
            'request_date' => now(),
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/material-requests/{$mr->id}/reject");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Material request rejected')
            ->assertJsonPath('data.status', 'rejected');

        $this->assertDatabaseHas('material_requests', [
            'id' => $mr->id,
            'status' => 'rejected',
        ]);
    }

    public function test_can_approve_purchase_request()
    {
        $pr = PurchaseRequest::create([
            'project_id' => $this->project->id,
            'requested_by' => $this->user->id,
            'status' => 'pending',
            'request_date' => now(),
            'required_date' => now()->addDays(7),
        ]);

        $response = $this->postJson("/api/purchase-requests/{$pr->id}/approve");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Purchase request approved')
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $pr->id,
            'status' => 'approved',
            'approved_by' => $this->user->id,
        ]);
    }

    public function test_can_reject_purchase_request_with_reason()
    {
        $pr = PurchaseRequest::create([
            'project_id' => $this->project->id,
            'requested_by' => $this->user->id,
            'status' => 'pending',
            'request_date' => now(),
            'required_date' => now()->addDays(7),
        ]);

        $response = $this->postJson("/api/purchase-requests/{$pr->id}/reject", [
            'reason' => 'Too expensive'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Purchase request rejected')
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.rejection_reason', 'Too expensive');

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $pr->id,
            'status' => 'rejected',
            'rejection_reason' => 'Too expensive',
        ]);
    }

    public function test_cannot_approve_already_processed_request()
    {
        $pr = PurchaseRequest::create([
            'project_id' => $this->project->id,
            'requested_by' => $this->user->id,
            'status' => 'approved',
            'request_date' => now(),
            'required_date' => now()->addDays(7),
        ]);

        $response = $this->postJson("/api/purchase-requests/{$pr->id}/approve");

        $response->assertStatus(422)
            ->assertJsonPath('error', 'Request is not pending');
    }
}
