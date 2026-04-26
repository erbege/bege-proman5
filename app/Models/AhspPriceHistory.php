<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AhspPriceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'ahsp_base_price_id',
        'old_price',
        'new_price',
        'changed_by',
        'reason',
    ];

    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
    ];

    // Relationships
    public function basePrice(): BelongsTo
    {
        return $this->belongsTo(AhspBasePrice::class, 'ahsp_base_price_id');
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // Get price change percentage
    public function getChangePercentageAttribute(): float
    {
        if ($this->old_price == 0) {
            return 100;
        }
        return round((($this->new_price - $this->old_price) / $this->old_price) * 100, 2);
    }

    // Get formatted old price
    public function getFormattedOldPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->old_price, 0, ',', '.');
    }

    // Get formatted new price
    public function getFormattedNewPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->new_price, 0, ',', '.');
    }
}
