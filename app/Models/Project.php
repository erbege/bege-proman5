<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Project extends Model
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
        'code',
        'name',
        'description',
        'client_name',
        'type',
        'start_date',
        'end_date',
        'contract_value',
        'status',
        'location',
        'notes',
        'created_by',
        'owner_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_value' => 'decimal:2',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function rabSections(): HasMany
    {
        return $this->hasMany(RabSection::class)->orderBy('sort_order');
    }

    public function rabItems(): HasMany
    {
        return $this->hasMany(RabItem::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ProjectSchedule::class)->orderBy('week_number');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function materialRequests(): HasMany
    {
        return $this->hasMany(MaterialRequest::class);
    }

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function progressReports(): HasMany
    {
        return $this->hasMany(ProgressReport::class);
    }

    /**
     * Alias used by scoped route model binding for {report} parameter.
     */
    public function reports(): HasMany
    {
        return $this->progressReports();
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function materialUsages(): HasMany
    {
        return $this->hasMany(MaterialUsage::class);
    }

    public function projectFiles(): HasMany
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function team(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_team')
            ->withPivot('role', 'assigned_from', 'assigned_until', 'is_active')
            ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Accessors
    public function getFormattedContractValueAttribute(): string
    {
        return 'Rp ' . number_format($this->contract_value, 2, ',', '.');
    }

    public function getDurationDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    public function getDurationWeeksAttribute(): int
    {
        return (int) ceil($this->duration_days / 7);
    }

    /**
     * Calculate total project progress based on weighted average of RAB items
     * Progress is calculated as: sum(item_progress * item_weight) / total_weight
     */
    public function getTotalProgressAttribute(): float
    {
        $items = $this->rabItems()->whereNotNull('weight_percentage')->get();

        if ($items->isEmpty()) {
            return 0;
        }

        $totalWeight = $items->sum('weight_percentage');

        if ($totalWeight <= 0) {
            return 0;
        }

        $weightedProgress = $items->sum(function ($item) {
            return ($item->actual_progress ?? 0) * ($item->weight_percentage ?? 0);
        });

        return round($weightedProgress / $totalWeight, 2);
    }

    // Helper methods
    public function calculateTotalWeight(): void
    {
        $totalPrice = $this->rabItems()->sum('total_price');

        if ($totalPrice > 0) {
            foreach ($this->rabItems as $item) {
                $item->update([
                    'weight_percentage' => ($item->total_price / $totalPrice) * 100
                ]);
            }
        }
    }
}
