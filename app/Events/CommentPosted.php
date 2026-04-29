<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentPosted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;

    /**
     * Create a new event instance.
     */
    public function __construct($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Broadcast to the specific project channel
        // Since comments are contextual, we might broadcast to a general project channel
        // or a specific weekly report channel.
        $project_id = null;
        if ($this->comment->commentable_type === 'App\Models\WeeklyReport') {
            $project_id = $this->comment->commentable->project_id;
        }

        return [
            new PrivateChannel('project.' . $project_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'comment' => new \App\Http\Resources\Api\CommentResource($this->comment),
            'commentable_type' => $this->comment->commentable_type,
            'commentable_id' => $this->comment->commentable_id,
        ];
    }
}
