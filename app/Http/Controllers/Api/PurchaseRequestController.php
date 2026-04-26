<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    /**
     * List purchase requests.
     * 
     * Get a paginated list of purchase requests.
     */
    public function index(Request $request)
    {
        $query = PurchaseRequest::with(['project:id,name', 'requestedBy:id,name']);

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $user = auth()->user();
        if ($user && $user->hasRole('supervisor')) {
            $query->where('requested_by', $user->id);
        } else if ($user) {
            $supervisorProjectIds = $user->projects()->wherePivot('role', 'supervisor')->pluck('projects.id');
            if ($supervisorProjectIds->isNotEmpty()) {
                $query->where(function($q) use ($user, $supervisorProjectIds) {
                    $q->whereNotIn('project_id', $supervisorProjectIds)
                      ->orWhere('requested_by', $user->id);
                });
            }
        }

        return $query->latest()->paginate($request->per_page ?? 15);
    }

    /**
     * Get purchase request details.
     * 
     * Get detailed information about a specific purchase request.
     */
    public function show(PurchaseRequest $purchaseRequest)
    {
        $user = auth()->user();
        $isSupervisor = $user && $user->hasRole('supervisor');
        if (!$isSupervisor && $user) {
            $isSupervisor = $user->projects()->wherePivot('role', 'supervisor')->where('projects.id', $purchaseRequest->project_id)->exists();
        }

        if ($isSupervisor && $purchaseRequest->requested_by !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $purchaseRequest->load(['project', 'requestedBy', 'items.material'])
        ]);
    }

    /**
     * Create new purchase request.
     * 
     * Create a new purchase request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.estimated_price' => 'nullable|numeric|min:0',
        ]);

        $purchaseRequest = PurchaseRequest::create([
            'project_id' => $validated['project_id'],
            'requested_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        foreach ($validated['items'] as $item) {
            $purchaseRequest->items()->create($item);
        }

        return response()->json([
            'message' => 'Purchase request created successfully',
            'data' => $purchaseRequest->load('items.material')
        ], 201);
    }

    /**
     * Approve purchase request.
     * 
     * Approve a pending purchase request.
     */
    public function approve(PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'pending') {
            return response()->json(['error' => 'Request is not pending'], 422);
        }

        $purchaseRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Purchase request approved',
            'data' => $purchaseRequest
        ]);
    }

    /**
     * Reject purchase request.
     * 
     * Reject a pending purchase request.
     */
    public function reject(Request $request, PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'pending') {
            return response()->json(['error' => 'Request is not pending'], 422);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $purchaseRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['reason'] ?? null,
        ]);

        return response()->json([
            'message' => 'Purchase request rejected',
            'data' => $purchaseRequest
        ]);
    }
}
