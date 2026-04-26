<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'material_id',
        'quantity',
        'reserved_qty',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'reserved_qty' => 'decimal:4',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(InventoryLog::class)->orderByDesc('created_at');
    }

    // Accessors
    public function getAvailableQtyAttribute(): float
    {
        return $this->quantity - $this->reserved_qty;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity <= $this->material->min_stock;
    }

    // Methods
    public function addStock(float $qty, string $referenceType = null, int $referenceId = null, string $notes = null, int $userId = null): void
    {
        $this->quantity += $qty;
        $this->save();

        $this->logs()->create([
            'type' => 'in',
            'quantity' => $qty,
            'balance_after' => $this->quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'created_by' => $userId ?? auth()->id(),
        ]);
    }

    public function removeStock(float $qty, string $referenceType = null, int $referenceId = null, string $notes = null, int $userId = null): void
    {
        $this->quantity -= $qty;
        $this->save();

        $this->logs()->create([
            'type' => 'out',
            'quantity' => $qty,
            'balance_after' => $this->quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'created_by' => $userId ?? auth()->id(),
        ]);
    }
}
