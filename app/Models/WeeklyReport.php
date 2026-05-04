<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class WeeklyReport extends Model
{
    use HasFactory, LogsActivity;

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_REJECTED = 'rejected';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'project_id',
        'week_number',
        'period_start',
        'period_end',
        'cover_title',
        'cover_image_id',
        'cover_image_path',
        'cumulative_data',
        'detail_data',
        'documentation_ids',
        'documentation_uploads',
        'activities',
        'problems',
        'status',
        'created_by',
        'submitted_by',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'cumulative_data' => 'array',
        'detail_data' => 'array',
        'documentation_ids' => 'array',
        'documentation_uploads' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // ========================
    // Relationships
    // ========================

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(ProjectFile::class, 'cover_image_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function approvalLogs()
    {
        return $this->morphMany(\App\Models\ApprovalLog::class, 'approvable');
    }

    // ========================
    // Accessors
    // ========================

    public function getCoverImageUrlAttribute(): ?string
    {
        // Priority: uploaded image path, then ProjectFile
        if ($this->cover_image_path) {
            return SystemSetting::getFileUrl($this->cover_image_path);
        }

        if ($this->coverImage && $this->coverImage->latestVersion) {
            return SystemSetting::getFileUrl($this->coverImage->latestVersion->file_path);
        }

        return null;
    }

    public function getDocumentationFilesAttribute(): array
    {
        $result = [];

        // From project files (documentation_ids)
        if ($this->documentation_ids && is_array($this->documentation_ids)) {
            $files = ProjectFile::whereIn('id', $this->documentation_ids)
                ->with('latestVersion')
                ->get();

            foreach ($files as $file) {
                $result[] = [
                    'id' => $file->id,
                    'name' => $file->name,
                    'url' => $file->latestVersion ? SystemSetting::getFileUrl($file->latestVersion->file_path) : null,
                    'source' => 'project_file',
                ];
            }
        }

        // From uploaded documentation photos
        if ($this->documentation_uploads && is_array($this->documentation_uploads)) {
            foreach ($this->documentation_uploads as $index => $path) {
                $result[] = [
                    'id' => 'upload_' . $index,
                    'name' => basename($path),
                    'url' => SystemSetting::getFileUrl($path),
                    'source' => 'upload',
                    'path' => $path,
                ];
            }
        }

        return $result;
    }

    public function getAllDocumentationPhotosAttribute(): array
    {
        return $this->documentation_files;
    }

    public function getPeriodLabelAttribute(): string
    {
        return $this->period_start->format('d M Y') . ' - ' . $this->period_end->format('d M Y');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_IN_REVIEW => 'Sedang Review',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_REJECTED => 'Ditolak',
            default => ucfirst($this->status ?? 'draft'),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
            self::STATUS_IN_REVIEW => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            self::STATUS_APPROVED => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
            self::STATUS_PUBLISHED => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            self::STATUS_REJECTED => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        };
    }

    /**
     * Check if the report can be edited (content changes).
     * Only draft and rejected reports can be edited.
     */
    public function getIsEditableAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }

    /**
     * Check if the report can be submitted for review.
     */
    public function getCanSubmitAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }

    /**
     * Check if the report can be approved.
     */
    public function getCanApproveAttribute(): bool
    {
        return $this->status === self::STATUS_IN_REVIEW;
    }

    /**
     * Check if the report can be published.
     */
    public function getCanPublishAttribute(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    // ========================
    // Scopes
    // ========================

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeByWeek($query, int $weekNumber)
    {
        return $query->where('week_number', $weekNumber);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeInReview($query)
    {
        return $query->where('status', self::STATUS_IN_REVIEW);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Visible to owner: only published reports.
     * Visible to team: all except draft (can see in_review, approved, published).
     */
    public function scopeVisibleTo($query, $user)
    {
        if ($user->hasRole('owner')) {
            return $query->where('status', self::STATUS_PUBLISHED);
        }

        return $query;
    }
}
