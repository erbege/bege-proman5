<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProgressReport extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Progress Report has been {$eventName}");
    }

    protected $fillable = [
        'project_id',
        'rab_item_id',
        'report_date',
        'report_code',
        'progress_percentage',
        'cumulative_progress',
        'description',
        'issues',
        'photos',
        'weather',
        'weather_duration',
        'workers_count',
        'labor_details',
        'equipment_details',
        'material_usage_summary',
        'safety_details',
        'next_day_plan',
        'reported_by',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'rejected_by',
        'rejected_at',
        'rejected_notes',
        'published_by',
        'published_at',
    ];

    protected $casts = [
        'report_date' => 'date',
        'report_code' => 'string',
        'progress_percentage' => 'decimal:2',
        'cumulative_progress' => 'decimal:2',
        'photos' => 'array',
        'labor_details' => 'array',
        'equipment_details' => 'array',
        'material_usage_summary' => 'array',
        'safety_details' => 'array',
        'reviewed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_REVIEWED = 'reviewed';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_PUBLISHED = 'published';

    public const DOC_TYPE = 'PROGRESS_REPORT';

    // ========================
    // Relationships
    // ========================

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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function progressReportMaterials(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProgressReportMaterial::class);
    }

    // ========================
    // Auto Numbering
    // ========================

    public static function boot()
    {
        parent::boot();

        static::creating(function (ProgressReport $report) {
            if (empty($report->report_code)) {
                $report->report_code = $report->generateReportCode();
            }
        });
    }

    public function generateReportCode(): string
    {
        $year = now()->year;
        $prefix = "LHP-{$year}-";
        $seq = DocumentSequence::getNext(self::DOC_TYPE, $prefix, $this->project_id);

        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    // ========================
    // Accessors
    // ========================

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

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Diajukan',
            self::STATUS_REVIEWED => 'Diverifikasi',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_PUBLISHED => 'Dipublikasikan',
            default => ucfirst($this->status ?? 'draft'),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_SUBMITTED => 'blue',
            self::STATUS_REVIEWED => 'green',
            self::STATUS_REJECTED => 'red',
            self::STATUS_PUBLISHED => 'purple',
            default => 'gray',
        };
    }

    public function getIsEditableAttribute(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function getCanSubmitAttribute(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function getCanReviewAttribute(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function getCanApproveAttribute(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function getCanRejectAttribute(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function getCanPublishAttribute(): bool
    {
        return $this->status === self::STATUS_REVIEWED;
    }

    public function getCanDeleteAttribute(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function getPhotoUrlsAttribute(): array
    {
        if (! $this->photos || ! is_array($this->photos)) {
            return [];
        }

        return array_map(fn($photo) => SystemSetting::getFileUrl($photo), $this->photos);
    }

    public function getHasEquipmentAttribute(): bool
    {
        return ! empty($this->equipment_details) && count($this->equipment_details) > 0;
    }

    public function getHasMaterialUsageAttribute(): bool
    {
        return ! empty($this->material_usage_summary) && count($this->material_usage_summary) > 0;
    }

    public function getHasSafetyDataAttribute(): bool
    {
        return ! empty($this->safety_details);
    }

    public function getSafetyIncidentCountAttribute(): int
    {
        if (! $this->safety_details) {
            return 0;
        }

        return ($this->safety_details['incidents'] ?? 0) + ($this->safety_details['near_miss'] ?? 0);
    }

    public function getEquipmentCountAttribute(): int
    {
        if (! $this->equipment_details) {
            return 0;
        }

        return array_sum(array_column($this->equipment_details, 'qty'));
    }

    // ========================
    // Scopes
    // ========================

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('report_date', [$startDate, $endDate]);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeReviewed($query)
    {
        return $query->where('status', self::STATUS_REVIEWED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }
}
