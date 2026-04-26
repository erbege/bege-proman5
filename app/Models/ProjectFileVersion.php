<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectFileVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_file_id',
        'version',
        'file_path',
        'disk',
        'file_size',
        'mime_type',
        'extension',
        'hash',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'file_size' => 'integer',
    ];

    // Relationships
    public function projectFile(): BelongsTo
    {
        return $this->belongsTo(ProjectFile::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Helpers
    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    public function getUrl(): ?string
    {
        return Storage::disk($this->disk)->url($this->file_path);
    }

    public function getTemporaryUrl(int $minutes = 30): ?string
    {
        if ($this->disk === 's3') {
            return Storage::disk($this->disk)->temporaryUrl($this->file_path, now()->addMinutes($minutes));
        }

        return $this->getUrl();
    }

    public function exists(): bool
    {
        return Storage::disk($this->disk)->exists($this->file_path);
    }

    public function getContents(): ?string
    {
        if ($this->exists()) {
            return Storage::disk($this->disk)->get($this->file_path);
        }

        return null;
    }

    public function isPreviewable(): bool
    {
        $previewable = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        return in_array(strtolower($this->extension), $previewable);
    }

    public function getIconAttribute(): string
    {
        return match (strtolower($this->extension)) {
            'pdf' => 'document-text',
            'doc', 'docx' => 'document',
            'xls', 'xlsx' => 'table-cells',
            'ppt', 'pptx' => 'presentation-chart-bar',
            'dwg', 'dxf' => 'cube',
            'skp' => 'cube-transparent',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'photo',
            'zip', 'rar', '7z' => 'archive-box',
            default => 'document',
        };
    }
}
