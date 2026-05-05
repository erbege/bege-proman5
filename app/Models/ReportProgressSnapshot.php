<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportProgressSnapshot extends Model
{
    protected $fillable = [
        'report_type',
        'report_id',
        'rab_item_id',
        'planned_weight',
        'actual_weight',
        'deviation',
    ];

    protected $casts = [
        'planned_weight' => 'decimal:4',
        'actual_weight' => 'decimal:4',
        'deviation' => 'decimal:4',
    ];

    public function rabItem(): BelongsTo
    {
        return $this->belongsTo(RabItem::class);
    }

    public function report()
    {
        if ($this->report_type === 'weekly') {
            return $this->belongsTo(WeeklyReport::class, 'report_id');
        }
        return $this->belongsTo(MonthlyReport::class, 'report_id');
    }
}
