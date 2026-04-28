<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\ProjectTeam;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectTeamManager extends Component
{
    use WithPagination;

    // Project context
    public int $projectId;

    // Search & Filter
    public string $search = '';
    public string $roleFilter = '';
    public string $statusFilter = '';

    // Modal states
    public bool $showModal = false;
    public bool $showDeleteModal = false;

    // Form data
    public ?int $editingId = null;
    public ?int $userId = null;
    public string $role = '';
    public ?string $assignedFrom = null;
    public ?string $assignedUntil = null;
    public bool $isActive = true;

    // Delete
    public ?int $deleteId = null;
    public string $deleteName = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount(int $projectId)
    {
        $this->projectId = $projectId;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    // Modal CRUD
    public function openModal(?int $id = null)
    {
        if (!auth()->user()->can('financials.manage')) {
            return;
        }
        $this->resetValidation();
        $this->reset(['userId', 'role', 'assignedFrom', 'assignedUntil', 'isActive', 'editingId']);

        if ($id) {
            $member = ProjectTeam::find($id);
            $this->editingId = $id;
            $this->userId = $member->user_id;
            $this->role = $member->role;
            $this->assignedFrom = optional($member->assigned_from)->format('Y-m-d');
            $this->assignedUntil = optional($member->assigned_until)->format('Y-m-d');
            $this->isActive = $member->is_active;
        } else {
            // Set project dates as defaults for new members
            $project = Project::find($this->projectId);
            $this->assignedFrom = $project->start_date?->format('Y-m-d');
            $this->assignedUntil = $project->end_date?->format('Y-m-d');
            $this->isActive = true;
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['userId', 'role', 'assignedFrom', 'assignedUntil', 'isActive', 'editingId']);
    }

    public function save()
    {
        if (!auth()->user()->can('financials.manage')) {
            abort(403);
        }
        $rules = [
            'userId' => 'required|exists:users,id',
            'role' => 'required|string|in:' . implode(',', array_keys(ProjectTeam::getRoles())),
            'assignedFrom' => 'nullable|date',
            'assignedUntil' => 'nullable|date|after_or_equal:assignedFrom',
        ];

        // Check unique constraint only for new entries or when changing user/role
        if (!$this->editingId) {
            $rules['userId'] = 'required|exists:users,id|unique:project_team,user_id,NULL,id,project_id,' . $this->projectId . ',role,' . $this->role;
        }

        $this->validate($rules, [
            'userId.required' => 'User harus dipilih.',
            'userId.unique' => 'User ini sudah ditugaskan dengan role yang sama.',
            'role.required' => 'Role harus dipilih.',
            'role.in' => 'Role tidak valid.',
            'assignedUntil.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ]);

        $data = [
            'project_id' => $this->projectId,
            'user_id' => $this->userId,
            'role' => $this->role,
            'assigned_from' => $this->assignedFrom ?: null,
            'assigned_until' => $this->assignedUntil ?: null,
            'is_active' => $this->isActive,
        ];

        if ($this->editingId) {
            $member = ProjectTeam::find($this->editingId);
            $member->update($data);
            session()->flash('success', 'Anggota tim berhasil diperbarui.');
        } else {
            ProjectTeam::create($data);

            // Notify new team member about project assignment
            $assignedUser = User::find($this->userId);
            $project = Project::find($this->projectId);
            if ($assignedUser && $project) {
                \App\Services\NotificationHelper::sendToUser(
                    $assignedUser,
                    new \App\Notifications\ProjectAssignmentNotification($project, $this->role)
                );
            }

            session()->flash('success', 'Anggota tim berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    // Delete
    public function confirmDelete(int $id, string $name)
    {
        if (!auth()->user()->can('financials.manage')) {
            return;
        }
        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->reset(['deleteId', 'deleteName']);
    }

    public function delete()
    {
        if (!auth()->user()->can('financials.manage')) {
            abort(403);
        }
        ProjectTeam::find($this->deleteId)?->delete();
        session()->flash('success', 'Anggota tim berhasil dihapus.');
        $this->closeDeleteModal();
    }

    public function toggleStatus(int $id)
    {
        if (!auth()->user()->can('financials.manage')) {
            return;
        }
        $member = ProjectTeam::find($id);
        if ($member) {
            $member->update(['is_active' => !$member->is_active]);
            session()->flash('success', 'Status anggota tim berhasil diubah.');
        }
    }

    public function render()
    {
        $query = ProjectTeam::where('project_id', $this->projectId)
            ->with('user');

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->roleFilter) {
            $query->where('role', $this->roleFilter);
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        $members = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get available users (exclude already assigned with same role)
        $assignedUserIds = ProjectTeam::where('project_id', $this->projectId)
            ->when($this->editingId, function ($q) {
                $q->where('id', '!=', $this->editingId);
            })
            ->pluck('user_id')
            ->toArray();

        $availableUsers = User::with('roles')
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['Superadmin', 'super-admin', 'administrator', 'viewer']);
            })
            ->orderBy('name')
            ->get();

        return view('livewire.project-team-manager', [
            'members' => $members,
            'availableUsers' => $availableUsers,
            'roles' => ProjectTeam::getRoles(),
            'project' => Project::find($this->projectId),
        ]);
    }
}
