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
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        $query = $project->purchaseRequests()->with(['requestedBy', 'approvedBy', 'items']);
        
        $user = auth()->user();
        if ($user->hasRole('supervisor') || $user->getProjectRole($project) === 'supervisor') {
            $query->where('requested_by', $user->id);
        }

        $prs = $query->latest()->paginate(10);

        return view('projects.pr.index', compact('project', 'prs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Project $project)
    {
        $materials = Material::orderBy('name')->get();

        $mr = null;
        $items = [];

        if (request('from_mr')) {
            $mr = MaterialRequest::with('items')->find(request('from_mr'));
            if ($mr && $mr->status === 'approved') {
                $items = $mr->items->map(function ($item) {
                    return [
                        'material_id' => $item->material_id,
                        'quantity' => $item->quantity,
                        'unit' => $item->unit,
                        'estimated_price' => 0, // Default
                        'notes' => $item->notes,
                    ];
                });
            }
        }

        return view('projects.pr.create', compact('project', 'materials', 'mr', 'items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'required_date' => 'required|date',
            'priority' => 'required|in:low,normal,high,urgent',
            'notes' => 'nullable|string',
            'from_mr_id' => 'nullable|exists:material_requests,id',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.estimated_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        $pr = DB::transaction(function () use ($validated, $project) {
            $pr = PurchaseRequest::create([
                'project_id' => $project->id,
                'request_date' => now(),
                'required_date' => $validated['required_date'],
                'status' => 'pending', // Pending approval
                'priority' => $validated['priority'],
                'notes' => $validated['notes'] . ($validated['from_mr_id'] ? " (From MR ID: {$validated['from_mr_id']})" : ""),
                'requested_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $pr->items()->create([
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                    'estimated_price' => $item['estimated_price'] ?? 0,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Update MR status if applicable
            if (!empty($validated['from_mr_id'])) {
                $mr = MaterialRequest::find($validated['from_mr_id']);
                if ($mr) {
                    $mr->update(['status' => 'processed']);
                }
            }

            return $pr;
        });

        // Notify project team + admins about new PR
        $pr->load(['project', 'requestedBy']);
        \App\Services\NotificationHelper::sendToProjectTeam(
            $project,
            new \App\Notifications\PurchaseRequestCreatedNotification($pr),
            auth()->id()
        );

        return redirect()->route('projects.pr.index', $project)->with('success', 'Purchase Request berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, PurchaseRequest $pr)
    {
        $user = auth()->user();
        if (($user->hasRole('supervisor') || $user->getProjectRole($project) === 'supervisor') && $pr->requested_by !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke Purchase Request ini.');
        }

        $pr->load(['items.material', 'requestedBy', 'approvedBy']);
        return view('projects.pr.show', compact('project', 'pr'));
    }

    /**
     * Update status (Approval).
     */
    public function updateStatus(Request $request, Project $project, PurchaseRequest $pr)
    {
        $request->validate(['status' => 'required|in:approved,rejected']);

        $data = [
            'status' => $request->status,
        ];

        if ($request->status === 'approved') {
            $data['approved_by'] = auth()->id();
            $data['approved_at'] = now();
        } elseif ($request->status === 'rejected') {
            // Reason logic could be added here
        }

        $pr->update($data);

        // Send notification to the requester
        if ($pr->requestedBy && $pr->requestedBy->id !== auth()->id()) {
            $pr->requestedBy->notify(
                new \App\Notifications\PurchaseRequestStatusNotification($pr, $request->status)
            );
        }

        return back()->with('success', 'Status PR berhasil diperbarui.');
    }
}
