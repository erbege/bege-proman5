<?php

namespace App\Http\Controllers\Portal\Owner;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\WeeklyReport;
use App\Events\CommentPosted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Store a new comment on a weekly report
     */
    public function store(Request $request, WeeklyReport $report)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'metadata' => 'nullable|array',
        ]);

        // Security check
        if ($report->project->owner_id != Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke proyek ini.');
        }

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'commentable_type' => WeeklyReport::class,
            'commentable_id' => $report->id,
            'content' => $validated['content'],
            'metadata' => $validated['metadata'] ?? null,
        ]);

        // Broadcast the event for real-time updates
        broadcast(new CommentPosted($comment->load('user')))->toOthers();

        // Notify Project Team
        $teamMembers = $report->project->team()->where('users.id', '!=', Auth::id())->get();
        foreach ($teamMembers as $member) {
            $member->notify(new \App\Notifications\OwnerCommentNotification($comment));
        }

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }
}
