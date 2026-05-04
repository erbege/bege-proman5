<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreMaterialRequestRequest;
use App\Http\Resources\Api\MaterialRequestResource;
use App\Models\MaterialRequest;
use App\Services\MaterialRequestService;
use Illuminate\Http\Request;

/**
 * @group Procurement: Material Requests
 * @authenticated
 * 
 * Endpoints for managing material requests (MR) from the field.
 */
class MaterialRequestController extends Controller
{
    use ApiResponse;

    protected $mrService;

    private const ELEVATED_ROLES = ['Superadmin', 'super-admin', 'administrator'];

    public function __construct(MaterialRequestService $mrService)
    {
        $this->mrService = $mrService;
    }

    /**
     * List material requests.
     * 
     * Get a paginated list of material requests.
     */
    public function index(Request $request)
    {
        $query = MaterialRequest::with(['project:id,name', 'requestedBy:id,name']);

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $user = auth()->user();
        if ($user) {
            $this->applyVisibilityScope($query, $user);
        }

        $requests = $query->latest()->paginate($request->per_page ?? 15);

        return $this->paginatedResponse(
            'Material requests retrieved successfully.', 
            MaterialRequestResource::collection($requests)
        );
    }

    /**
     * Get material request details.
     * 
     * Get detailed information about a specific material request.
     */
    public function show(MaterialRequest $materialRequest)
    {
        $user = auth()->user();
        if (!$user || !$this->canViewRequest($materialRequest, $user)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $materialRequest->load(['project', 'requestedBy', 'items.material', 'approvalLogs']);

        return $this->successResponse(
            'Material request retrieved successfully.', 
            new MaterialRequestResource($materialRequest)
        );
    }

    /**
     * Create new material request.
     * 
     * Create a new material request for a project.
     */
    public function store(StoreMaterialRequestRequest $request)
    {
        $this->authorize('mr.manage');
        if (!$this->mrService->canCreateRequest(auth()->id(), $request->project_id)) {
            return $this->errorResponse('Anda tidak terdaftar dalam tim proyek ini.', 403);
        }

        $materialRequest = $this->mrService->createMaterialRequest(
            $request->validated(),
            auth()->id()
        );

        return $this->successResponse(
            'Material request created successfully', 
            new MaterialRequestResource($materialRequest->load('items.material')), 
            201
        );
    }

    /**
     * Approve material request.
     * 
     * Approve a pending material request at the current level.
     */
    public function approve(Request $request, MaterialRequest $materialRequest)
    {
        try {
            $this->mrService->approvalService()->approve($materialRequest, $request->comment);
            $materialRequest->refresh();
            
            return $this->successResponse(
                'Material request approved', 
                new MaterialRequestResource($materialRequest->load('approvalLogs'))
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Reject material request.
     * 
     * Reject a pending material request.
     */
    public function reject(Request $request, MaterialRequest $materialRequest)
    {
        try {
            $reason = $request->input('comment', 'Rejected');
            $this->mrService->approvalService()->reject($materialRequest, $reason);
            $materialRequest->refresh();
            
            return $this->successResponse(
                'Material request rejected', 
                new MaterialRequestResource($materialRequest->load('approvalLogs'))
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    private function applyVisibilityScope($query, $user): void
    {
        if ($user->hasAnyRole(self::ELEVATED_ROLES) || $user->can('projects.view.all')) {
            return;
        }

        $projectIds = $user->projects()->pluck('projects.id');
        $query->where(function ($q) use ($user, $projectIds) {
            $q->where('requested_by', $user->id);
            if ($projectIds->isNotEmpty()) {
                $q->orWhereIn('project_id', $projectIds);
            }
        });
    }

    private function canViewRequest(MaterialRequest $materialRequest, $user): bool
    {
        if ($user->hasAnyRole(self::ELEVATED_ROLES) || $user->can('projects.view.all')) {
            return true;
        }

        if ($materialRequest->requested_by === $user->id) {
            return true;
        }

        return $user->projects()->where('projects.id', $materialRequest->project_id)->exists();
    }
}
