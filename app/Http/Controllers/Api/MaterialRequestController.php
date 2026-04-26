<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaterialRequest;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialRequestController extends Controller
{
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
        if ($user && $user->hasRole('supervisor')) {
            $query->where('requested_by', $user->id);
        } else if ($user) {
            $supervisorProjectIds = $user->projects()->wherePivot('role', 'supervisor')->pluck('projects.id');
            if ($supervisorProjectIds->isNotEmpty()) {
                $query->where(function ($q) use ($user, $supervisorProjectIds) {
                    $q->whereNotIn('project_id', $supervisorProjectIds)
                        ->orWhere('requested_by', $user->id);
                });
            }
        }

        return $query->latest()->paginate($request->per_page ?? 15);
    }

    /**
     * Get material request details.
     * 
     * Get detailed information about a specific material request.
     */
    public function show(MaterialRequest $materialRequest)
    {
        $user = auth()->user();
        $isSupervisor = $user && $user->hasRole('supervisor');
        if (!$isSupervisor && $user) {
            $isSupervisor = $user->projects()->wherePivot('role', 'supervisor')->where('projects.id', $materialRequest->project_id)->exists();
        }

        if ($isSupervisor && $materialRequest->requested_by !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $materialRequest->load(['project', 'requestedBy', 'items.material'])
        ]);
    }

    /**
     * Create new material request.
     * 
     * Create a new material request for a project.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'request_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:50',
        ]);

        $materialRequest = MaterialRequest::create([
            'project_id' => $validated['project_id'],
            'requested_by' => auth()->id(),
            'request_date' => $validated['request_date'] ?? now(),
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        foreach ($validated['items'] as $item) {
            if (empty($item['unit'])) {
                $material = Material::find($item['material_id']);
                $item['unit'] = $material->unit ?? '-';
            }
            $materialRequest->items()->create($item);
        }

        return response()->json([
            'message' => 'Material request created successfully',
            'data' => $materialRequest->load('items.material')
        ], 201);
    }

    /**
     * Approve material request.
     * 
     * Approve a pending material request.
     */
    public function approve(MaterialRequest $materialRequest)
    {
        if ($materialRequest->status !== 'pending') {
            return response()->json(['error' => 'Request is not pending'], 422);
        }

        $materialRequest->update([
            'status' => 'approved',
        ]);

        return response()->json([
            'message' => 'Material request approved',
            'data' => $materialRequest
        ]);
    }

    /**
     * Reject material request.
     * 
     * Reject a pending material request.
     */
    public function reject(MaterialRequest $materialRequest)
    {
        if ($materialRequest->status !== 'pending') {
            return response()->json(['error' => 'Request is not pending'], 422);
        }

        $materialRequest->update([
            'status' => 'rejected',
        ]);

        return response()->json([
            'message' => 'Material request rejected',
            'data' => $materialRequest
        ]);
    }
}
