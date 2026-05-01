<?php

namespace App\Events;

use App\Http\Resources\Api\CommentResource;
use App\Models\WeeklyReport;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;

    public function __construct($comment)
    {
        $this->comment = $comment;
    }

    public function broadcastOn(): array
    {
        $projectId = null;

        if ($this->comment->commentable_type === WeeklyReport::class) {
            $projectId = $this->comment->commentable?->project_id;
        }

        return [
            new PrivateChannel('project.' . $projectId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'comment' => new CommentResource($this->comment),
            'commentable_type' => $this->comment->commentable_type,
            'commentable_id' => $this->comment->commentable_id,
        ];
    }
}

