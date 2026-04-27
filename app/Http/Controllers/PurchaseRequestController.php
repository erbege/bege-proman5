<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\Project;
use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseRequestController extends Controller
{
    protected $approvalService;

    public function __construct(\App\Services\ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        $this->authorize('procurement.manage');

        $query = $project->purchaseRequests()->with(['requestedBy', 'approvedBy', 'items']);
        
        $user = auth()->user();
        if ($user->hasRole('supervisor') || $user->getProjectRole($project) === 'supervisor') {
            $query->where('requested_by', $user->id);
        }

        $prs = $query->latest()->paginate(10);
        $approvedMrs = $project->materialRequests()->where('status', 'approved')->get();

        return view('projects.pr.index', compact('project', 'prs', 'approvedMrs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Project $project)
    {
        $this->authorize('procurement.manage');

        $materials = Material::orderBy('name')->get();
        // ... rest of create logic ...
        $availableMrItems = \App\Models\MaterialRequestItem::whereHas('materialRequest', function ($q) use ($project) {
                $q->where('project_id', $project->id)
                  ->where('status', 'approved');
            })
            ->with(['material', 'materialRequest'])
            ->get()
            ->filter(fn($item) => $item->remaining_to_order > 0)
            ->values();

        $mr = null;
        $items = [];

        if (request('from_mr')) {
            $mr = MaterialRequest::with('items.material')->find(request('from_mr'));
            if ($mr && $mr->status === 'approved') {
                $items = $mr->items
                    ->filter(fn($item) => $item->remaining_to_order > 0)
                    ->map(function ($item) {
                        return [
                            'material_id' => $item->material_id,
                            'material_request_item_id' => $item->id,
                            'material_name' => $item->material->name,
                            'mr_code' => $item->materialRequest->code,
                            'quantity' => $item->remaining_to_order,
                            'unit' => $item->unit,
                            'estimated_price' => 0,
                            'notes' => $item['notes'] ?? '',
                        ];
                    });
            }
        }

        return view('projects.pr.create', compact('project', 'materials', 'mr', 'items', 'availableMrItems'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $this->authorize('procurement.manage');

        $validated = $request->validate([
            'required_date' => 'required|date',
            'priority' => 'required|in:low,normal,high,urgent',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.material_request_item_id' => 'nullable|exists:material_request_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.estimated_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        try {
            $service = app(\App\Services\PurchaseRequestService::class);
            $service->createPurchaseRequest([
                'project_id' => $project->id,
                'required_date' => $validated['required_date'],
                'priority' => $validated['priority'],
                'notes' => $validated['notes'],
                'items' => $validated['items'],
            ], auth()->id());

            return redirect()->route('projects.pr.index', $project)->with('success', 'Purchase Request berhasil dibuat dan diajukan untuk approval.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat PR: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, PurchaseRequest $pr)
    {
        $this->authorize('procurement.manage');

        $user = auth()->user();
        if (($user->hasRole('supervisor') || $user->getProjectRole($project) === 'supervisor') && $pr->requested_by !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke Purchase Request ini.');
        }

        $pr->load(['items.material', 'requestedBy', 'approvedBy', 'approvalLogs.user']);
        return view('projects.pr.show', compact('project', 'pr'));
    }

    /**
     * Update status (Approval).
     */
    public function updateStatus(Request $request, Project $project, PurchaseRequest $pr)
    {
        $this->authorize('financials.manage');

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'comment' => 'nullable|string|max:500'
        ]);

        try {
            if ($request->status === 'approved') {
                $this->approvalService->approve($pr, $request->comment);
            } else {
                $this->approvalService->reject($pr, $request->comment ?? 'Rejected by user');
            }

            // Send notification to the requester
            if ($pr->requestedBy && $pr->requestedBy->id !== auth()->id()) {
                $pr->requestedBy->notify(
                    new \App\Notifications\PurchaseRequestStatusNotification($pr, $request->status)
                );
            }

            return back()->with('success', 'Status PR berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
