<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'material_id',
        'quantity',
        'estimated_price',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'estimated_price' => 'decimal:2',
    ];

    // Relationships
    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    // Accessors
    public function getTotalPriceAttribute(): float
    {
        return $this->quantity * $this->estimated_price;
    }
}
