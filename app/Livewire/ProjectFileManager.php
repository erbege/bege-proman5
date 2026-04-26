<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectFileVersion;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class ProjectFileManager extends Component
{
    use WithFileUploads, WithPagination;

    public Project $project;

    // Filters
    public string $category = '';
    public string $status = '';
    public string $search = '';
    public ?int $folderId = null;

    // Modal states
    public bool $showUploadModal = false;
    public bool $showFolderModal = false;

    // Upload form
    public $file;
    public string $fileName = '';
    public string $fileCategory = 'document';
    public string $fileDescription = '';

    // Folder form
    public string $folderName = '';
    public ?int $editingFolderId = null;

    // Move file
    public bool $showMoveModal = false;
    public ?int $movingFileId = null;
    public ?int $targetFolderId = null;

    // Delete confirmation
    public bool $showDeleteModal = false;
    public ?int $deletingFileId = null;

    // Upload progress
    public bool $isUploading = false;

    protected $queryString = [
        'category',
        'status',
        'search',
        'folderId' => ['as' => 'folder']
    ];

    public function mount(Project $project, ?int $folder = null)
    {
        $this->project = $project;
        $this->folderId = $folder;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategory()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function openUploadModal()
    {
        $this->reset(['file', 'fileName', 'fileCategory', 'fileDescription']);
        $this->fileCategory = 'document';
        $this->showUploadModal = true;
    }

    public function closeUploadModal()
    {
        $this->showUploadModal = false;
        $this->reset(['file', 'fileName', 'fileCategory', 'fileDescription']);
    }

    public function openFolderModal()
    {
        $this->reset(['folderName']);
        $this->showFolderModal = true;
    }

    public function closeFolderModal()
    {
        $this->showFolderModal = false;
        $this->reset(['folderName', 'editingFolderId']);
    }

    public function uploadFile()
    {
        $maxSize = SystemSetting::getMaxFileSize();
        $maxSizeKb = (int) ($maxSize / 1024);

        $this->validate([
            'file' => "required|file|max:{$maxSizeKb}",
            'fileName' => 'nullable|string|max:255',
            'fileCategory' => 'required|in:planning,design,cad,document,image,other',
            'fileDescription' => 'nullable|string|max:1000',
        ]);

        $this->isUploading = true;

        $disk = SystemSetting::getStorageDisk();
        $extension = $this->file->getClientOriginalExtension();
        $fileName = Str::uuid() . '.' . $extension;
        $path = "project-files/{$this->project->id}/{$fileName}";

        // Store file
        Storage::disk($disk)->put($path, file_get_contents($this->file->getRealPath()));

        // Create file record
        $projectFile = ProjectFile::create([
            'project_id' => $this->project->id,
            'folder_id' => $this->folderId,
            'name' => $this->fileName ?: pathinfo($this->file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_name' => $this->file->getClientOriginalName(),
            'type' => 'file',
            'category' => $this->fileCategory,
            'status' => 'draft',
            'current_version' => 1,
            'description' => $this->fileDescription,
            'uploaded_by' => auth()->id(),
        ]);

        // Create version record
        ProjectFileVersion::create([
            'project_file_id' => $projectFile->id,
            'version' => 1,
            'file_path' => $path,
            'disk' => $disk,
            'file_size' => $this->file->getSize(),
            'mime_type' => $this->file->getMimeType(),
            'extension' => $extension,
            'hash' => hash_file('sha256', $this->file->getRealPath()),
            'notes' => 'Initial upload',
            'uploaded_by' => auth()->id(),
        ]);

        $this->isUploading = false;
        $this->closeUploadModal();

        session()->flash('success', 'File berhasil diunggah.');
    }

    public function getBreadcrumbsProperty()
    {
        $breadcrumbs = [];
        $currentId = $this->folderId;

        while ($currentId) {
            $folder = ProjectFile::find($currentId);
            if (!$folder)
                break;

            array_unshift($breadcrumbs, $folder);
            $currentId = $folder->folder_id;
        }

        return $breadcrumbs;
    }



    public function editFolder(int $folderId)
    {
        $folder = ProjectFile::findOrFail($folderId);
        $this->editingFolderId = $folder->id;
        $this->folderName = $folder->name;
        $this->showFolderModal = true;
    }

    public function saveFolder()
    {
        $this->validate([
            'folderName' => 'required|string|max:255',
        ]);

        if ($this->editingFolderId) {
            // Update existing folder
            $folder = ProjectFile::findOrFail($this->editingFolderId);
            $folder->update([
                'name' => $this->folderName,
                'original_name' => $this->folderName,
            ]);
            $message = 'Folder berhasil diperbarui.';
        } else {
            // Create new folder
            ProjectFile::create([
                'project_id' => $this->project->id,
                'folder_id' => $this->folderId,
                'name' => $this->folderName,
                'original_name' => $this->folderName,
                'type' => 'folder',
                'category' => 'other',
                'status' => 'draft',
                'uploaded_by' => auth()->id(),
            ]);
            $message = 'Folder berhasil dibuat.';
        }

        $this->closeFolderModal();
        session()->flash('success', $message);
    }

    public function deleteFolder(int $folderId)
    {
        $folder = ProjectFile::withCount('children')->findOrFail($folderId);

        if ($folder->children_count > 0) {
            session()->flash('error', 'Folder tidak dapat dihapus karena berisi file atau folder lain.');
            return;
        }

        $folder->delete();
        session()->flash('success', 'Folder berhasil dihapus.');
    }

    public function openMoveModal(int $fileId)
    {
        $this->movingFileId = $fileId;
        $this->targetFolderId = null;
        $this->showMoveModal = true;
    }

    public function closeMoveModal()
    {
        $this->showMoveModal = false;
        $this->reset(['movingFileId', 'targetFolderId']);
    }

    public function moveFile()
    {
        $this->validate([
            'movingFileId' => 'required|exists:project_files,id',
            'targetFolderId' => 'nullable|exists:project_files,id',
        ]);

        $file = ProjectFile::findOrFail($this->movingFileId);

        // Prevent moving folder into itself or its children (basic check)
        if ($file->isFolder() && $file->id == $this->targetFolderId) {
            $this->addError('targetFolderId', 'Tidak dapat memindahkan folder ke dirinya sendiri.');
            return;
        }

        $file->update([
            'folder_id' => $this->targetFolderId,
        ]);

        $this->closeMoveModal();
        session()->flash('success', 'File berhasil dipindahkan.');
    }

    public function getAllFoldersProperty()
    {
        // Get all folders for the move dropdown, excluding current file if it's a folder
        return ProjectFile::where('project_id', $this->project->id)
            ->where('type', 'folder')
            ->when($this->movingFileId, function ($q) {
                return $q->where('id', '!=', $this->movingFileId);
            })
            ->orderBy('name')
            ->get();
    }

    public function confirmDelete(int $fileId)
    {
        $this->deletingFileId = $fileId;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->deletingFileId = null;
    }

    public function deleteFile()
    {
        if (!$this->deletingFileId)
            return;

        $file = ProjectFile::findOrFail($this->deletingFileId);
        $file->delete();

        $this->cancelDelete();
        session()->flash('success', 'File berhasil dihapus.');
    }

    public function render()
    {
        $query = ProjectFile::where('project_id', $this->project->id)
            ->where('type', 'file')
            ->with(['uploader', 'latestVersion']);

        // Filter by folder
        if ($this->folderId) {
            $query->where('folder_id', $this->folderId);
        } else {
            $query->whereNull('folder_id');
        }

        // Filter by category
        if ($this->category) {
            $query->where('category', $this->category);
        }

        // Filter by status
        if ($this->status) {
            $query->where('status', $this->status);
        }

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('original_name', 'like', "%{$this->search}%");
            });
        }

        $files = $query->orderByDesc('updated_at')->paginate(20);

        $folders = ProjectFile::where('project_id', $this->project->id)
            ->where('type', 'folder')
            ->when($this->folderId, fn($q) => $q->where('folder_id', $this->folderId), fn($q) => $q->whereNull('folder_id'))
            ->orderBy('name')
            ->get();

        return view('livewire.project-file-manager', [
            'files' => $files,
            'folders' => $folders,
            'categories' => ProjectFile::$categories,
            'statuses' => ProjectFile::$statuses,
        ]);
    }
}
