<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectFileVersion;
use App\Models\ProjectFileComment;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProjectFileDetail extends Component
{
    use WithFileUploads;

    public Project $project;
    public ProjectFile $file;

    // Upload new version
    public bool $showVersionModal = false;
    public $newVersionFile;
    public string $versionNotes = '';

    // Comment
    public string $newComment = '';
    public ?int $replyingTo = null;
    public string $replyComment = '';

    public function mount(Project $project, ProjectFile $file)
    {
        $this->project = $project;
        $this->file = $file;
    }

    public function openVersionModal()
    {
        $this->reset(['newVersionFile', 'versionNotes']);
        $this->showVersionModal = true;
    }

    public function closeVersionModal()
    {
        $this->showVersionModal = false;
        $this->reset(['newVersionFile', 'versionNotes']);
    }

    public function uploadVersion()
    {
        $maxSize = SystemSetting::getMaxFileSize();
        $maxSizeKb = (int) ($maxSize / 1024);

        $this->validate([
            'newVersionFile' => "required|file|max:{$maxSizeKb}",
            'versionNotes' => 'nullable|string|max:500',
        ]);

        $disk = SystemSetting::getStorageDisk();
        $extension = $this->newVersionFile->getClientOriginalExtension();
        $fileName = Str::uuid() . '.' . $extension;
        $path = "project-files/{$this->project->id}/{$fileName}";

        // Store file
        Storage::disk($disk)->put($path, file_get_contents($this->newVersionFile->getRealPath()));

        // Get next version number
        $nextVersion = $this->file->versions()->max('version') + 1;

        // Create version record
        ProjectFileVersion::create([
            'project_file_id' => $this->file->id,
            'version' => $nextVersion,
            'file_path' => $path,
            'disk' => $disk,
            'file_size' => $this->newVersionFile->getSize(),
            'mime_type' => $this->newVersionFile->getMimeType(),
            'extension' => $extension,
            'hash' => hash_file('sha256', $this->newVersionFile->getRealPath()),
            'notes' => $this->versionNotes ?: "Version {$nextVersion}",
            'uploaded_by' => auth()->id(),
        ]);

        // Update file's current version
        $this->file->update([
            'current_version' => $nextVersion,
            'original_name' => $this->newVersionFile->getClientOriginalName(),
        ]);

        $this->file->refresh();
        $this->closeVersionModal();
        session()->flash('success', "Versi {$nextVersion} berhasil diunggah.");
    }

    public function rollback(int $versionId)
    {
        $version = ProjectFileVersion::findOrFail($versionId);

        if ($version->project_file_id !== $this->file->id) {
            return;
        }

        $this->file->update(['current_version' => $version->version]);
        $this->file->refresh();

        session()->flash('success', "Berhasil rollback ke versi {$version->version}.");
    }

    public function updateStatus(string $status)
    {
        if (!in_array($status, ['draft', 'review', 'approved', 'final'])) {
            return;
        }

        if (!$this->file->canTransitionTo($status)) {
            session()->flash('error', 'Transisi status tidak valid.');
            return;
        }

        $this->file->update([
            'status' => $status,
            'is_final' => $status === 'final',
        ]);

        $this->file->refresh();
        session()->flash('success', 'Status berhasil diperbarui.');
    }

    public function addComment()
    {
        $this->validate([
            'newComment' => 'required|string|max:2000',
        ]);

        ProjectFileComment::create([
            'project_file_id' => $this->file->id,
            'user_id' => auth()->id(),
            'comment' => $this->newComment,
        ]);

        $this->newComment = '';
        $this->file->refresh();
    }

    public function startReply(int $commentId)
    {
        $this->replyingTo = $commentId;
        $this->replyComment = '';
    }

    public function cancelReply()
    {
        $this->replyingTo = null;
        $this->replyComment = '';
    }

    public function submitReply()
    {
        $this->validate([
            'replyComment' => 'required|string|max:2000',
        ]);

        ProjectFileComment::create([
            'project_file_id' => $this->file->id,
            'user_id' => auth()->id(),
            'parent_id' => $this->replyingTo,
            'comment' => $this->replyComment,
        ]);

        $this->cancelReply();
        $this->file->refresh();
    }

    public function toggleResolve(int $commentId)
    {
        $comment = ProjectFileComment::findOrFail($commentId);

        if ($comment->resolved) {
            $comment->unresolve();
        } else {
            $comment->resolve(auth()->id());
        }

        $this->file->refresh();
    }

    public function deleteFile()
    {
        $this->file->delete();

        return redirect()->route('projects.files.index', $this->project)
            ->with('success', 'File berhasil dihapus.');
    }

    public function render()
    {
        $this->file->load(['versions.uploader', 'comments.user', 'comments.replies.user', 'uploader']);

        return view('livewire.project-file-detail');
    }
}
