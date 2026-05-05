<?php

namespace App\Jobs;

use App\Models\Project;
use App\Services\ScheduleCalculator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecalculateProjectScheduleJob implements ShouldQueue
{
    use Queueable;

    protected Project $project;

    /**
     * Create a new job instance.
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Execute the job.
     */
    public function handle(ScheduleCalculator $scheduleCalculator): void
    {
        // Panggil re-kalkulasi jadwal (ini akan menghapus & memasukkan ulang baris-baris jadwal)
        $scheduleCalculator->updateFromProgress($this->project);
    }
}
