<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectFileVersion;
use App\Models\ProjectFileComment;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectFileController extends Controller
{
    /**
     * Display file listing for a project
     */
    /**
     * Display file listing for a project
     */
    public function index(Request $request, Project $project)
    {
        $this->authorize('files.view');

        $query = ProjectFile::where('project_id', $project->id)
            ->where('type', 'file')
            ->with(['uploader', 'latestVersion']);

        // ... existing logic ...
        // Filter by folder
        if ($request->has('folder')) {
            $query->where('folder_id', $request->folder);
        } else {
            $query->whereNull('folder_id');
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('original_name', 'like', "%{$request->search}%");
            });
        }

        $files = $query->orderByDesc('updated_at')->paginate(20);

        $folders = ProjectFile::where('project_id', $project->id)
            ->where('type', 'folder')
            ->whereNull('folder_id')
            ->orderBy('name')
            ->get();

        $categories = ProjectFile::$categories;
        $statuses = ProjectFile::$statuses;

        return view('projects.files.index', compact(
            'project',
            'files',
            'folders',
            'categories',
            'statuses'
        ));
    }

    /**
     * Store a new file
     */
    public function store(Request $request, Project $project)
    {
        $this->authorize('files.create');

        $maxSize = SystemSetting::getMaxFileSize();
        $maxSizeKb = $maxSize / 1024;

        $request->validate([
            'file' => "required|file|max:{$maxSizeKb}",
            'name' => 'nullable|string|max:255',
            'category' => 'required|in:planning,design,cad,document,image,other',
            'folder_id' => 'nullable|exists:project_files,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $uploadedFile = $request->file('file');
        $disk = SystemSetting::getStorageDisk();

        // Generate file path
        $extension = $uploadedFile->getClientOriginalExtension();
        $fileName = Str::uuid() . '.' . $extension;
        $path = "project-files/{$project->id}/{$fileName}";

        // Store file
        Storage::disk($disk)->put($path, file_get_contents($uploadedFile));

        // Create file record
        $projectFile = ProjectFile::create([
            'project_id' => $project->id,
            'folder_id' => $request->folder_id,
            'name' => $request->name ?: pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME),
            'original_name' => $uploadedFile->getClientOriginalName(),
            'type' => 'file',
            'category' => $request->category,
            'status' => 'draft',
            'current_version' => 1,
            'description' => $request->description,
            'uploaded_by' => auth()->id(),
        ]);

        // Create version record
        ProjectFileVersion::create([
            'project_file_id' => $projectFile->id,
            'version' => 1,
            'file_path' => $path,
            'disk' => $disk,
            'file_size' => $uploadedFile->getSize(),
            'mime_type' => $uploadedFile->getMimeType(),
            'extension' => $extension,
            'hash' => hash_file('sha256', $uploadedFile->getRealPath()),
            'notes' => 'Initial upload',
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()
            ->route('projects.files.index', $project)
            ->with('success', 'File berhasil diunggah.');
    }

    /**
     * Show file detail
     */
    public function show(Project $project, ProjectFile $file)
    {
        $this->authorize('files.view');

        $file->load(['versions.uploader', 'comments.user', 'comments.replies.user', 'uploader']);

        // Generate breadcrumbs
        $breadcrumbs = [];
        $currentFolder = $file->folder;
        while ($currentFolder) {
            array_unshift($breadcrumbs, [
                'label' => $currentFolder->name,
                'url' => route('projects.files.index', [$project, 'folder' => $currentFolder->id])
            ]);
            $currentFolder = $currentFolder->folder;
        }

        return view('projects.files.show', compact('project', 'file', 'breadcrumbs'));
    }

    /**
     * Upload new version
     */
    public function uploadVersion(Request $request, Project $project, ProjectFile $file)
    {
        $this->authorize('files.update');

        // Protect FINAL files
        if ($file->status === 'final' && !auth()->user()->hasRole(['Superadmin', 'super-admin', 'project-manager'])) {
            return back()->with('error', 'File berstatus FINAL tidak dapat diupdate versinya.');
        }

        $maxSize = SystemSetting::getMaxFileSize();
        $maxSizeKb = $maxSize / 1024;

        $request->validate([
            'file' => "required|file|max:{$maxSizeKb}",
            'notes' => 'nullable|string|max:500',
        ]);

        $uploadedFile = $request->file('file');
        $disk = SystemSetting::getStorageDisk();

        // Generate file path
        $extension = $uploadedFile->getClientOriginalExtension();
        $fileName = Str::uuid() . '.' . $extension;
        $path = "project-files/{$project->id}/{$fileName}";

        // Store file
        Storage::disk($disk)->put($path, file_get_contents($uploadedFile));

        // Get next version number
        $nextVersion = $file->versions()->max('version') + 1;

        // Create version record
        ProjectFileVersion::create([
            'project_file_id' => $file->id,
            'version' => $nextVersion,
            'file_path' => $path,
            'disk' => $disk,
            'file_size' => $uploadedFile->getSize(),
            'mime_type' => $uploadedFile->getMimeType(),
            'extension' => $extension,
            'hash' => hash_file('sha256', $uploadedFile->getRealPath()),
            'notes' => $request->notes ?? "Version {$nextVersion}",
            'uploaded_by' => auth()->id(),
        ]);

        // Update file's current version
        $file->update([
            'current_version' => $nextVersion,
            'original_name' => $uploadedFile->getClientOriginalName(),
        ]);

        return redirect()
            ->route('projects.files.show', [$project, $file])
            ->with('success', "Versi {$nextVersion} berhasil diunggah.");
    }

    /**
     * Rollback to a previous version
     */
    public function rollback(Project $project, ProjectFile $file, ProjectFileVersion $version)
    {
        $this->authorize('files.update');

        // Protect FINAL files
        if ($file->status === 'final' && !auth()->user()->hasRole(['Superadmin', 'super-admin', 'project-manager'])) {
            return back()->with('error', 'File berstatus FINAL tidak dapat di-rollback.');
        }

        if ($version->project_file_id !== $file->id) {
            abort(404);
        }

        $file->update(['current_version' => $version->version]);

        return redirect()
            ->route('projects.files.show', [$project, $file])
            ->with('success', "Berhasil rollback ke versi {$version->version}.");
    }

    /**
     * Update file status
     */
    public function updateStatus(Request $request, Project $project, ProjectFile $file)
    {
        $this->authorize('files.manage-status');

        $request->validate([
            'status' => 'required|in:draft,review,approved,final',
        ]);

        $newStatus = $request->status;

        if (!$file->canTransitionTo($newStatus)) {
            return back()->with('error', 'Transisi status tidak valid.');
        }

        $file->update([
            'status' => $newStatus,
            'is_final' => $newStatus === 'final',
        ]);

        return back()->with('success', 'Status berhasil diperbarui.');
    }

    /**
     * Download file
     */
    public function download(Project $project, ProjectFile $file, ?ProjectFileVersion $version = null)
    {
        $this->authorize('files.view');

        $version = $version ?? $file->latestVersion;

        if (!$version || !$version->exists()) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk($version->disk)->download(
            $version->file_path,
            $file->original_name
        );
    }

    /**
     * Delete file
     */
    public function destroy(Project $project, ProjectFile $file)
    {
        $this->authorize('files.delete');

        // Protect FINAL files
        if ($file->status === 'final' && !auth()->user()->hasRole(['Superadmin', 'super-admin', 'project-manager'])) {
            return back()->with('error', 'File berstatus FINAL tidak dapat dihapus.');
        }

        $file->delete();

        return redirect()
            ->route('projects.files.index', $project)
            ->with('success', 'File berhasil dihapus.');
    }

    /**
     * Store a comment
     */
    public function storeComment(Request $request, Project $project, ProjectFile $file)
    {
        $this->authorize('files.view');

        $request->validate([
            'comment' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:project_file_comments,id',
            'version_id' => 'nullable|exists:project_file_versions,id',
        ]);

        ProjectFileComment::create([
            'project_file_id' => $file->id,
            'version_id' => $request->version_id,
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }

    /**
     * Resolve/unresolve a comment
     */
    public function toggleResolveComment(Project $project, ProjectFile $file, ProjectFileComment $comment)
    {
        $this->authorize('files.view');

        if ($comment->resolved) {
            $comment->unresolve();
        } else {
            $comment->resolve(auth()->id());
        }

        return back();
    }

    /**
     * Create a folder
     */
    public function createFolder(Request $request, Project $project)
    {
        $this->authorize('files.create');

        $request->validate([
            'name' => 'required|string|max:255',
            'folder_id' => 'nullable|exists:project_files,id',
        ]);

        ProjectFile::create([
            'project_id' => $project->id,
            'folder_id' => $request->folder_id,
            'name' => $request->name,
            'original_name' => $request->name,
            'type' => 'folder',
            'category' => 'other',
            'status' => 'draft',
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Folder berhasil dibuat.');
    }
}
