<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'rab_item_id',
        'report_date',
        'progress_percentage',
        'cumulative_progress',
        'description',
        'issues',
        'photos',
        'weather',
        'workers_count',
        'labor_details',
        'reported_by',
    ];

    protected $casts = [
        'report_date' => 'date',
        'progress_percentage' => 'decimal:2',
        'cumulative_progress' => 'decimal:2',
        'photos' => 'array',
        'labor_details' => 'array',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function rabItem(): BelongsTo
    {
        return $this->belongsTo(RabItem::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function reporter(): BelongsTo
    {
        return $this->reportedBy();
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            // Update RAB item actual progress
            if ($model->rabItem) {
                $model->rabItem->update([
                    'actual_progress' => $model->cumulative_progress
                ]);
            }
        });

        static::deleted(function ($model) {
            // Recalculate RAB item actual progress based on remaining reports
            if ($model->rabItem) {
                $latestReport = $model->rabItem->progressReports()
                    ->orderByDesc('report_date')
                    ->orderByDesc('id')
                    ->first();

                $model->rabItem->update([
                    'actual_progress' => $latestReport ? $latestReport->cumulative_progress : 0
                ]);
            }
        });
    }

    // Accessors
    public function getWeatherLabelAttribute(): string
    {
        return match ($this->weather) {
            'sunny' => 'Cerah',
            'cloudy' => 'Berawan',
            'rainy' => 'Hujan',
            'stormy' => 'Badai',
            default => $this->weather ?? '-',
        };
    }

    // Scopes
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('report_date', [$startDate, $endDate]);
    }

    /**
     * Get photo URLs with proper storage handling (S3 temporaryUrl or local asset)
     */
    public function getPhotoUrlsAttribute(): array
    {
        if (!$this->photos || !is_array($this->photos)) {
            return [];
        }

        return array_map(function ($photo) {
            return SystemSetting::getFileUrl($photo);
        }, $this->photos);
    }
}
