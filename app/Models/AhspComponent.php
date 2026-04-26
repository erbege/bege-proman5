<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AhspComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'ahsp_work_type_id',
        'component_type',
        'code',
        'name',
        'unit',
        'coefficient',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'coefficient' => 'decimal:6',
    ];

    const TYPE_LABOR = 'labor';
    const TYPE_MATERIAL = 'material';
    const TYPE_EQUIPMENT = 'equipment';

    // Relationships
    public function workType(): BelongsTo
    {
        return $this->belongsTo(AhspWorkType::class, 'ahsp_work_type_id');
    }

    /**
     * Get price for this component in a specific region
     */
    public function getPrice(string $regionCode): float
    {
        // Helper to query with case resilience
        $query = function ($field, $value) use ($regionCode) {
            return AhspBasePrice::where($field, $value)
                ->where(function ($q) {
                    $q->where('component_type', $this->component_type)
                        ->orWhere('component_type', ucfirst($this->component_type))
                        ->orWhere('component_type', strtoupper($this->component_type));
                })
                ->where('is_active', true)
                ->when($regionCode, fn($q) => $q->where('region_code', $regionCode))
                ->orderByDesc('effective_date');
        };

        // 1. Try Name + Region
        $basePrice = $query('name', $this->name)->first();

        // 2. Try Code + Region
        if (!$basePrice && $this->code) {
            $basePrice = $query('code', $this->code)->first();
        }

        // 3. Fallback: Name (Any Region)
        if (!$basePrice) {
            $basePrice = AhspBasePrice::where('name', $this->name)
                ->where(function ($q) {
                    $q->where('component_type', $this->component_type)
                        ->orWhere('component_type', ucfirst($this->component_type))
                        ->orWhere('component_type', strtoupper($this->component_type));
                })
                ->where('is_active', true)
                ->orderByDesc('effective_date')
                ->first();
        }

        return $basePrice ? (float) $basePrice->price : 0;
    }

    /**
     * Calculate amount (coefficient × price)
     */
    public function calculateAmount(string $regionCode): float
    {
        return $this->coefficient * $this->getPrice($regionCode);
    }

    // Get component type label in Indonesian
    public function getTypeLabelAttribute(): string
    {
        return match ($this->component_type) {
            self::TYPE_LABOR => 'Tenaga Kerja',
            self::TYPE_MATERIAL => 'Bahan',
            self::TYPE_EQUIPMENT => 'Peralatan',
            default => $this->component_type,
        };
    }

    // Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('component_type', $type);
    }

    public function scopeLabor($query)
    {
        return $query->where('component_type', self::TYPE_LABOR);
    }

    public function scopeMaterial($query)
    {
        return $query->where('component_type', self::TYPE_MATERIAL);
    }

    public function scopeEquipment($query)
    {
        return $query->where('component_type', self::TYPE_EQUIPMENT);
    }
}
