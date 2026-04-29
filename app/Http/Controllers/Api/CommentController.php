<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Resources\Api\CommentResource;
use App\Models\Comment;
use App\Events\CommentPosted;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use ApiResponse;

    /**
     * Store a new comment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|integer',
            'content' => 'required|string|max:1000',
            'metadata' => 'nullable|array',
        ]);

        // Basic authorization - user must have weekly_report.comment permission
        $this->authorize('weekly_report.comment');

        // Verify the model exists
        $modelClass = $validated['commentable_type'];
        if (!class_exists($modelClass)) {
            return $this->errorResponse('Invalid model type', 422);
        }

        $model = $modelClass::findOrFail($validated['commentable_id']);

        // Check if user is part of the project team or owner
        // (Assuming all procurement/report models have project_id)
        if (isset($model->project_id)) {
            $user = auth()->user();
            $isTeamMember = $user->projects()->where('projects.id', $model->project_id)->exists();
            $isOwner = $user->hasRole('owner'); // Simplified check
            
            if (!$isTeamMember && !$isOwner && !$user->hasRole('Superadmin')) {
                return $this->errorResponse('Unauthorized access to this project', 403);
            }
        }

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'commentable_type' => $validated['commentable_type'],
            'commentable_id' => $validated['commentable_id'],
            'content' => $validated['content'],
            'metadata' => $validated['metadata'] ?? null,
        ]);

        // Load the user relationship
        $comment->load('user');

        // Broadcast the event
        broadcast(new CommentPosted($comment))->toOthers();

        return $this->successResponse(
            'Comment posted successfully',
            new CommentResource($comment),
            201
        );
    }

    /**
     * List comments for a model.
     */
    public function index(Request $request)
    {
        $request->validate([
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|integer',
        ]);

        $comments = Comment::where('commentable_type', $request->commentable_type)
            ->where('commentable_id', $request->commentable_id)
            ->with('user')
            ->latest()
            ->get();

        return $this->successResponse(
            'Comments retrieved successfully',
            CommentResource::collection($comments)
        );
    }
}
