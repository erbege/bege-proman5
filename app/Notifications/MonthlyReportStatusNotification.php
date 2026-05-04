<?php

namespace App\Notifications;

use App\Models\MonthlyReport;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MonthlyReportStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected MonthlyReport $report;
    protected string $action;

    /**
     * @param MonthlyReport $report
     * @param string $action One of: submitted, approved, rejected, published
     */
    public function __construct(MonthlyReport $report, string $action)
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
        $periodLabel = $this->report->period_label;
        
        $messages = [
            'submitted' => "Monthly Report {$periodLabel} diajukan untuk review oleh {$this->getActorName()}",
            'approved' => "Monthly Report {$periodLabel} telah disetujui oleh {$this->getActorName()}",
            'rejected' => "Monthly Report {$periodLabel} ditolak oleh {$this->getActorName()}",
            'published' => "Monthly Report {$periodLabel} telah dipublish oleh {$this->getActorName()}",
        ];

        $titles = [
            'submitted' => 'Monthly Report Diajukan',
            'approved' => 'Monthly Report Disetujui',
            'rejected' => 'Monthly Report Ditolak',
            'published' => 'Monthly Report Dipublish',
        ];

        return [
            'type' => "monthly_report_{$this->action}",
            'title' => $titles[$this->action] ?? 'Update Monthly Report',
            'message' => $messages[$this->action] ?? "Status monthly report berubah menjadi {$this->action}",
            'report_id' => $this->report->id,
            'project_id' => $this->report->project_id,
            'project_name' => $this->report->project->name ?? '',
            'year' => $this->report->year,
            'month' => $this->report->month,
            'status' => $this->action,
            'rejection_reason' => $this->action === 'rejected' ? $this->report->rejection_reason : null,
            'url' => route('projects.monthly-reports.show', [
                'project' => $this->report->project_id,
                'monthly_report' => $this->report->id,
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
            'submitted' => 'Monthly Report Diajukan',
            'approved' => 'Monthly Report Disetujui',
            'rejected' => 'Monthly Report Ditolak',
            'published' => 'Monthly Report Dipublish',
        ];

        $periodLabel = $this->report->period_label;

        return [
            'title' => $titles[$this->action] ?? 'Update Monthly Report',
            'body' => "{$periodLabel} - {$this->report->project->name}: {$this->action}",
            'data' => [
                'type' => "monthly_report_{$this->action}",
                'report_id' => (string) $this->report->id,
                'project_id' => (string) $this->report->project_id,
                'url' => route('projects.monthly-reports.show', [
                    'project' => $this->report->project_id,
                    'monthly_report' => $this->report->id,
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
