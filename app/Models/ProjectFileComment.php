<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectFileComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_file_id',
        'version_id',
        'user_id',
        'parent_id',
        'comment',
        'resolved',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function projectFile(): BelongsTo
    {
        return $this->belongsTo(ProjectFile::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ProjectFileVersion::class, 'version_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProjectFileComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ProjectFileComment::class, 'parent_id')->orderBy('created_at');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Helpers
    public function resolve(int $userId): void
    {
        $this->update([
            'resolved' => true,
            'resolved_by' => $userId,
            'resolved_at' => now(),
        ]);
    }

    public function unresolve(): void
    {
        $this->update([
            'resolved' => false,
            'resolved_by' => null,
            'resolved_at' => null,
        ]);
    }

    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }
}
