<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AhspBasePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id',
        'name',
        'category',
        'code',
        'component_type',
        'unit',
        'region_code',
        'region_name',
        'price',
        'effective_date',
        'source',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'effective_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(AhspPriceHistory::class);
    }

    /**
     * Get latest price for a component by name and region
     */
    public static function getLatestPrice(string $name, string $regionCode, string $componentType = null): ?float
    {
        $query = static::where('name', $name)
            ->where('region_code', $regionCode)
            ->where('is_active', true);

        if ($componentType) {
            $query->where('component_type', $componentType);
        }

        $basePrice = $query->orderByDesc('effective_date')->first();

        return $basePrice ? (float) $basePrice->price : null;
    }

    /**
     * Get all available regions
     */
    public static function getRegions(): array
    {
        return static::select('region_code', 'region_name')
            ->distinct()
            ->orderBy('region_name')
            ->get()
            ->mapWithKeys(fn($item) => [$item->region_code => $item->region_name ?: $item->region_code])
            ->toArray();
    }

    // Get formatted price
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    // Get component type label in Indonesian
    public function getTypeLabelAttribute(): string
    {
        return match ($this->component_type) {
            'labor' => 'Tenaga Kerja',
            'material' => 'Bahan',
            'equipment' => 'Peralatan',
            default => $this->component_type,
        };
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRegion($query, string $regionCode)
    {
        return $query->where('region_code', $regionCode);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('component_type', $type);
    }

    public function scopeLabor($query)
    {
        return $query->where('component_type', 'labor');
    }

    public function scopeMaterial($query)
    {
        return $query->where('component_type', 'material');
    }

    public function scopeEquipment($query)
    {
        return $query->where('component_type', 'equipment');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%");
        });
    }
}
