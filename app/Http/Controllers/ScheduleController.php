<?php

namespace App\Http\Controllers;

use App\Exports\TimeScheduleExport;
use App\Models\Project;
use App\Models\RabItem;
use App\Services\ScheduleCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ScheduleController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('schedule.view');

        // Load hierarchical RAB sections
        $rabSections = $project->rabSections()
            ->whereNull('parent_id')
            ->with(['recursiveChildren', 'items'])
            ->orderByRaw("CAST(SUBSTRING_INDEX(code, '.', 1) AS UNSIGNED)")
            ->get();

        $project->load(['schedules']);

        $scheduleCalculator = new ScheduleCalculator();
        $scurveData = $scheduleCalculator->getScurveData($project);

        // Check if user can edit schedule
        $canEditSchedule = auth()->user()->can('schedule.manage');

        return view('projects.schedule.index', compact('project', 'rabSections', 'scurveData', 'canEditSchedule'));
    }

    public function regenerate(Project $project)
    {
        $this->authorize('schedule.manage');

        $scheduleCalculator = new ScheduleCalculator();
        $scheduleCalculator->generateSchedule($project);

        return redirect()
            ->route('projects.schedule.index', $project)
            ->with('success', 'Jadwal berhasil digenerate ulang.');
    }

    public function gantt(Project $project)
    {
        $this->authorize('schedule.view');

        $rabSections = $project->rabSections()
            ->whereNull('parent_id')
            ->with(['recursiveChildren', 'items'])
            ->orderByRaw("CAST(SUBSTRING_INDEX(code, '.', 1) AS UNSIGNED)")
            ->get();

        return view('projects.schedule.gantt', compact('project', 'rabSections'));
    }

    public function scurve(Project $project)
    {
        $this->authorize('schedule.view');

        $schedules = $project->schedules()->orderBy('week_number')->get();

        $rabSections = $project->rabSections()
            ->whereNull('parent_id')
            ->with(['recursiveChildren', 'items'])
            ->orderByRaw("CAST(SUBSTRING_INDEX(code, '.', 1) AS UNSIGNED)")
            ->get();

        $scheduleCalculator = new ScheduleCalculator();
        $chartData = $scheduleCalculator->getScurveData($project);

        return view('projects.schedule.scurve', compact('project', 'schedules', 'chartData', 'rabSections'));
    }

    public function exportExcel(Project $project)
    {
        $this->authorize('schedule.view');

        $rabSections = $project->rabSections()
            ->whereNull('parent_id')
            ->with(['recursiveChildren', 'items'])
            ->orderByRaw("CAST(SUBSTRING_INDEX(code, '.', 1) AS UNSIGNED)")
            ->get();

        $schedules = $project->schedules()->orderBy('week_number')->get();

        $filename = 'Time_Schedule_' . str_replace(' ', '_', $project->code) . '_' . date('Ymd') . '.xlsx';

        return Excel::download(new TimeScheduleExport($project, $schedules, $rabSections), $filename);
    }

    public function exportPdf(Project $project)
    {
        $this->authorize('schedule.view');

        $rabSections = $project->rabSections()
            ->whereNull('parent_id')
            ->with(['recursiveChildren', 'items'])
            ->orderByRaw("CAST(SUBSTRING_INDEX(code, '.', 1) AS UNSIGNED)")
            ->get();

        $schedules = $project->schedules()->orderBy('week_number')->get();

        // Calculate weeks for the matrix
        $startDate = $project->start_date;
        $endDate = $project->end_date;
        $totalWeeks = max(1, (int) ceil($startDate->diffInDays($endDate) / 7));

        $months = [];
        for ($w = 0; $w < $totalWeeks; $w++) {
            $weekDate = $startDate->copy()->addWeeks($w);
            $monthKey = $weekDate->format('M-Y');
            if (!isset($months[$monthKey])) {
                $months[$monthKey] = ['label' => $weekDate->format('M Y'), 'weeks' => []];
            }
            $months[$monthKey]['weeks'][] = [
                'num' => $w + 1,
                'date' => $weekDate->format('d'),
                'full' => $weekDate
            ];
        }

        $pdf = Pdf::loadView('exports.schedule-pdf', compact('project', 'schedules', 'months', 'totalWeeks', 'startDate', 'rabSections'))
            ->setPaper('a3', 'landscape');

        $filename = 'Time_Schedule_' . str_replace(' ', '_', $project->code) . '_' . date('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    public function updateItemSchedule(Request $request, Project $project, RabItem $item)
    {
        $this->authorize('schedule.manage');

        // Validate that the item belongs to the project
        if ($item->project_id !== $project->id) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Item tidak ditemukan dalam proyek ini.'], 404);
            }
            abort(404);
        }

        // Validate input
        $validated = $request->validate([
            'planned_start' => 'nullable|date',
            'planned_end' => 'nullable|date|after_or_equal:planned_start',
        ], [
            'planned_end.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        // Update item
        $item->update([
            'planned_start' => $validated['planned_start'] ?: null,
            'planned_end' => $validated['planned_end'] ?: null,
        ]);

        // Regenerate schedule
        $scheduleCalculator = new ScheduleCalculator();
        $scheduleCalculator->generateSchedule($project);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal item berhasil diperbarui.',
            ]);
        }

        return redirect()
            ->route('projects.schedule.index', $project)
            ->with('success', 'Jadwal item berhasil diperbarui.');
    }

    /**
     * Update the parallel scheduling flag for an item
     */
    public function updateItemParallel(Request $request, $projectId, $itemId)
    {
        $this->authorize('schedule.manage');

        try {
            // Manual binding to debug/avoid scoping issues
            $project = Project::findOrFail($projectId);
            $item = RabItem::findOrFail($itemId);

            // Validate that the item belongs to the project
            if ($item->project_id !== $project->id) {
                return response()->json(['error' => 'Item tidak ditemukan dalam proyek ini.'], 404);
            }

            // Validate input
            $validated = $request->validate([
                'can_parallel' => 'required|boolean',
            ]);

            // Update item
            $item->update([
                'can_parallel' => $validated['can_parallel'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengaturan paralel berhasil diperbarui.',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Data tidak ditemukan (Project atau Item).'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Preview auto-generated schedule based on RAB weights
     */
    public function autoSchedule(Project $project)
    {
        $this->authorize('schedule.manage');

        // Check if project has RAB items
        $itemCount = $project->rabItems()->count();
        if ($itemCount === 0) {
            return redirect()
                ->route('projects.schedule.index', $project)
                ->with('error', 'Proyek belum memiliki item pekerjaan (RAB). Upload RAB terlebih dahulu.');
        }

        $scheduleCalculator = new ScheduleCalculator();
        $autoScheduleData = $scheduleCalculator->autoSchedule($project);

        return view('projects.schedule.auto', compact('project', 'autoScheduleData'));
    }

    /**
     * Apply auto-generated schedule to RAB items
     */
    public function applyAutoSchedule(Request $request, Project $project)
    {
        $this->authorize('schedule.manage');

        $validated = $request->validate([
            'mode' => 'required|in:extend,compress,keep',
        ]);

        $scheduleCalculator = new ScheduleCalculator();
        $result = $scheduleCalculator->applyAutoSchedule($project, $validated['mode']);

        $message = 'Jadwal otomatis berhasil diterapkan.';
        if ($validated['mode'] === 'extend') {
            $message .= ' Tanggal selesai proyek telah disesuaikan.';
        } elseif ($validated['mode'] === 'compress') {
            $message .= ' Jadwal telah dikompres agar sesuai dengan tanggal selesai proyek.';
        }

        // Notify project team members about schedule change
        $teamMembers = $project->team()
            ->where('users.id', '!=', auth()->id())
            ->get();

        \App\Services\NotificationHelper::sendToProjectTeam(
            $project,
            new \App\Notifications\ScheduleChangedNotification($project, 'auto_applied', $message),
            auth()->id()
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $result,
            ]);
        }

        return redirect()
            ->route('projects.schedule.index', $project)
            ->with('success', $message);
    }
}
