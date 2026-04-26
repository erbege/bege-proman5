<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        $orders = $project->purchaseOrders()
            ->with(['supplier', 'createdBy'])
            ->latest()
            ->paginate(10);

        return view('projects.po.index', compact('project', 'orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Project $project)
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        // Get Approved PRs that are NOT completed
        $pendingPrs = $project->purchaseRequests()
            ->where('status', 'approved')
            ->with(['items.material', 'items'])
            ->orderBy('pr_number')
            ->get();

        return view('projects.po.create', compact('project', 'suppliers', 'pendingPrs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery' => 'required|date|after_or_equal:order_date',
            'payment_terms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'pr_ids' => 'nullable|array',
            'pr_ids.*' => 'exists:purchase_requests,id',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        $po = DB::transaction(function () use ($validated, $project) {
            // Calculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $tax = $validated['tax_amount'] ?? 0;
            $discount = $validated['discount_amount'] ?? 0;
            $total = $subtotal + $tax - $discount;

            $po = PurchaseOrder::create([
                'project_id' => $project->id,
                'supplier_id' => $validated['supplier_id'],
                // Link to single PR only if exactly one is used, otherwise null
                'purchase_request_id' => (isset($validated['pr_ids']) && count($validated['pr_ids']) === 1) ? $validated['pr_ids'][0] : null,
                'order_date' => $validated['order_date'],
                'expected_delivery' => $validated['expected_delivery'],
                'status' => 'sent', // Assume Sent immediately for now, or Draft
                'payment_terms' => $validated['payment_terms'],
                'notes' => $validated['notes'],
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $po->items()->create([
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Mark PRs as completed
            if (!empty($validated['pr_ids'])) {
                PurchaseRequest::whereIn('id', $validated['pr_ids'])->update(['status' => 'completed']);
            }

            return $po;
        });

        // Notify project team members (except creator)
        $po->load('supplier');
        $teamMembers = $project->team()
            ->where('users.id', '!=', auth()->id())
            ->get();

        \Illuminate\Support\Facades\Notification::send(
            $teamMembers,
            new \App\Notifications\PurchaseOrderCreatedNotification($po)
        );

        return redirect()->route('projects.po.index', $project)->with('success', 'Purchase Order berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, PurchaseOrder $po)
    {
        $po->load(['items.material', 'supplier', 'createdBy', 'project']);
        return view('projects.po.show', compact('project', 'po'));
    }

    /**
     * Print/PDF view of the PO.
     */
    public function print(Project $project, PurchaseOrder $po)
    {
        $po->load(['items.material', 'supplier', 'createdBy', 'project']);
        return view('projects.po.print', compact('project', 'po'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, PurchaseOrder $po)
    {
        if ($po->status !== 'draft' && $po->status !== 'sent') {
            return back()->with('error', 'Hanya PO status Draft atau Sent yang dapat dihapus.');
        }

        // Revert PR status if linked?
        // If we grouped, we lost the strict link in `purchase_request_id` column but we might have it in logs?
        // For MVP, if we delete PO, we might leave PRs as completed (manual fix needed) or we try to find them.
        // Since we didn't store the grouped IDs, we can't revert easily.
        // One improvement: Store linked PR IDs in `notes` or pivot.
        // For now, simple delete.

        $po->delete();
        return redirect()->route('projects.po.index', $project)->with('success', 'Purchase Order berhasil dihapus.');
    }
}
