<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Resources\Api\CommentResource;
use App\Models\Comment;
use App\Events\CommentPosted;
use App\Events\CommentDeleted;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        if (isset($model->project_id)) {
            $user = auth()->user();
            $project = $model->project;
            
            $isTeamMember = $user->projects()->where('projects.id', $model->project_id)->exists();
            $isCorrectOwner = $user->hasRole('owner') && $project->owner_id == $user->id;
            $isAdmin = $user->hasRole(['Superadmin', 'super-admin', 'administrator']);
            
            if (!$isTeamMember && !$isCorrectOwner && !$isAdmin) {
                return $this->errorResponse('Unauthorized access to this project', 403);
            }

            // Owners can only comment on PUBLISHED weekly reports
            if ($user->hasRole('owner') && $model instanceof \App\Models\WeeklyReport && $model->status !== 'published') {
                return $this->errorResponse('Cannot comment on unpublished reports', 403);
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

        // Notification Logic
        if ($model instanceof \App\Models\WeeklyReport) {
            $project = $model->project;
            
            // If commenter is Owner, notify Team
            if (auth()->user()->hasRole('owner')) {
                $teamMembers = $project->team()->where('users.id', '!=', auth()->id())->get();
                foreach ($teamMembers as $member) {
                    $member->notify(new \App\Notifications\OwnerCommentNotification($comment));
                }
            } 
            // If commenter is NOT Owner (Team/Admin), notify Owner
            else {
                $owner = $project->owner;
                if ($owner && $owner->id != auth()->id()) {
                    $owner->notify(new \App\Notifications\TeamCommentNotification($comment));
                }
            }
        }

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

        $comments = Comment::withTrashed()
            ->where('commentable_type', $request->commentable_type)
            ->where('commentable_id', $request->commentable_id)
            ->with('user')
            ->latest()
            ->get();

        return $this->successResponse(
            'Comments retrieved successfully',
            CommentResource::collection($comments)
        );
    }

    /**
     * Soft delete a comment (author only).
     */
    public function destroy(Request $request, string $id)
    {
        $comment = Comment::withTrashed()->with('user')->findOrFail($id);

        // Already deleted => idempotent success
        if ($comment->trashed()) {
            return $this->successResponse('Comment already deleted.', new CommentResource($comment));
        }

        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        // Owner can only delete their own message (requirement)
        if ((int) $comment->user_id !== (int) $user->id) {
            return $this->errorResponse('Forbidden: you can only delete your own message.', 403);
        }

        // Verify user has access to the related project/report (defense in depth)
        $comment->loadMissing(['commentable', 'commentable.project']);
        $model = $comment->commentable;
        if ($model && isset($model->project_id)) {
            $project = $model->project;

            $isTeamMember = $user->projects()->where('projects.id', $model->project_id)->exists();
            $isCorrectOwner = $user->hasRole('owner') && $project?->owner_id == $user->id;
            $isAdmin = $user->hasRole(['Superadmin', 'super-admin', 'administrator']);

            if (!$isTeamMember && !$isCorrectOwner && !$isAdmin) {
                return $this->errorResponse('Unauthorized access to this project', 403);
            }

            // Owners can only interact within published weekly reports
            if ($user->hasRole('owner') && $model instanceof \App\Models\WeeklyReport && $model->status !== 'published') {
                return $this->errorResponse('Cannot delete comment on unpublished reports', 403);
            }
        }

        $comment->deleted_by = $user->id;
        $comment->save();
        $comment->delete();

        $comment->loadMissing('user');

        broadcast(new CommentDeleted($comment))->toOthers();

        return $this->successResponse('Comment deleted.', new CommentResource($comment));
    }
}
