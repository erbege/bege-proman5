<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImageResizeService
{
    /**
     * Maximum dimensions for landscape images
     */
    protected const MAX_LANDSCAPE_WIDTH = 640;
    protected const MAX_LANDSCAPE_HEIGHT = 480;

    /**
     * Maximum dimensions for portrait images
     */
    protected const MAX_PORTRAIT_WIDTH = 480;
    protected const MAX_PORTRAIT_HEIGHT = 640;

    /**
     * Maximum file size in KB
     */
    protected const MAX_FILE_SIZE_KB = 250;

    /**
     * Process and resize an uploaded image
     * Save as WebP format with optimized size
     *
     * @param UploadedFile|TemporaryUploadedFile $file
     * @param string $directory
     * @param string $disk
     * @return string|null Path to saved file
     */
    public function processAndSave($file, string $directory, string $disk = 'public'): ?string
    {
        try {
            // Read the image
            $image = Image::read($file->getRealPath());

            // Get original dimensions
            $width = $image->width();
            $height = $image->height();

            // Determine if landscape or portrait
            $isLandscape = $width >= $height;

            // Calculate target dimensions
            if ($isLandscape) {
                $maxWidth = self::MAX_LANDSCAPE_WIDTH;
                $maxHeight = self::MAX_LANDSCAPE_HEIGHT;
            } else {
                $maxWidth = self::MAX_PORTRAIT_WIDTH;
                $maxHeight = self::MAX_PORTRAIT_HEIGHT;
            }

            // Only resize if image is larger than max dimensions
            if ($width > $maxWidth || $height > $maxHeight) {
                $image->scaleDown($maxWidth, $maxHeight);
            }

            // Generate unique filename with webp extension
            $filename = uniqid() . '_' . time() . '.webp';
            $path = trim($directory, '/') . '/' . $filename;

            // Start with quality 85 and reduce if file size is too large
            $quality = 85;
            $minQuality = 30;
            $encoded = null;

            while ($quality >= $minQuality) {
                $encoded = $image->toWebp($quality);
                $sizeKB = strlen($encoded->toString()) / 1024;

                if ($sizeKB <= self::MAX_FILE_SIZE_KB) {
                    break;
                }

                // Reduce quality by 10%
                $quality -= 10;
            }

            // Save to storage
            Storage::disk($disk)->put($path, $encoded->toString());

            return $path;
        } catch (\Exception $e) {
            \Log::error('Image resize failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Process multiple uploaded images
     *
     * @param array $files
     * @param string $directory
     * @param string $disk
     * @return array Array of saved paths
     */
    public function processMultiple(array $files, string $directory, string $disk = 'public'): array
    {
        $paths = [];

        foreach ($files as $file) {
            $path = $this->processAndSave($file, $directory, $disk);
            if ($path) {
                $paths[] = $path;
            }
        }

        return $paths;
    }
}
