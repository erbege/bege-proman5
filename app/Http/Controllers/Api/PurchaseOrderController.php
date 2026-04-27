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
}
