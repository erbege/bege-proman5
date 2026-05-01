<?php

namespace App\Http\Controllers\Portal\Owner;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\WeeklyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GalleryController extends Controller
{
    /**
     * Display a gallery of all documentation photos for a project
     */
    public function index(Project $project)
    {
        // Security check
        if ($project->owner_id != Auth::id()) {
            abort(403);
        }

        $reports = WeeklyReport::where('project_id', $project->id)
            ->published()
            ->orderBy('week_number', 'desc')
            ->get();

        $photos = [];
        foreach ($reports as $report) {
            foreach ($report->all_documentation_photos as $photo) {
                $photos[] = array_merge($photo, [
                    'report_id' => $report->id,
                    'week_number' => $report->week_number,
                    'period' => $report->period_start->format('d M') . ' - ' . $report->period_end->format('d M Y'),
                ]);
            }
        }

        return view('portal.owner.projects.gallery', compact('project', 'photos'));
    }
}
