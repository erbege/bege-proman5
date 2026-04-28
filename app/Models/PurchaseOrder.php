<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'po_number',
        'purchase_request_id',
        'project_id',
        'supplier_id',
        'order_date',
        'expected_delivery',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'payment_terms',
        'notes',
        'created_by',
        'current_approval_level',
        'max_approval_level',
        'is_fully_approved',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Relationships
    public function purchaseRequests(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(PurchaseRequest::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function approvalLogs(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(ApprovalLog::class, 'approvable');
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->po_number)) {
                $model->po_number = static::generateNumber();
            }
        });

        static::updating(function ($model) {
            // Prevent editing core fields if already approved
            if ($model->getOriginal('status') === 'approved' || $model->getOriginal('is_fully_approved')) {
                // Fields that should NOT change after approval
                $restrictedFields = [
                    'project_id', 'supplier_id', 'po_number', 
                    'order_date', 'subtotal', 'tax_amount', 
                    'discount_amount', 'total_amount'
                ];
                
                foreach ($restrictedFields as $field) {
                    if ($model->isDirty($field)) {
                        throw new \Exception("Dokumen yang sudah disetujui tidak dapat diedit.");
                    }
                }
            }
        });
    }

    public static function generateNumber(): string
    {
        return DocumentSequence::next('PO');
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'sent' => 'Dikirim',
            'partial' => 'Diterima Sebagian',
            'received' => 'Diterima',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    // Methods
    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('total_price');
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->save();
    }

    public function updateReceiveStatus(): void
    {
        $totalItems = $this->items->count();
        $fullyReceived = $this->items->where('received_qty', '>=', $this->items->pluck('quantity'))->count();
        $partialReceived = $this->items->where('received_qty', '>', 0)->count();

        if ($fullyReceived === $totalItems) {
            $this->status = 'received';
        } elseif ($partialReceived > 0) {
            $this->status = 'partial';
        }

        $this->save();
    }
}
