<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProgressReport;
use App\Models\RabItem;
use App\Models\SystemSetting;
use App\Services\ScheduleCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProgressReportController extends Controller
{
    public function index(Project $project)
    {
        $reports = $project->progressReports()
            ->with(['rabItem', 'reporter'])
            ->orderByDesc('report_date')
            ->paginate(20);

        return view('projects.progress.index', compact('project', 'reports'));
    }

    public function create(Project $project)
    {
        $rabItems = $project->rabItems()
            ->with('section')
            ->orderBy('sort_order')
            ->get();

        $weatherOptions = [
            'sunny' => 'Cerah',
            'cloudy' => 'Berawan',
            'rainy' => 'Hujan',
            'stormy' => 'Badai',
        ];

        return view('projects.progress.create', compact('project', 'rabItems', 'weatherOptions'));
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'rab_item_id' => 'nullable|exists:rab_items,id',
            'report_date' => 'required|date',
            'progress_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'issues' => 'nullable|string',
            'weather' => 'nullable|in:sunny,cloudy,rainy,stormy',
            'workers_count' => 'nullable|integer|min:0',
            'labor_details' => 'nullable|array',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|max:5120', // 5MB max per image
        ]);

        $validated['project_id'] = $project->id;
        $validated['reported_by'] = Auth::id();

        // Handle photo uploads with resize and WebP conversion
        if ($request->hasFile('photos')) {
            $disk = SystemSetting::getStorageDisk();
            $imageResizer = new \App\Services\ImageResizeService();
            $photoPaths = $imageResizer->processMultiple(
                $request->file('photos'),
                "progress/{$project->id}",
                $disk
            );
            $validated['photos'] = $photoPaths;
        }

        // Calculate cumulative progress if rab_item_id is provided
        if (!empty($validated['rab_item_id'])) {
            $rabItem = RabItem::find($validated['rab_item_id']);
            $validated['cumulative_progress'] = min(100, $rabItem->actual_progress + $validated['progress_percentage']);
        }

        $report = ProgressReport::create($validated);

        // Update RAB item progress
        if ($report->rab_item_id) {
            $report->rabItem->update([
                'actual_progress' => $report->cumulative_progress ?? $report->progress_percentage,
            ]);

            // Regenerate schedule
            $scheduleCalculator = new ScheduleCalculator();
            $scheduleCalculator->updateFromProgress($project);
        }

        // Notify project team members (except reporter)
        $report->load(['project', 'reporter']);
        $teamMembers = $project->team()
            ->where('users.id', '!=', Auth::id())
            ->get();

        \Illuminate\Support\Facades\Notification::send(
            $teamMembers,
            new \App\Notifications\ProgressReportCreatedNotification($report)
        );

        return redirect()
            ->route('projects.progress.index', $project)
            ->with('success', 'Laporan progress berhasil ditambahkan.');
    }

    public function show(Project $project, ProgressReport $report)
    {
        $report->load(['rabItem.section', 'reporter']);

        return view('projects.progress.show', compact('project', 'report'));
    }

    public function destroy(Project $project, ProgressReport $report)
    {
        $hasRabItem = $report->rab_item_id ? true : false;
        $rabItem = $report->rabItem;

        // Delete photos
        if ($report->photos) {
            $disk = SystemSetting::getStorageDisk();
            foreach ($report->photos as $photo) {
                Storage::disk($disk)->delete($photo);
            }
        }

        $report->delete();

        // Regenerate schedule if the deleted report was linked to a RAB item
        if ($hasRabItem && $rabItem) {
            // Recalculate actual_progress for the RAB item
            $actualProgress = ProgressReport::where('rab_item_id', $rabItem->id)->sum('progress_percentage');
            $rabItem->update([
                'actual_progress' => min(100, $actualProgress)
            ]);

            $scheduleCalculator = new ScheduleCalculator();
            $scheduleCalculator->updateFromProgress($project);
        }

        return redirect()
            ->route('projects.progress.index', $project)
            ->with('success', 'Laporan progress berhasil dihapus.');
    }
}
