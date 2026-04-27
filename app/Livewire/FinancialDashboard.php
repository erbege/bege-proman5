<?php

namespace App\Livewire;

use App\Models\Project;
use App\Services\CostControlService;
use Livewire\Component;

class FinancialDashboard extends Component
{
    public Project $project;
    public $summary;
    public $details;
    public $search = '';

    public function mount(Project $project)
    {
        if (!auth()->user()->can('financials.view-report')) {
            abort(403);
        }
        $this->project = $project;
        $this->refreshData();
    }

    public function refreshData()
    {
        if (!auth()->user()->can('financials.view-report')) {
            abort(403);
        }
        $costService = app(CostControlService::class);
        $this->summary = $costService->getProjectFinancialSummary($this->project);
        $this->details = $costService->generateReport($this->project);
        
        if ($this->search) {
            $this->details = $this->details->filter(function($item) {
                return str_contains(strtolower($item['work_name']), strtolower($this->search)) ||
                       str_contains(strtolower($item['code']), strtolower($this->search));
            });
        }
    }

    public function updatedSearch()
    {
        $this->refreshData();
    }

    public function render()
    {
        return view('livewire.financial-dashboard');
    }
}
