<?php

namespace App\Notifications;

use App\Models\WeeklyReport;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WeeklyReportStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected WeeklyReport $report;
    protected string $action;

    /**
     * @param WeeklyReport $report
     * @param string $action One of: submitted, approved, rejected, published
     */
    public function __construct(WeeklyReport $report, string $action)
    {
        $this->report = $report;
        $this->action = $action;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', FcmChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        $messages = [
            'submitted' => "Weekly Report Week {$this->report->week_number} diajukan untuk review oleh {$this->getActorName()}",
            'approved' => "Weekly Report Week {$this->report->week_number} telah disetujui oleh {$this->getActorName()}",
            'rejected' => "Weekly Report Week {$this->report->week_number} ditolak oleh {$this->getActorName()}",
            'published' => "Weekly Report Week {$this->report->week_number} telah dipublish oleh {$this->getActorName()}",
        ];

        $titles = [
            'submitted' => 'Weekly Report Diajukan',
            'approved' => 'Weekly Report Disetujui',
            'rejected' => 'Weekly Report Ditolak',
            'published' => 'Weekly Report Dipublish',
        ];

        return [
            'type' => "weekly_report_{$this->action}",
            'title' => $titles[$this->action] ?? 'Update Weekly Report',
            'message' => $messages[$this->action] ?? "Status weekly report berubah menjadi {$this->action}",
            'report_id' => $this->report->id,
            'project_id' => $this->report->project_id,
            'project_name' => $this->report->project->name ?? '',
            'week_number' => $this->report->week_number,
            'status' => $this->action,
            'rejection_reason' => $this->action === 'rejected' ? $this->report->rejection_reason : null,
            'url' => route('projects.weekly-reports.show', [
                'project' => $this->report->project_id,
                'weekly_report' => $this->report->id,
            ]),
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'data' => $this->toArray($notifiable),
        ];
    }

    public function toFcm(object $notifiable): array
    {
        $titles = [
            'submitted' => 'Weekly Report Diajukan',
            'approved' => 'Weekly Report Disetujui',
            'rejected' => 'Weekly Report Ditolak',
            'published' => 'Weekly Report Dipublish',
        ];

        return [
            'title' => $titles[$this->action] ?? 'Update Weekly Report',
            'body' => "Week {$this->report->week_number} - {$this->report->project->name}: {$this->action}",
            'data' => [
                'type' => "weekly_report_{$this->action}",
                'report_id' => (string) $this->report->id,
                'project_id' => (string) $this->report->project_id,
                'url' => route('projects.weekly-reports.show', [
                    'project' => $this->report->project_id,
                    'weekly_report' => $this->report->id,
                ]),
            ],
        ];
    }

    protected function getActorName(): string
    {
        return match ($this->action) {
            'submitted' => $this->report->submitter?->name ?? 'seseorang',
            'approved' => $this->report->approver?->name ?? 'seseorang',
            'rejected' => $this->report->reviewer?->name ?? 'seseorang',
            'published' => $this->report->approver?->name ?? 'seseorang',
            default => 'seseorang',
        };
    }
}
