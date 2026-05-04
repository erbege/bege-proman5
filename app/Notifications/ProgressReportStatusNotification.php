<?php

namespace App\Notifications;

use App\Models\ProgressReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProgressReportStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ProgressReport $report,
        public string $action
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'fcm'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title())
            ->line($this->body())
            ->action('Lihat Progress Report', route('projects.progress.show', [$this->report->project, $this->report]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title(),
            'body' => $this->body(),
            'report_id' => $this->report->id,
            'project_id' => $this->report->project_id,
            'status' => $this->report->status,
            'action' => $this->action,
            'url' => route('projects.progress.show', [$this->report->project, $this->report]),
        ];
    }

    public function toFcm(object $notifiable): array
    {
        return [
            'title' => $this->title(),
            'body' => $this->body(),
            'data' => [
                'type' => 'progress_report_status',
                'report_id' => (string) $this->report->id,
                'project_id' => (string) $this->report->project_id,
                'status' => $this->report->status,
                'action' => $this->action,
            ],
        ];
    }

    protected function title(): string
    {
        return match ($this->action) {
            'submitted' => 'Progress Report Diajukan',
            'approved' => 'Progress Report Diverifikasi',
            'rejected' => 'Progress Report Ditolak',
            'published' => 'Progress Report Dipublikasikan',
            default => 'Update Progress Report',
        };
    }

    protected function body(): string
    {
        $code = $this->report->report_code ?? "#{$this->report->id}";
        return match ($this->action) {
            'submitted' => "Laporan {$code} diajukan untuk review.",
            'approved' => "Laporan {$code} telah diverifikasi.",
            'rejected' => "Laporan {$code} ditolak untuk revisi.",
            'published' => "Laporan {$code} telah dipublikasikan.",
            default => "Ada pembaruan status untuk laporan {$code}.",
        };
    }
}

