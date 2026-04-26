<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'folder_id',
        'name',
        'original_name',
        'type',
        'category',
        'status',
        'current_version',
        'is_final',
        'description',
        'uploaded_by',
    ];

    protected $casts = [
        'is_final' => 'boolean',
        'current_version' => 'integer',
    ];

    // Categories with labels
    public static array $categories = [
        'planning' => 'Perencanaan',
        'design' => 'Desain',
        'cad' => 'CAD/3D',
        'document' => 'Dokumen',
        'image' => 'Gambar',
        'other' => 'Lainnya',
    ];

    // Status with labels
    public static array $statuses = [
        'draft' => 'Draft',
        'review' => 'Dalam Review',
        'approved' => 'Disetujui',
        'final' => 'Final',
    ];

    // Status colors for badges
    public static array $statusColors = [
        'draft' => 'gray',
        'review' => 'yellow',
        'approved' => 'green',
        'final' => 'blue',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(ProjectFile::class, 'folder_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ProjectFile::class, 'folder_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ProjectFileVersion::class)->orderByDesc('version');
    }

    public function currentVersionFile(): HasOne
    {
        return $this->hasOne(ProjectFileVersion::class)
            ->where('version', $this->current_version);
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(ProjectFileVersion::class)->latestOfMany('version');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectFileComment::class)->whereNull('parent_id')->orderByDesc('created_at');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(ProjectFileComment::class)->orderByDesc('created_at');
    }

    // Helpers
    public function isFolder(): bool
    {
        return $this->type === 'folder';
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::$categories[$this->category] ?? $this->category;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::$statuses[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::$statusColors[$this->status] ?? 'gray';
    }

    public function canTransitionTo(string $status): bool
    {
        $transitions = [
            'draft' => ['review'],
            'review' => ['draft', 'approved'],
            'approved' => ['review', 'final'],
            'final' => ['approved'],
        ];

        return in_array($status, $transitions[$this->status] ?? []);
    }

    public function getUnresolvedCommentsCountAttribute(): int
    {
        return $this->allComments()->where('resolved', false)->count();
    }
}
