<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequestItem extends Model
{
    protected $fillable = [
        'material_request_id',
        'material_id',
        'quantity',
        'ordered_quantity',
        'unit',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'ordered_quantity' => 'decimal:4',
    ];

    public function purchaseRequestItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function getRemainingToOrderAttribute(): float
    {
        return (float) max(0, $this->quantity - $this->ordered_quantity);
    }

    public function materialRequest()
    {
        return $this->belongsTo(MaterialRequest::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
