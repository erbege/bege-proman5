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
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'cumulative_data' => 'array',
        'detail_data' => 'array',
        'documentation_ids' => 'array',
        'documentation_uploads' => 'array',
    ];

    // Relationships
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

    // Accessors
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

    public function getPeriodLabelAttribute(): string
    {
        return $this->period_start->format('d M Y') . ' - ' . $this->period_end->format('d M Y');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'published' => 'Published',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'yellow',
            'published' => 'green',
            default => 'gray',
        };
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByWeek($query, int $weekNumber)
    {
        return $query->where('week_number', $weekNumber);
    }
}
