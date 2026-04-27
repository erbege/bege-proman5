<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RejectPurchaseRequestRequest;
use App\Http\Requests\Api\StorePurchaseRequestRequest;
use App\Http\Resources\Api\PurchaseRequestResource;
use App\Models\PurchaseRequest;
use App\Services\PurchaseRequestService;
use Illuminate\Http\Request;

/**
 * @group Procurement: Purchase Requests
 * @authenticated
 * 
 * Endpoints for managing purchase requests (PR).
 */
class PurchaseRequestController extends Controller
{
    use ApiResponse;

    protected $prService;

    private const ELEVATED_ROLES = ['super-admin', 'Superadmin', 'administrator'];

    public function __construct(PurchaseRequestService $prService)
    {
        $this->prService = $prService;
    }

    /**
     * List purchase requests.
     * 
     * Get a paginated list of purchase requests.
     */
    public function index(Request $request)
    {
        $this->authorize('procurement.view');
        $query = PurchaseRequest::with(['project:id,name', 'requestedBy:id,name']);

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
            'Purchase requests retrieved successfully.', 
            PurchaseRequestResource::collection($requests)
        );
    }

    /**
     * Get purchase request details.
     * 
     * Get detailed information about a specific purchase request.
     */
    public function show(PurchaseRequest $purchaseRequest)
    {
        $this->authorize('procurement.view');
        $user = auth()->user();
        if (!$user || !$this->canViewRequest($purchaseRequest, $user)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $purchaseRequest->load(['project', 'requestedBy', 'items.material', 'approvalLogs']);

        return $this->successResponse(
            'Purchase request retrieved successfully.', 
            new PurchaseRequestResource($purchaseRequest)
        );
    }

    /**
     * Create new purchase request.
     * 
     * Create a new purchase request using the service layer.
     */
    public function store(StorePurchaseRequestRequest $request)
    {
        $this->authorize('procurement.manage');
        if (!$this->prService->canCreatePR(auth()->id(), $request->project_id)) {
            return $this->errorResponse('Anda tidak terdaftar dalam tim proyek ini.', 403);
        }

        $purchaseRequest = $this->prService->createPurchaseRequest(
            $request->validated(),
            auth()->id()
        );

        return $this->successResponse(
            'Purchase request created successfully', 
            new PurchaseRequestResource($purchaseRequest->load('items.material')), 
            201
        );
    }

    /**
     * Approve purchase request.
     * 
     * Approve a pending purchase request.
     */
    public function approve(PurchaseRequest $purchaseRequest)
    {
        $this->authorize('financials.manage');
        if ($purchaseRequest->status !== 'pending') {
            return $this->errorResponse('Request is not pending', 422);
        }

        $purchaseRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return $this->successResponse(
            'Purchase request approved', 
            new PurchaseRequestResource($purchaseRequest)
        );
    }

    /**
     * Reject purchase request.
     * 
     * Reject a pending purchase request.
     */
    public function reject(RejectPurchaseRequestRequest $request, PurchaseRequest $purchaseRequest)
    {
        $this->authorize('financials.manage');
        if ($purchaseRequest->status !== 'pending') {
            return $this->errorResponse('Request is not pending', 422);
        }

        $validated = $request->validated();

        $purchaseRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['reason'] ?? null,
        ]);

        return $this->successResponse(
            'Purchase request rejected', 
            new PurchaseRequestResource($purchaseRequest)
        );
    }

    /**
     * Get available MR items for import.
     * 
     * Get list of approved MR items for a project that have not been fully processed to PR.
     * 
     * @urlParam project_id integer required The ID of the project.
     */
    public function availableMrItems(Request $request, \App\Models\Project $project)
    {
        $user = auth()->user();
        if (!$user->projects()->where('projects.id', $project->id)->exists() && !$user->hasAnyRole(self::ELEVATED_ROLES)) {
            return $this->errorResponse('Unauthorized access to project', 403);
        }

        $items = \App\Models\MaterialRequestItem::whereHas('materialRequest', function ($q) use ($project) {
                $q->where('project_id', $project->id)
                  ->where('status', 'approved');
            })
            ->with(['material:id,name,unit', 'materialRequest:id,code'])
            ->get()
            ->filter(fn($item) => $item->remaining_to_order > 0)
            ->values();

        return $this->successResponse('Available MR items retrieved successfully.', $items->map(fn($item) => [
            'id' => $item->id,
            'mr_id' => $item->material_request_id,
            'mr_code' => $item->materialRequest?->code,
            'material_id' => $item->material_id,
            'material_name' => $item->material?->name,
            'unit' => $item->material?->unit,
            'quantity_requested' => (float) $item->quantity,
            'quantity_ordered' => (float) $item->ordered_quantity,
            'remaining_to_order' => (float) $item->remaining_to_order,
            'notes' => $item->notes,
        ]));
    }

    private function applyVisibilityScope($query, $user): void
    {
        if ($user->hasAnyRole(self::ELEVATED_ROLES)) {
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

    private function canViewRequest(PurchaseRequest $purchaseRequest, $user): bool
    {
        if ($user->hasAnyRole(self::ELEVATED_ROLES)) {
            return true;
        }

        if ($purchaseRequest->requested_by === $user->id) {
            return true;
        }

        return $user->projects()->where('projects.id', $purchaseRequest->project_id)->exists();
    }
}
