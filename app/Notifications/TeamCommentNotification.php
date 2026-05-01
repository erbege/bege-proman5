<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class TeamCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $projectName = $this->comment->commentable->project->name ?? 'Project';
        $userName = $this->comment->user->name;

        return [
            'comment_id' => $this->comment->id,
            'project_id' => $this->comment->commentable->project_id ?? null,
            'project_name' => $projectName,
            'title' => 'Tanggapan Baru dari Tim Proyek',
            'message' => "{$userName} memberikan tanggapan pada laporan mingguan proyek {$projectName}.",
            'action_url' => route('owner.weekly-reports.show', [
                'report' => $this->comment->commentable_id
            ]),
            'type' => 'team_comment',
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
