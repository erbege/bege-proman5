<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * Get a setting value by key
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("setting_{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * Set a setting value
     */
    public static function setValue(string $key, mixed $value): void
    {
        $setting = static::where('key', $key)->first();

        if ($setting) {
            if ($setting->type === 'json') {
                $value = json_encode($value);
            } elseif ($setting->type === 'boolean') {
                $value = $value ? '1' : '0';
            }

            $setting->update(['value' => $value]);
            Cache::forget("setting_{$key}");
        }
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup(string $group): array
    {
        return static::where('group', $group)
            ->get()
            ->mapWithKeys(fn($s) => [$s->key => static::getValue($s->key)])
            ->toArray();
    }

    /**
     * Cached storage disk value for current request
     */
    protected static ?string $cachedDisk = null;

    /**
     * Get storage disk based on settings (with in-memory caching)
     */
    public static function getStorageDisk(): string
    {
        // Return cached value if available (avoid repeated lookups)
        if (static::$cachedDisk !== null) {
            return static::$cachedDisk;
        }

        $driver = static::getValue('storage_driver', 'public');
        // Map 'local' to 'public' for backward compatibility (photos are in public disk)
        static::$cachedDisk = $driver === 'local' ? 'public' : $driver;

        return static::$cachedDisk;
    }

    /**
     * Get max file size in bytes
     */
    public static function getMaxFileSize(): int
    {
        return (int) static::getValue('max_file_size', 52428800);
    }

    /**
     * Get photo/file URL based on storage driver
     * Uses temporaryUrl for S3, relative URL for local/public storage
     */
    public static function getFileUrl(string $path, int $expirationMinutes = 60): string
    {
        $disk = static::getStorageDisk();

        if ($disk === 's3') {
            try {
                return \Illuminate\Support\Facades\Storage::disk('s3')
                    ->temporaryUrl($path, now()->addMinutes($expirationMinutes));
            } catch (\Exception $e) {
                // Fallback to regular URL if temporaryUrl fails
                return \Illuminate\Support\Facades\Storage::disk('s3')->url($path);
            }
        }

        // Local/public storage - use absolute URL for better compatibility across different contexts (broadcasts, etc)
        return asset('storage/' . $path);
    }

    /**
     * Check if current storage is S3
     */
    public static function isS3Storage(): bool
    {
        return static::getStorageDisk() === 's3';
    }
}
