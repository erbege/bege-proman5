<?php

namespace App\Http\Controllers;

use App\Models\ApprovalMatrix;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class ApprovalMatrixController extends Controller
{
    public function index()
    {
        $matrices = ApprovalMatrix::orderBy('document_type')
            ->orderBy('level')
            ->get();
            
        $roles = Role::all();
        
        return view('settings.approval-matrix', compact('matrices', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|in:MR,PR,PO',
            'level' => 'required|integer|min:1',
            'role_name' => 'required|exists:roles,name',
            'min_amount' => 'nullable|numeric|min:0',
        ]);

        ApprovalMatrix::create($validated);

        return back()->with('success', 'Matriks approval berhasil ditambahkan.');
    }

    public function update(Request $request, ApprovalMatrix $matrix)
    {
        $validated = $request->validate([
            'role_name' => 'required|exists:roles,name',
            'min_amount' => 'nullable|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        $matrix->update($validated);

        return back()->with('success', 'Matriks approval berhasil diperbarui.');
    }

    public function destroy(ApprovalMatrix $matrix)
    {
        $matrix->delete();
        return back()->with('success', 'Matriks approval berhasil dihapus.');
    }
}
