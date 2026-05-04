<?php

namespace App\Services;

use App\Models\ProgressReport;
use App\Models\Project;
use App\Models\SystemSetting;
use App\Models\WeeklyReport;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentationService
{
    protected ImageResizeService $imageResizer;

    public function __construct(ImageResizeService $imageResizer)
    {
        $this->imageResizer = $imageResizer;
    }

    /**
     * Upload documentation photos to a weekly report.
     *
     * @param WeeklyReport $report
     * @param Project $project
     * @param array $files Array of UploadedFile instances
     * @return array{count: int, photos: array} Array of uploaded photo info
     */
    public function uploadDocumentation($report, Project $project, array $files): array
    {
        $disk = SystemSetting::getStorageDisk();
        $uploads = $report->documentation_uploads ?? [];
        $newPhotos = [];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) continue;

            $path = $this->imageResizer->processAndSave(
                $file,
                "reports/{$project->id}/docs",
                $disk
            );

            $uploads[] = $path;
            $newPhotos[] = [
                'path' => $path,
                'url' => SystemSetting::getFileUrl($path),
                'name' => $file->getClientOriginalName(),
            ];
        }

        $report->update(['documentation_uploads' => $uploads]);

        return [
            'count' => count($newPhotos),
            'photos' => $newPhotos,
        ];
    }

    /**
     * Add progress report photo paths to report documentation.
     * These are existing photos from daily progress reports — no file upload needed.
     *
     * @param mixed $report WeeklyReport or MonthlyReport
     * @param array $photoPaths Array of storage paths
     * @return array{count: int, photos: array}
     */
    public function addProgressPhotos($report, array $photoPaths): array
    {
        $uploads = $report->documentation_uploads ?? [];
        $added = [];

        foreach ($photoPaths as $path) {
            if (!in_array($path, $uploads)) {
                $uploads[] = $path;
                $added[] = [
                    'path' => $path,
                    'url' => SystemSetting::getFileUrl($path),
                    'name' => basename($path),
                ];
            }
        }

        $report->update(['documentation_uploads' => $uploads]);

        return [
            'count' => count($added),
            'photos' => $added,
        ];
    }

    /**
     * Remove a documentation item from a report.
     *
     * @param mixed $report WeeklyReport or MonthlyReport
     * @param string $type Either 'project_file' or 'upload'
     * @param string|int|null $identifier File ID for project_file, path for upload
     * @return void
     */
    public function removeDocumentation($report, string $type, $identifier): void
    {
        if ($type === 'project_file') {
            $ids = $report->documentation_ids ?? [];
            $ids = array_values(array_filter($ids, fn($id) => $id != $identifier));
            $report->update(['documentation_ids' => $ids]);
        } else {
            $uploads = $report->documentation_uploads ?? [];
            $uploads = array_values(array_filter($uploads, fn($p) => $p !== $identifier));
            $report->update(['documentation_uploads' => $uploads]);
        }
    }

    /**
     * Update the list of project file IDs used as documentation.
     *
     * @param mixed $report WeeklyReport or MonthlyReport
     * @param array $fileIds Array of project_files IDs
     * @return void
     */
    public function updateDocumentationIds($report, array $fileIds): void
    {
        $report->update(['documentation_ids' => $fileIds]);
    }

    /**
     * Collect all progress report photos for a specific period.
     * Used by weekly report to show available photos from daily progress entries.
     *
     * @param Project $project
     * @param Carbon $periodStart
     * @param Carbon $periodEnd
     * @return array Array of photo data with path, url, date, rab_item, reporter
     */
    public function getProgressPhotosForPeriod(Project $project, Carbon $periodStart, Carbon $periodEnd): array
    {
        $reports = ProgressReport::where('project_id', $project->id)
            ->whereBetween('report_date', [$periodStart, $periodEnd])
            ->with(['rabItem', 'reporter'])
            ->orderBy('report_date')
            ->get();

        $photos = [];
        foreach ($reports as $pr) {
            if ($pr->photos && is_array($pr->photos)) {
                foreach ($pr->photos as $photoPath) {
                    $photos[] = [
                        'path' => $photoPath,
                        'url' => SystemSetting::getFileUrl($photoPath),
                        'date' => $pr->report_date->format('d M Y'),
                        'rab_item' => $pr->rabItem
                            ? $pr->rabItem->full_code . ' - ' . $pr->rabItem->work_name
                            : 'N/A',
                        'reporter' => $pr->reporter ? $pr->reporter->name : 'Unknown',
                    ];
                }
            }
        }

        return $photos;
    }

    /**
     * Delete a cover image from storage.
     *
     * @param string $path
     * @return void
     */
    public function deleteCoverImage(string $path): void
    {
        $disk = SystemSetting::getStorageDisk();
        Storage::disk($disk)->delete($path);
    }
}
