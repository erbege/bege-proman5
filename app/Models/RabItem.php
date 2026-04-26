<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RabItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'rab_section_id',
        'ahsp_work_type_id',
        'code',
        'work_name',
        'description',
        'volume',
        'unit',
        'unit_price',
        'total_price',
        'weight_percentage',
        'planned_start',
        'planned_end',
        'actual_progress',
        'sort_order',
        'can_parallel',
        'is_analyzed',
        'source',
    ];

    protected $casts = [
        'volume' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'weight_percentage' => 'decimal:4',
        'planned_start' => 'date',
        'planned_end' => 'date',
        'actual_progress' => 'decimal:2',
        'can_parallel' => 'boolean',
        'is_analyzed' => 'boolean',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(RabSection::class, 'rab_section_id');
    }

    public function materialForecasts(): HasMany
    {
        return $this->hasMany(MaterialForecast::class);
    }

    public function progressReports(): HasMany
    {
        return $this->hasMany(ProgressReport::class);
    }

    public function materialUsages(): HasMany
    {
        return $this->hasMany(MaterialUsage::class);
    }

    public function ahspWorkType(): BelongsTo
    {
        return $this->belongsTo(AhspWorkType::class);
    }

    public function priceSnapshot(): HasOne
    {
        return $this->hasOne(AhspPriceSnapshot::class);
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->total_price = $model->volume * $model->unit_price;
        });
    }

    // Accessors
    public function getFullCodeAttribute(): string
    {
        if ($this->section) {
            $sectionCode = $this->section->full_code;

            // If code already contains section code prefix, return as is (database has full code)
            if (str_starts_with($this->code, $sectionCode . '.')) {
                return $this->code;
            }

            // Otherwise append suffix
            return $sectionCode . '.' . $this->code;
        }
        return $this->code;
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    public function getDurationDaysAttribute(): ?int
    {
        if ($this->planned_start && $this->planned_end) {
            return $this->planned_start->diffInDays($this->planned_end);
        }
        return null;
    }

    public function getRemainingProgressAttribute(): float
    {
        return 100 - $this->actual_progress;
    }

    // Scopes
    public function scopeWithSchedule($query)
    {
        return $query->whereNotNull('planned_start')
            ->whereNotNull('planned_end');
    }

    public function scopeNotAnalyzed($query)
    {
        return $query->where('is_analyzed', false);
    }
}
