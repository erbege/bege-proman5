<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressReportMaterial extends Model
{
    protected $fillable = [
        'progress_report_id',
        'material_id',
        'material_name',
        'quantity',
        'unit',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function progressReport(): BelongsTo
    {
        return $this->belongsTo(ProgressReport::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
