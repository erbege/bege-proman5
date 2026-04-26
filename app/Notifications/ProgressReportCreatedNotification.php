<?php

namespace App\Notifications;

use App\Models\ProgressReport;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProgressReportCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ProgressReport $progressReport;

    public function __construct(ProgressReport $progressReport)
    {
        $this->progressReport = $progressReport;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    /**
     * Get the array representation of the notification for database.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'progress_report_created',
            'title' => 'Laporan Progres Baru',
            'message' => "Laporan progres baru untuk proyek {$this->progressReport->project->name}",
            'report_id' => $this->progressReport->id,
            'project_id' => $this->progressReport->project_id,
            'project_name' => $this->progressReport->project->name,
            'reporter_name' => $this->progressReport->reporter?->name,
            'progress_percentage' => $this->progressReport->progress_percentage,
            'report_date' => $this->progressReport->report_date ? $this->progressReport->report_date->format('Y-m-d') : null,
            'url' => route('projects.progress.show', [
                'project' => $this->progressReport->project_id,
                'report' => $this->progressReport->id,
            ]),
        ];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm(object $notifiable): array
    {
        $project = $this->progressReport->project;
        $progress = $this->progressReport->progress_percentage ?? 0;

        return [
            'title' => 'Laporan Progres Baru',
            'body' => "Progres {$project->name}: {$progress}%",
            'data' => [
                'type' => 'progress_report_created',
                'report_id' => (string) $this->progressReport->id,
                'project_id' => (string) $this->progressReport->project_id,
                'url' => route('projects.progress.show', [
                    'project' => $this->progressReport->project_id,
                    'report' => $this->progressReport->id,
                ]),
            ],
        ];
    }
}
