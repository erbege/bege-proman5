<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePurchaseOrderRequest;
use App\Http\Resources\Api\PurchaseOrderResource;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;

/**
 * @group Procurement: Purchase Orders
 * @authenticated
 * 
 * Endpoints for managing purchase orders (PO) to suppliers.
 */
class PurchaseOrderController extends Controller
{
    use ApiResponse;

    protected $poService;
    private const ELEVATED_ROLES = ['Superadmin', 'super-admin', 'administrator'];

    public function __construct(PurchaseOrderService $poService)
    {
        $this->poService = $poService;
    }

    /**
     * List purchase orders.
     * 
     * Get a paginated list of purchase orders.
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['project:id,name', 'supplier:id,name', 'createdBy:id,name']);

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $user = auth()->user();
        if ($user) {
            $this->applyVisibilityScope($query, $user);
        }

        $orders = $query->latest()->paginate($request->per_page ?? 15);

        return $this->paginatedResponse(
            'Purchase orders retrieved successfully.',
            PurchaseOrderResource::collection($orders)
        );
    }

    /**
     * Get purchase order details.
     * 
     * Get detailed information about a specific purchase order.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $user = auth()->user();
        if (!$user || !$this->canViewRequest($purchaseOrder, $user)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $purchaseOrder->load(['project', 'supplier', 'items.material', 'createdBy', 'approvalLogs', 'purchaseRequests']);
        
        return $this->successResponse(
            'Purchase order detail retrieved successfully.',
            new PurchaseOrderResource($purchaseOrder)
        );
    }

    /**
     * Create new purchase order.
     * 
     * Create a new purchase order using the service layer.
     */
    public function store(StorePurchaseOrderRequest $request)
    {
        $this->authorize('procurement.manage');
        $project = Project::findOrFail($request->input('project_id'));
        
        $purchaseOrder = $this->poService->createPurchaseOrder(
            $request->validated(),
            $project,
            auth()->id()
        );

        return $this->successResponse(
            'Purchase order created successfully',
            new PurchaseOrderResource($purchaseOrder->load('items.material')),
            201
        );
    }

    /**
     * Approve purchase order.
     */
    public function approve(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('po.approve');

        try {
            $this->poService->approvalService()->approve($purchaseOrder, $request->comment);
            
            return $this->successResponse(
                'Purchase order approved successfully', 
                new PurchaseOrderResource($purchaseOrder->load('approvalLogs'))
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Reject purchase order.
     */
    public function reject(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('po.approve');

        $request->validate([
            'comment' => 'required|string|max:500'
        ]);

        try {
            $this->poService->approvalService()->reject($purchaseOrder, $request->comment);
            
            return $this->successResponse(
                'Purchase order rejected', 
                new PurchaseOrderResource($purchaseOrder->load('approvalLogs'))
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
    /**
     * Get available PR items for import.
     * 
     * Get list of approved PR items for a project that have not been fully processed to PO.
     * 
     * @urlParam project_id integer required The ID of the project.
     */
    public function availablePrItems(Request $request, Project $project)
    {
        $user = auth()->user();
        if (!$user->projects()->where('projects.id', $project->id)->exists() && !$user->hasAnyRole(self::ELEVATED_ROLES) && !$user->can('projects.view.all')) {
            return $this->errorResponse('Unauthorized access to project', 403);
        }

        $items = \App\Models\PurchaseRequestItem::whereHas('purchaseRequest', function ($q) use ($project) {
                $q->where('project_id', $project->id)
                  ->where('status', 'approved');
            })
            ->with(['material:id,name,unit', 'purchaseRequest:id,pr_number'])
            ->get()
            ->filter(fn($item) => $item->remaining_to_order > 0)
            ->values();

        return $this->successResponse('Available PR items retrieved successfully.', $items->map(fn($item) => [
            'id' => $item->id,
            'pr_id' => $item->purchase_request_id,
            'pr_number' => $item->purchaseRequest?->pr_number,
            'material_id' => $item->material_id,
            'material_name' => $item->material?->name,
            'unit' => $item->material?->unit,
            'quantity_requested' => (float) $item->quantity,
            'quantity_ordered' => (float) $item->ordered_quantity,
            'remaining_to_order' => (float) $item->remaining_to_order,
            'estimated_price' => (float) $item->estimated_price,
            'notes' => $item->notes,
        ]));
    }

    private function applyVisibilityScope($query, $user): void
    {
        if ($user->hasAnyRole(self::ELEVATED_ROLES) || $user->can('projects.view.all')) {
            return;
        }

        $projectIds = $user->projects()->pluck('projects.id');
        $query->where(function ($q) use ($user, $projectIds) {
            $q->where('created_by', $user->id);
            if ($projectIds->isNotEmpty()) {
                $q->orWhereIn('project_id', $projectIds);
            }
        });
    }

    private function canViewRequest(PurchaseOrder $purchaseOrder, $user): bool
    {
        if ($user->hasAnyRole(self::ELEVATED_ROLES) || $user->can('projects.view.all')) {
            return true;
        }

        if ($purchaseOrder->created_by === $user->id) {
            return true;
        }

        return $user->projects()->where('projects.id', $purchaseOrder->project_id)->exists();
    }
}
