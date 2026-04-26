<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RabSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'ahsp_category_id',
        'code',
        'name',
        'sort_order',
        'parent_id',
        'level',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function ahspCategory(): BelongsTo
    {
        return $this->belongsTo(AhspCategory::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(RabSection::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(RabSection::class, 'parent_id')
            ->orderByRaw("CAST(SUBSTRING_INDEX(code, '.', -1) AS UNSIGNED)");
    }

    public function recursiveChildren(): HasMany
    {
        return $this->children()->with(['recursiveChildren', 'items']);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RabItem::class)
            ->orderByRaw("CAST(SUBSTRING_INDEX(code, '.', -1) AS UNSIGNED)");
    }

    // Accessors
    public function getFullCodeAttribute(): string
    {
        if ($this->parent) {
            $parentCode = $this->parent->full_code;

            // If code already contains parent code prefix, return as is (database has full code)
            if (str_starts_with($this->code, $parentCode . '.')) {
                return $this->code;
            }

            // Otherwise append suffix
            return $parentCode . '.' . $this->code;
        }
        return $this->code;
    }

    public function getTotalPriceAttribute()
    {
        // Get items total - total_price is a DB column on rab_items
        if ($this->relationLoaded('items')) {
            $itemsTotal = $this->items->sum('total_price');
        } else {
            $itemsTotal = $this->items()->sum('total_price');
        }

        // Get children total - total_price is an accessor on RabSection, not a DB column
        // So we need to load children and sum their accessor values
        if ($this->relationLoaded('children')) {
            $childrenTotal = $this->children->sum(fn($child) => $child->total_price);
        } else {
            // Load children with their items to calculate their total_price
            $children = $this->children()->with('items')->get();
            $childrenTotal = $children->sum(fn($child) => $child->total_price);
        }

        return $itemsTotal + $childrenTotal;
    }

    public function getWeightPercentageAttribute()
    {
        // Get items weight - weight_percentage is a DB column on rab_items
        if ($this->relationLoaded('items')) {
            $itemsWeight = $this->items->sum('weight_percentage');
        } else {
            $itemsWeight = $this->items()->sum('weight_percentage');
        }

        // Get children weight - weight_percentage is an accessor on RabSection, not a DB column
        // So we need to load children and sum their accessor values
        if ($this->relationLoaded('children')) {
            $childrenWeight = $this->children->sum(fn($child) => $child->weight_percentage);
        } else {
            // Load children with their items to calculate their weight_percentage
            $children = $this->children()->with('items')->get();
            $childrenWeight = $children->sum(fn($child) => $child->weight_percentage);
        }

        return $itemsWeight + $childrenWeight;
    }
}
