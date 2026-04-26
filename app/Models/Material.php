<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'category',
        'unit',
        'unit_price',
        'min_stock',
        'description',
        'is_active',
        'region_code',
        'region_name',
        'effective_date',
        'source',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_date' => 'date',
    ];

    // Relationships
    public function forecasts(): HasMany
    {
        return $this->hasMany(MaterialForecast::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function purchaseRequestItems(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function ahspBasePrice(): HasOne
    {
        return $this->hasOne(AhspBasePrice::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByRegion($query, string $regionCode)
    {
        return $query->where('region_code', $regionCode);
    }

    // Accessors
    public function getFormattedUnitPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->unit_price, 0, ',', '.');
    }

    public function getSourceLabelAttribute(): ?string
    {
        if ($this->source) {
            return $this->source;
        }
        return $this->region_name ? "AHSP - {$this->region_name}" : null;
    }
}

