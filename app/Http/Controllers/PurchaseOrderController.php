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
    public function store(Request $request, Project $project, \App\Services\PurchaseOrderService $poService)
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

        $poService->createPurchaseOrder($validated, $project, auth()->id());

        return redirect()->route('projects.po.index', $project)->with('success', 'Purchase Order berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, PurchaseOrder $po)
    {
        $po->load(['items.material', 'supplier', 'createdBy', 'project', 'approvalLogs.user']);
        return view('projects.po.show', compact('project', 'po'));
    }

    /**
     * Update status (Approval).
     */
    public function updateStatus(Request $request, Project $project, PurchaseOrder $po)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'comment' => 'nullable|string|max:500'
        ]);

        try {
            if ($request->status === 'approved') {
                $this->approvalService->approve($po, $request->comment);
            } else {
                $this->approvalService->reject($po, $request->comment ?? 'Rejected by user');
            }

            return back()->with('success', 'Status PO berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Print/PDF view of the PO.
     */
    public function print(Project $project, PurchaseOrder $po)
    {
        if (!$po->is_fully_approved) {
            return back()->with('error', 'PO harus disetujui sepenuhnya sebelum dicetak.');
        }
        $po->load(['items.material', 'supplier', 'createdBy', 'project']);
        return view('projects.po.print', compact('project', 'po'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, PurchaseOrder $po)
    {
        if ($po->status !== 'draft' && $po->status !== 'pending') {
            return back()->with('error', 'Hanya PO status Draft atau Pending yang dapat dihapus.');
        }

        $po->delete();
        return redirect()->route('projects.po.index', $project)->with('success', 'Purchase Order berhasil dihapus.');
    }
}
