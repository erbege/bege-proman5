<?php

namespace App\Services;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class MaterialRequestService
{
    protected $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Create a new Material Request.
     */
    public function createMaterialRequest(array $data, int $userId): MaterialRequest
    {
        return DB::transaction(function () use ($data, $userId) {
            $materialRequest = MaterialRequest::create([
                'project_id' => $data['project_id'],
                'requested_by' => $userId,
                'request_date' => $data['request_date'] ?? now(),
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
            ]);

            foreach ($data['items'] as $item) {
                if (empty($item['unit'])) {
                    $material = Material::find($item['material_id']);
                    $item['unit'] = $material->unit ?? '-';
                }

                $materialRequest->items()->create([
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Submit for approval
            $this->approvalService->submit($materialRequest);

            return $materialRequest;
        });
    }

    /**
     * Validate if a user can create a request for a project.
     */
    public function canCreateRequest(int $userId, int $projectId): bool
    {
        $project = Project::find($projectId);
        if (!$project) return false;

        // Check if user is in team or is admin
        $user = \App\Models\User::find($userId);
        if ($user->hasAnyRole(['super-admin', 'Superadmin', 'administrator'])) {
            return true;
        }

        return $project->team()->where('users.id', $userId)->exists();
    }
}
