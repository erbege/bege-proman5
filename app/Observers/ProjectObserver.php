<?php

namespace App\Observers;

use App\Http\Controllers\DashboardController;
use App\Models\Project;
use Illuminate\Support\Facades\Cache;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        $this->clearRelatedCaches();
    }

    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        $this->clearRelatedCaches();
    }

    /**
     * Handle the Project "deleted" event.
     */
    public function deleted(Project $project): void
    {
        $this->clearRelatedCaches();
    }

    /**
     * Clear caches when project changes
     */
    protected function clearRelatedCaches(): void
    {
        DashboardController::clearDashboardCache();
    }
}
