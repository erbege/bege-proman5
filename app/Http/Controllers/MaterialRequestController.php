<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MaterialRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        $query = $project->materialRequests()->with(['requestedBy', 'items']);
        
        $user = auth()->user();
        if ($user->hasRole('supervisor') || $user->getProjectRole($project) === 'supervisor') {
            $query->where('requested_by', $user->id);
        }

        $requests = $query->latest()->paginate(10);
        return view('projects.mr.index', compact('project', 'requests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Project $project)
    {
        $materials = Material::orderBy('name')->get();
        return view('projects.mr.create', compact('project', 'materials'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'request_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string',
            'items.*.notes' => 'nullable|string',
        ]);

        $mr = DB::transaction(function () use ($validated, $project) {
            // Generate Code: MR-PROJECTCODE-001 (simplified)
            $count = MaterialRequest::where('project_id', $project->id)->count() + 1;
            $code = 'MR-' . $project->code . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

            $mr = MaterialRequest::create([
                'project_id' => $project->id,
                'requested_by' => auth()->id(),
                'code' => $code,
                'request_date' => $validated['request_date'],
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $mr->items()->create([
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $mr;
        });

        // Notify project team + admins about new MR
        $mr->load(['project', 'requestedBy']);
        \App\Services\NotificationHelper::sendToProjectTeam(
            $project,
            new \App\Notifications\MaterialRequestCreatedNotification($mr),
            auth()->id()
        );

        return redirect()->route('projects.mr.index', $project)->with('success', 'Material Request berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, MaterialRequest $mr)
    {
        $user = auth()->user();
        if (($user->hasRole('supervisor') || $user->getProjectRole($project) === 'supervisor') && $mr->requested_by !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke Material Request ini.');
        }

        $mr->load(['items.material', 'requestedBy']);
        return view('projects.mr.show', compact('project', 'mr'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project, MaterialRequest $mr)
    {
        // Only pending MR can be edited
        if ($mr->status !== 'pending') {
            return back()->with('error', 'Hanya MR status pending yang bisa diedit.');
        }

        $materials = Material::orderBy('name')->get();
        return view('projects.mr.edit', compact('project', 'mr', 'materials'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, MaterialRequest $mr)
    {
        if ($mr->status !== 'pending') {
            return back()->with('error', 'Hanya MR status pending yang bisa diedit.');
        }

        $validated = $request->validate([
            'request_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $mr) {
            $mr->update([
                'request_date' => $validated['request_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Replace items (simplest approach)
            $mr->items()->delete();

            foreach ($validated['items'] as $item) {
                $mr->items()->create([
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('projects.mr.show', [$project, $mr])->with('success', 'Material Request berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, MaterialRequest $mr)
    {
        if ($mr->status !== 'pending') {
            return back()->with('error', 'Hanya MR status pending yang bisa dihapus.');
        }

        $mr->delete();
        return redirect()->route('projects.mr.index', $project)->with('success', 'Material Request berhasil dihapus.');
    }

    public function updateStatus(Request $request, Project $project, MaterialRequest $mr)
    {
        // Simple approval flow
        $request->validate(['status' => 'required|in:approved,rejected']);

        $mr->update(['status' => $request->status]);

        // Notify the requester
        if ($mr->requestedBy && $mr->requestedBy->id !== auth()->id()) {
            $mr->requestedBy->notify(
                new \App\Notifications\MaterialRequestStatusNotification($mr, $request->status)
            );
        }

        return back()->with('success', 'Status MR berhasil diperbarui.');
    }
}
