<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'week_number',
        'week_start',
        'week_end',
        'planned_weight',
        'actual_weight',
        'planned_cumulative',
        'actual_cumulative',
        'deviation',
    ];

    protected $casts = [
        'week_start' => 'date',
        'week_end' => 'date',
        'planned_weight' => 'decimal:4',
        'actual_weight' => 'decimal:4',
        'planned_cumulative' => 'decimal:4',
        'actual_cumulative' => 'decimal:4',
        'deviation' => 'decimal:4',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Accessors
    public function getWeekLabelAttribute(): string
    {
        return 'Minggu ' . $this->week_number;
    }

    public function getDeviationStatusAttribute(): string
    {
        if ($this->deviation > 0) {
            return 'ahead';
        } elseif ($this->deviation < 0) {
            return 'behind';
        }
        return 'on_track';
    }

    // Helper to recalculate deviation
    public function recalculateDeviation(): void
    {
        $this->deviation = $this->actual_cumulative - $this->planned_cumulative;
        $this->save();
    }
}
