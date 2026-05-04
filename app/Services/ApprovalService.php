<?php

namespace App\Services;

use App\Models\ApprovalLog;
use App\Models\ApprovalMatrix;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ApprovalService
{
    /**
     * Roles that can bypass/approve any level.
     */
    protected const BYPASS_ROLES = ['Superadmin', 'super-admin', 'administrator'];

    protected function hasBypassRole($user): bool
    {
        $roleNames = $user->roles->pluck('name')->all();
        return count(array_intersect($roleNames, self::BYPASS_ROLES)) > 0;
    }
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
                return $query->where('min_amount', '<=', $model->total_amount)
                             ->where(function($q) use ($model) {
                                 $q->whereNull('max_amount')
                                   ->orWhere('max_amount', '>=', $model->total_amount);
                             });
            })
            ->max('level') ?? 1;

        $model->update([
            'status' => 'pending',
            'current_approval_level' => 1,
            'max_approval_level' => $maxLevel,
            'is_fully_approved' => false
        ]);

        // Notify next level approvers
        $this->notifyApprovers($model);

        return $model;
    }

    /**
     * Approve a document at current level.
     */
    public function approve(Model $model, ?string $comment = null)
    {
        return \Illuminate\Support\Facades\DB::transaction(function() use ($model, $comment) {
            $user = Auth::user();
            // Refresh to ensure we have the latest state before checks
            $model->refresh();
            if ($model->status !== 'pending') {
                throw new \Exception('Request is not pending');
            }
            
            $currentLevel = (int) $model->current_approval_level;
            if ($currentLevel <= 0) {
                $currentLevel = 1;
            }
            $maxLevel = (int) $model->max_approval_level;
            if ($maxLevel <= 0) {
                $maxLevel = $currentLevel;
            }

            // Verify if user has the required role for this level
            $docType = $this->getDocumentType($model);
            $matrix = ApprovalMatrix::where('document_type', $docType)
                ->where('level', $currentLevel)
                ->where('is_active', true)
                ->first();

            if ($matrix && !$this->canUserApprove($user, $matrix)) {
                throw new \Exception("User does not have the required role ({$matrix->role_name}) for level {$currentLevel}.");
            }

            // Record log
            \App\Models\ApprovalLog::create([
                'approvable_type' => get_class($model),
                'approvable_id' => $model->id,
                'level' => $currentLevel,
                'user_id' => $user->id,
                'status' => 'approved',
                'comment' => $comment,
            ]);

            if ($currentLevel >= $maxLevel) {
                // Final Approval
                $model->update([
                    'status' => 'approved',
                    'is_fully_approved' => true,
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);

                // Notify requester about final approval
                $this->notifyRequester($model, 'approved');
            } else {
                // Move to next level
                $model->update([
                    'current_approval_level' => $currentLevel + 1
                ]);

                // Notify requester about progress (intermediate approval)
                $this->notifyRequester($model, 'level_approved');

                // Notify next level approvers
                $this->notifyApprovers($model->refresh());
            }

            return $model;
        });
    }

    /**
     * Reject a document.
     */
    public function reject(Model $model, string $reason)
    {
        return \Illuminate\Support\Facades\DB::transaction(function() use ($model, $reason) {
            $user = Auth::user();
            $model->refresh();
            if ($model->status !== 'pending') {
                throw new \Exception('Request is not pending');
            }
            $currentLevel = (int) $model->current_approval_level;
            if ($currentLevel <= 0) {
                $currentLevel = 1;
            }

            // Verify if user has the required role for this level
            $docType = $this->getDocumentType($model);
            $matrix = ApprovalMatrix::where('document_type', $docType)
                ->where('level', $currentLevel)
                ->where('is_active', true)
                ->first();

            if ($matrix && !$this->canUserApprove($user, $matrix)) {
                throw new \Exception("User does not have the required role ({$matrix->role_name}) to reject at level {$currentLevel}.");
            }

            \App\Models\ApprovalLog::create([
                'approvable_type' => get_class($model),
                'approvable_id' => $model->id,
                'level' => $currentLevel,
                'user_id' => $user->id,
                'status' => 'rejected',
                'comment' => $reason,
            ]);

            $model->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
            ]);

            // Notify requester about rejection
            $this->notifyRequester($model, 'rejected');

            return $model;
        });
    }

    /**
     * Notify users responsible for the current approval level.
     */
    protected function notifyApprovers(Model $model): void
    {
        $docType = $this->getDocumentType($model);
        $currentLevel = (int) $model->current_approval_level;

        $matrix = ApprovalMatrix::where('document_type', $docType)
            ->where('level', $currentLevel)
            ->where('is_active', true)
            ->first();

        if (!$matrix) return;

        // Targeted Notification: Only notify users who are in the project team AND have the required role
        $project = $model->project;
        $approversInTeam = $project->team()
            ->whereHas('roles', function($q) use ($matrix) {
                $q->where('name', $matrix->role_name);
            })->get();

        // Also notify super-admins/admins regardless of project membership for oversight
        $admins = collect();
        try {
            $admins = \App\Models\User::role(['super-admin', 'Superadmin', 'administrator'])->get();
        } catch (\Throwable $e) {
            $admins = collect();
        }
        
        $recipients = $approversInTeam->merge($admins)->unique('id');
        
        foreach ($recipients as $recipient) {
            $notification = null;
            if ($model instanceof \App\Models\MaterialRequest) {
                $notification = new \App\Notifications\MaterialRequestCreatedNotification($model);
            } elseif ($model instanceof \App\Models\PurchaseRequest) {
                $notification = new \App\Notifications\PurchaseRequestCreatedNotification($model);
            } elseif ($model instanceof \App\Models\GoodsReceipt) {
                $notification = new \App\Notifications\GoodsReceiptNotification($model);
            } elseif ($model instanceof \App\Models\PurchaseOrder) {
                $notification = new \App\Notifications\PurchaseOrderCreatedNotification($model);
            }

            if ($notification) {
                // Ensure we don't notify the person who just approved it (if they are also an admin)
                if (Auth::check() && Auth::id() === $recipient->id) continue;
                
                NotificationHelper::sendToUser($recipient, $notification, false); // false = don't merge admins again
            }
        }
    }

    /**
     * Notify the requester about a status update.
     */
    protected function notifyRequester(Model $model, string $status): void
    {
        $requester = null;
        if ($model instanceof \App\Models\MaterialRequest) {
            $model->loadMissing('requestedBy');
            $requester = $model->requestedBy;
        } elseif ($model instanceof \App\Models\PurchaseRequest) {
            $model->loadMissing('requestedBy');
            $requester = $model->requestedBy;
        } elseif ($model instanceof \App\Models\GoodsReceipt) {
            $model->loadMissing('receivedBy');
            $requester = $model->receivedBy;
        } elseif ($model instanceof \App\Models\PurchaseOrder) {
            $model->loadMissing('createdBy');
            $requester = $model->createdBy;
        }

        if ($requester) {
            $notification = null;
            if ($model instanceof \App\Models\MaterialRequest) {
                $notification = new \App\Notifications\MaterialRequestStatusNotification($model, $status);
            } elseif ($model instanceof \App\Models\PurchaseRequest) {
                $notification = new \App\Notifications\PurchaseRequestStatusNotification($model, $status);
            } elseif ($model instanceof \App\Models\GoodsReceipt) {
                $notification = new \App\Notifications\GoodsReceiptNotification($model);
            } elseif ($model instanceof \App\Models\PurchaseOrder) {
                $notification = new \App\Notifications\PurchaseOrderStatusNotification($model, $status);
            }

            if ($notification) {
                NotificationHelper::sendToUser($requester, $notification);
            }
        }
    }

    private function getDocumentType(Model $model): string
    {
        return match (get_class($model)) {
            \App\Models\MaterialRequest::class => 'MR',
            \App\Models\PurchaseRequest::class => 'PR',
            \App\Models\PurchaseOrder::class => 'PO',
            \App\Models\GoodsReceipt::class => 'GR',
            default => 'UNKNOWN',
        };
    }

    /**
     * Check if a user can approve for a given matrix level.
     */
    public function canUserApprove($user, $matrix): bool
    {
        if (!$user || !$matrix) return false;

        // Check if user has the specific role OR is in the bypass list
        return $user->hasRole($matrix->role_name) || $this->hasBypassRole($user);
    }
}
