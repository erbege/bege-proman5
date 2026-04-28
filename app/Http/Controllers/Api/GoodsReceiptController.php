<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Services\GoodsReceiptService;
use Illuminate\Http\Request;

/**
 * @group Procurement: Goods Receipts
 * @authenticated
 * 
 * Endpoints for managing goods receipts (GR) and inventory updates.
 */
class GoodsReceiptController extends Controller
{
    use ApiResponse;

    protected $grService;
    private const ELEVATED_ROLES = ['Superadmin', 'super-admin', 'administrator'];

    public function __construct(GoodsReceiptService $grService)
    {
        $this->grService = $grService;
    }

    /**
     * List goods receipts.
     * 
     * Get a paginated list of goods receipts.
     */
    public function index(Request $request)
    {
        $this->authorize('gr.view');
        $query = GoodsReceipt::with(['project:id,name', 'purchaseOrder:id,po_number', 'receivedBy:id,name']);

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

        return $this->paginatedResponse(
            'Goods receipts retrieved successfully.',
            $query->latest()->paginate($request->per_page ?? 15)
        );
    }

    /**
     * Get goods receipt details.
     * 
     * Get detailed information about a specific goods receipt.
     */
    public function show(GoodsReceipt $goodsReceipt)
    {
        $user = auth()->user();
        if (!$user || !$this->canViewRequest($goodsReceipt, $user)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse(
            'Goods receipt retrieved successfully.',
            $goodsReceipt->load(['project', 'purchaseOrder', 'items.material', 'receivedBy', 'approvalLogs'])
        );
    }

    /**
     * Create new goods receipt.
     * 
     * Create a new goods receipt and submit for approval.
     */
    public function store(Request $request)
    {
        $this->authorize('gr.create');
        
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'receipt_date' => 'nullable|date',
            'delivery_note_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.purchase_order_item_id' => 'nullable|exists:purchase_order_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string',
        ]);

        try {
            $goodsReceipt = $this->grService->createGoodsReceipt($validated, auth()->id());

            return $this->successResponse(
                'Goods receipt created and submitted for approval',
                $goodsReceipt->load('items.material'),
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Approve goods receipt.
     */
    public function approve(Request $request, GoodsReceipt $goodsReceipt)
    {
        $this->authorize('gr.approve');

        try {
            $this->grService->approvalService()->approve($goodsReceipt, $request->comment);
            
            // If fully approved, finalize (update inventory)
            if ($goodsReceipt->is_fully_approved) {
                $this->grService->finalize($goodsReceipt);
            }

            return $this->successResponse(
                'Goods receipt approved successfully', 
                $goodsReceipt->load('approvalLogs')
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * Reject goods receipt.
     */
    public function reject(Request $request, GoodsReceipt $goodsReceipt)
    {
        $this->authorize('gr.approve');

        $request->validate([
            'comment' => 'required|string|max:500'
        ]);

        try {
            $this->grService->approvalService()->reject($goodsReceipt, $request->comment);
            
            return $this->successResponse(
                'Goods receipt rejected', 
                $goodsReceipt->load('approvalLogs')
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
            $q->where('received_by', $user->id);
            if ($projectIds->isNotEmpty()) {
                $q->orWhereIn('project_id', $projectIds);
            }
        });
    }

    private function canViewRequest(GoodsReceipt $goodsReceipt, $user): bool
    {
        if ($user->hasAnyRole(self::ELEVATED_ROLES) || $user->can('projects.view.all')) {
            return true;
        }

        if ($goodsReceipt->received_by === $user->id) {
            return true;
        }

        return $user->projects()->where('projects.id', $goodsReceipt->project_id)->exists();
    }
}
