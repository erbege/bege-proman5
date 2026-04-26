<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TimeScheduleExport implements WithMultipleSheets
{
    protected Project $project;
    protected $schedules;
    protected $rabSections;

    public function __construct(Project $project, $schedules, $rabSections)
    {
        $this->project = $project;
        $this->schedules = $schedules;
        $this->rabSections = $rabSections;
    }

    public function sheets(): array
    {
        return [
            'Ringkasan' => new TimeScheduleSummarySheet($this->project, $this->schedules),
            'Time Schedule' => new TimeScheduleMatrixSheet($this->project, $this->schedules, $this->rabSections),
        ];
    }
}
