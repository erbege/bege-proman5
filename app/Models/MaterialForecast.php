<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialForecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'rab_item_id',
        'material_id',
        'raw_material_name',
        'estimated_qty',
        'unit',
        'coefficient',
        'match_score',
        'analysis_source',
        'ai_response_raw',
        'notes',
    ];

    protected $casts = [
        'estimated_qty' => 'decimal:4',
        'coefficient' => 'decimal:6',
        'ai_response_raw' => 'array',
        'match_score' => 'integer',
    ];

    // Relationships
    public function rabItem(): BelongsTo
    {
        return $this->belongsTo(RabItem::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function getStatusColorAttribute(): string
    {
        if (is_null($this->material_id)) {
            return 'red'; // Danger / Unknown
        }

        if ($this->match_score < 75) {
            return 'yellow'; // Warning / Low Confidence
        }

        return 'green'; // Success / High Confidence
    }

    // Scopes
    public function scopeBySource($query, string $source)
    {
        return $query->where('analysis_source', $source);
    }

    public function scopeAiGenerated($query)
    {
        return $query->where('analysis_source', 'ai');
    }

    public function scopeManual($query)
    {
        return $query->where('analysis_source', 'manual');
    }
}
