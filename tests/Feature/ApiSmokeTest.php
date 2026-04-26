<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\MaterialRequest;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceipt;
use App\Models\Inventory;

class ApiSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;

    public function setUp(): void
    {
        parent::setUp();

        // Create a user and authenticate
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);

        // Create common data needed for tests
        $this->project = Project::factory()->create(['created_by' => $this->user->id]);
    }

    /**
     * Auth Module
     */
    public function test_auth_logout()
    {
        $response = $this->postJson('/api/auth/logout');
        $response->assertStatus(200);
    }

    public function test_auth_user_profile()
    {
        $response = $this->getJson('/api/auth/user');
        $response->assertStatus(200);
    }

    /**
     * Dashboard Module
     */
    public function test_dashboard_stats()
    {
        $response = $this->getJson('/api/dashboard/stats');
        $response->assertStatus(200);
    }

    /**
     * Master Data Module
     */
    public function test_materials_list()
    {
        Material::factory()->count(3)->create();
        $response = $this->getJson('/api/materials');
        $response->assertStatus(200);
    }

    public function test_suppliers_list()
    {
        Supplier::factory()->count(3)->create();
        $response = $this->getJson('/api/suppliers');
        $response->assertStatus(200);
    }

    public function test_clients_list()
    {
        Client::factory()->count(3)->create();
        $response = $this->getJson('/api/clients');
        $response->assertStatus(200);
    }

    /**
     * Projects Module
     */
    public function test_projects_list()
    {
        $response = $this->getJson('/api/projects');
        $response->assertStatus(200);
    }

    public function test_project_show()
    {
        $response = $this->getJson("/api/projects/{$this->project->id}");
        $response->assertStatus(200);
    }

    public function test_project_team()
    {
        $response = $this->getJson("/api/projects/{$this->project->id}/team");
        $response->assertStatus(200);
    }

    public function test_project_stats()
    {
        $response = $this->getJson("/api/projects/{$this->project->id}/stats");
        $response->assertStatus(200);
    }

    /**
     * Engineering Module
     */
    public function test_rab_index()
    {
        $response = $this->getJson("/api/projects/{$this->project->id}/rab");
        $response->assertStatus(200);
    }

    public function test_schedule_index()
    {
        $response = $this->getJson("/api/projects/{$this->project->id}/schedule");
        $response->assertStatus(200);
    }

    /**
     * Field Operations Module
     */
    public function test_progress_reports_index()
    {
        $response = $this->getJson("/api/projects/{$this->project->id}/progress");
        $response->assertStatus(200);
    }

    /**
     * Procurement Module
     */
    public function test_material_requests_index()
    {
        $response = $this->getJson('/api/material-requests');
        $response->assertStatus(200);
    }

    public function test_purchase_requests_index()
    {
        $response = $this->getJson('/api/purchase-requests');
        $response->assertStatus(200);
    }

    public function test_purchase_orders_index()
    {
        $response = $this->getJson('/api/purchase-orders');
        $response->assertStatus(200);
    }

    public function test_goods_receipts_index()
    {
        $response = $this->getJson('/api/goods-receipts');
        $response->assertStatus(200);
    }

    /**
     * Inventory Module
     */
    public function test_inventory_index()
    {
        $response = $this->getJson('/api/inventory');
        $response->assertStatus(200);
    }

    public function test_inventory_history()
    {
        $response = $this->getJson('/api/inventory/history');
        $response->assertStatus(200);
    }

    /**
     * Utilities Module
     */
    public function test_notifications_index()
    {
        $response = $this->getJson('/api/notifications');
        $response->assertStatus(200);
    }
}
