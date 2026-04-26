<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AhspPriceSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'rab_item_id',
        'ahsp_work_type_id',
        'region_code',
        'labor_cost',
        'material_cost',
        'equipment_cost',
        'subtotal',
        'overhead_percentage',
        'overhead_cost',
        'unit_price',
        'components_data',
    ];

    protected $casts = [
        'labor_cost' => 'decimal:2',
        'material_cost' => 'decimal:2',
        'equipment_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'overhead_percentage' => 'decimal:2',
        'overhead_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'components_data' => 'array',
    ];

    // Relationships
    public function rabItem(): BelongsTo
    {
        return $this->belongsTo(RabItem::class);
    }

    public function workType(): BelongsTo
    {
        return $this->belongsTo(AhspWorkType::class, 'ahsp_work_type_id');
    }

    /**
     * Create snapshot from work type calculation
     */
    public static function createFromCalculation(
        RabItem $rabItem,
        AhspWorkType $workType,
        string $regionCode,
        array $calculation
    ): self {
        return static::create([
            'rab_item_id' => $rabItem->id,
            'ahsp_work_type_id' => $workType->id,
            'region_code' => $regionCode,
            'labor_cost' => $calculation['labor_cost'],
            'material_cost' => $calculation['material_cost'],
            'equipment_cost' => $calculation['equipment_cost'],
            'subtotal' => $calculation['subtotal'],
            'overhead_percentage' => $calculation['overhead_percentage'],
            'overhead_cost' => $calculation['overhead_cost'],
            'unit_price' => $calculation['unit_price'],
            'components_data' => $calculation['breakdown'],
        ]);
    }

    // Get formatted prices
    public function getFormattedUnitPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->unit_price, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }
}
