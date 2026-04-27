<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\CostControlService;
use Illuminate\Http\Request;

class CostControlController extends Controller
{
    protected CostControlService $costService;

    public function __construct(CostControlService $costService)
    {
        $this->costService = $costService;
    }

    /**
     * Display the financial status / cost control report for the project.
     */
    public function index(Project $project)
    {
        $this->authorize('financials.view-report');

        $summary = $this->costService->getProjectFinancialSummary($project);
        $details = $this->costService->generateReport($project);

        return view('projects.financial.index', compact('project', 'summary', 'details'));
    }
}
