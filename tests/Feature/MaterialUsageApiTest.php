<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialUsage;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MaterialUsageApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;
    protected $material;

    protected function setUp(): void
    {
        parent::setUp();

        // Create user and authenticate
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);

        // Create project
        $this->project = Project::factory()->create(['created_by' => $this->user->id]);

        // Create material
        $this->material = Material::factory()->create(['name' => 'Semen', 'unit' => 'Sak']);
    }

    public function test_can_list_material_usages()
    {
        // Create dummy usages
        MaterialUsage::factory()->count(3)->create(['project_id' => $this->project->id]);

        $response = $this->getJson("/api/projects/{$this->project->id}/material-usages");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'usage_date', 'usage_number', 'notes', 'items']
                ]
            ]);
    }

    public function test_can_create_material_usage_with_sufficient_stock()
    {
        // Add stock to inventory
        Inventory::create([
            'project_id' => $this->project->id,
            'material_id' => $this->material->id,
            'quantity' => 100,
            'reserved_qty' => 0
        ]);

        $data = [
            'usage_date' => now()->toDateString(),
            'notes' => 'Testing usage',
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'quantity' => 10,
                    'notes' => 'Used for foundation'
                ]
            ]
        ];

        $response = $this->postJson("/api/projects/{$this->project->id}/material-usages", $data);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Material usage created successfully');

        // Check database
        $this->assertDatabaseHas('material_usages', [
            'project_id' => $this->project->id,
            'notes' => 'Testing usage'
        ]);

        $this->assertDatabaseHas('material_usage_items', [
            'material_id' => $this->material->id,
            'quantity' => 10
        ]);

        // Check inventory deduction
        $this->assertDatabaseHas('inventories', [
            'project_id' => $this->project->id,
            'material_id' => $this->material->id,
            'quantity' => 90 // 100 - 10
        ]);
    }

    public function test_cannot_create_material_usage_with_insufficient_stock()
    {
        // Add minimal stock
        Inventory::create([
            'project_id' => $this->project->id,
            'material_id' => $this->material->id,
            'quantity' => 5,
            'reserved_qty' => 0
        ]);

        $data = [
            'usage_date' => now()->toDateString(),
            'notes' => 'Testing usage fail',
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'quantity' => 10, // Requesting more than available
                    'notes' => 'Should fail'
                ]
            ]
        ];

        $response = $this->postJson("/api/projects/{$this->project->id}/material-usages", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);

        // Check that stock was NOT deducted
        $this->assertDatabaseHas('inventories', [
            'project_id' => $this->project->id,
            'material_id' => $this->material->id,
            'quantity' => 5
        ]);
    }

    public function test_can_show_material_usage()
    {
        $usage = MaterialUsage::factory()->create(['project_id' => $this->project->id]);
        
        // Add an item manually since factory might not create items
        $usage->items()->create([
            'material_id' => $this->material->id,
            'quantity' => 10,
        ]);

        $response = $this->getJson("/api/projects/{$this->project->id}/material-usages/{$usage->id}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $usage->id)
            ->assertJsonStructure([
                'id',
                'usage_number',
                'items' => [
                    '*' => [
                        'id',
                        'material_id',
                        'quantity',
                        'material' => ['id', 'name']
                    ]
                ],
                'created_by' => ['id', 'name']
            ]);
    }

    public function test_show_material_usage_from_wrong_project_returns_404()
    {
        $otherProject = Project::factory()->create();
        $usage = MaterialUsage::factory()->create(['project_id' => $otherProject->id]);

        $response = $this->getJson("/api/projects/{$this->project->id}/material-usages/{$usage->id}");

        $response->assertStatus(404)
            ->assertJson(['error' => 'Material Usage not found in this project']);
    }
}
