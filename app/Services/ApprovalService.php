<?php

namespace App\Services;

use App\Models\ApprovalLog;
use App\Models\ApprovalMatrix;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ApprovalService
{
    /**
     * Submit a document for approval.
     * Sets the max_approval_level based on the matrix.
     */
    public function submit(Model $model)
    {
        $docType = $this->getDocumentType($model);
        
        $maxLevel = ApprovalMatrix::where('document_type', $docType)
            ->where('is_active', true)
            ->when($docType === 'PO', function ($query) use ($model) {
                return $query->where('min_amount', '<=', $model->total_amount);
            })
            ->max('level') ?? 1;

        $model->update([
            'status' => 'pending',
            'current_approval_level' => 1,
            'max_approval_level' => $maxLevel,
            'is_fully_approved' => false
        ]);

        return $model;
    }

    /**
     * Approve a document at current level.
     */
    public function approve(Model $model, string $comment = null)
    {
        $user = Auth::user();
        $currentLevel = $model->current_approval_level;

        // Verify if user has the required role for this level
        $docType = $this->getDocumentType($model);
        $matrix = ApprovalMatrix::where('document_type', $docType)
            ->where('level', $currentLevel)
            ->where('is_active', true)
            ->first();

        if ($matrix && !$user->hasRole($matrix->role_name)) {
            throw new \Exception("User does not have the required role ({$matrix->role_name}) for level {$currentLevel}.");
        }

        // Record log
        ApprovalLog::create([
            'approvable_type' => get_class($model),
            'approvable_id' => $model->id,
            'level' => $currentLevel,
            'user_id' => $user->id,
            'status' => 'approved',
            'comment' => $comment,
        ]);

        if ($currentLevel >= $model->max_approval_level) {
            // Final Approval
            $model->update([
                'status' => 'approved',
                'is_fully_approved' => true,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
        } else {
            // Move to next level
            $model->update([
                'current_approval_level' => $currentLevel + 1
            ]);
        }

        return $model;
    }

    /**
     * Reject a document.
     */
    public function reject(Model $model, string $reason)
    {
        $user = Auth::user();

        ApprovalLog::create([
            'approvable_type' => get_class($model),
            'approvable_id' => $model->id,
            'level' => $model->current_approval_level,
            'user_id' => $user->id,
            'status' => 'rejected',
            'comment' => $reason,
        ]);

        $model->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        return $model;
    }

    private function getDocumentType(Model $model): string
    {
        return match (get_class($model)) {
            \App\Models\MaterialRequest::class => 'MR',
            \App\Models\PurchaseRequest::class => 'PR',
            \App\Models\PurchaseOrder::class => 'PO',
            default => 'UNKNOWN',
        };
    }
}
