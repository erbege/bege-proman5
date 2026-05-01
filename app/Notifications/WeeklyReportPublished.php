<?php

namespace App\Notifications;

use App\Models\WeeklyReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class WeeklyReportPublished extends Notification implements ShouldQueue
{
    use Queueable;

    protected $report;

    /**
     * Create a new notification instance.
     */
    public function __construct(WeeklyReport $report)
    {
        $this->report = $report;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'project_id' => $this->report->project_id,
            'project_name' => $this->report->project->name,
            'week_number' => $this->report->week_number,
            'title' => 'Laporan Mingguan Baru Tersedia',
            'message' => "Laporan mingguan Week {$this->report->week_number} untuk proyek {$this->report->project->name} telah diterbitkan.",
            'action_url' => route('owner.weekly-reports.show', $this->report),
            'type' => 'weekly_report_published',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
