<?php

namespace App\Livewire;

use App\Models\Project;
use App\Services\ScheduleCalculator;
use Livewire\Component;

class ScheduleManager extends Component
{
    public Project $project;
    public string $viewMode = 'table'; // table, gantt, scurve
    public array $scurveData = [];

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->loadScurveData();
    }

    public function setViewMode(string $mode)
    {
        $this->viewMode = $mode;
    }

    public function loadScurveData()
    {
        $scheduleCalculator = new ScheduleCalculator();
        $this->scurveData = $scheduleCalculator->getScurveData($this->project);
    }

    public function regenerateSchedule()
    {
        $scheduleCalculator = new ScheduleCalculator();
        $scheduleCalculator->generateSchedule($this->project);
        $this->loadScurveData();
        session()->flash('success', 'Jadwal berhasil digenerate ulang.');
    }

    public function render()
    {
        $this->project->load(['rabSections.items', 'schedules']);

        $schedules = $this->project->schedules()->orderBy('week_number')->get();

        // Prepare Gantt data
        $ganttItems = [];
        foreach ($this->project->rabSections as $section) {
            foreach ($section->items as $item) {
                if ($item->planned_start && $item->planned_end) {
                    $ganttItems[] = [
                        'id' => $item->id,
                        'name' => $item->work_name,
                        'section' => $section->name,
                        'start' => $item->planned_start->format('Y-m-d'),
                        'end' => $item->planned_end->format('Y-m-d'),
                        'progress' => $item->actual_progress ?? 0,
                        'weight' => $item->weight ?? 0,
                    ];
                }
            }
        }

        return view('livewire.schedule-manager', [
            'schedules' => $schedules,
            'ganttItems' => $ganttItems,
        ]);
    }
}
