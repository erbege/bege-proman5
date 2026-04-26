<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AhspWorkType extends Model
{
    use HasFactory;

    protected $fillable = [
        'ahsp_category_id',
        'code',
        'name',
        'unit',
        'description',
        'source',
        'reference',
        'overhead_percentage',
        'is_active',
    ];

    protected $casts = [
        'overhead_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(AhspCategory::class, 'ahsp_category_id');
    }

    public function components(): HasMany
    {
        return $this->hasMany(AhspComponent::class)->orderBy('component_type')->orderBy('sort_order');
    }

    public function laborComponents(): HasMany
    {
        return $this->hasMany(AhspComponent::class)->where('component_type', 'labor')->orderBy('sort_order');
    }

    public function materialComponents(): HasMany
    {
        return $this->hasMany(AhspComponent::class)->where('component_type', 'material')->orderBy('sort_order');
    }

    public function equipmentComponents(): HasMany
    {
        return $this->hasMany(AhspComponent::class)->where('component_type', 'equipment')->orderBy('sort_order');
    }

    public function rabItems(): HasMany
    {
        return $this->hasMany(RabItem::class);
    }

    public function priceSnapshots(): HasMany
    {
        return $this->hasMany(AhspPriceSnapshot::class);
    }

    /**
     * Calculate unit price for a specific region
     * Returns breakdown of costs
     */
    public function calculateUnitPrice(string $regionCode): array
    {
        $laborCost = 0;
        $materialCost = 0;
        $equipmentCost = 0;
        $breakdown = [];

        foreach ($this->components as $component) {
            $price = $component->getPrice($regionCode);
            $amount = $component->coefficient * $price;

            $breakdown[] = [
                'id' => $component->id,
                'type' => $component->component_type,
                'code' => $component->code,
                'name' => $component->name,
                'unit' => $component->unit,
                'coefficient' => $component->coefficient,
                'price' => $price,
                'amount' => $amount,
            ];

            match ($component->component_type) {
                'labor' => $laborCost += $amount,
                'material' => $materialCost += $amount,
                'equipment' => $equipmentCost += $amount,
            };
        }

        $subtotal = $laborCost + $materialCost + $equipmentCost;
        $overheadCost = $subtotal * ($this->overhead_percentage / 100);
        $unitPrice = $subtotal + $overheadCost;

        return [
            'labor_cost' => round($laborCost, 2),
            'material_cost' => round($materialCost, 2),
            'equipment_cost' => round($equipmentCost, 2),
            'subtotal' => round($subtotal, 2),
            'overhead_percentage' => $this->overhead_percentage,
            'overhead_cost' => round($overheadCost, 2),
            'unit_price' => round($unitPrice, 2),
            'breakdown' => $breakdown,
        ];
    }

    // Get full code including category path
    public function getFullCodeAttribute(): string
    {
        return $this->code;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('ahsp_category_id', $categoryId);
    }

    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%");
        });
    }
}
