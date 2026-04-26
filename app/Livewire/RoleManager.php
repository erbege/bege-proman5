<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleManager extends Component
{
    use WithPagination;

    // Search
    public string $search = '';

    // Modal states
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showPermissionsModal = false;

    // Form data
    public ?int $editingId = null;
    public string $name = '';
    public array $selectedPermissions = [];

    // Delete
    public ?int $deleteId = null;
    public string $deleteName = '';

    // Permissions Modal
    public ?int $viewRoleId = null;
    public string $viewRoleName = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Modal CRUD
    public function openModal(?int $id = null)
    {
        $this->resetValidation();
        $this->reset(['name', 'selectedPermissions', 'editingId']);

        if ($id) {
            $role = Role::with('permissions')->find($id);
            $this->editingId = $id;
            $this->name = $role->name;
            $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['name', 'selectedPermissions', 'editingId']);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:roles,name' . ($this->editingId ? ',' . $this->editingId : ''),
        ], [
            'name.required' => 'Nama role harus diisi.',
            'name.unique' => 'Nama role sudah digunakan.',
        ]);

        if ($this->editingId) {
            $role = Role::find($this->editingId);
            $role->update(['name' => $this->name]);
            $role->syncPermissions($this->selectedPermissions);
            session()->flash('success', 'Role berhasil diperbarui.');
        } else {
            $role = Role::create(['name' => $this->name, 'guard_name' => 'web']);
            $role->syncPermissions($this->selectedPermissions);
            session()->flash('success', 'Role berhasil ditambahkan.');
        }

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->closeModal();
    }

    // Delete
    public function confirmDelete(int $id, string $name)
    {
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
        $role = Role::find($this->deleteId);

        // Prevent deletion of essential roles
        $protectedRoles = ['super-admin', 'Superadmin', 'administrator'];
        if ($role && !in_array($role->name, $protectedRoles)) {
            $role->delete();
            session()->flash('success', 'Role berhasil dihapus.');
        } else {
            session()->flash('error', 'Role ini tidak dapat dihapus.');
        }

        $this->closeDeleteModal();
    }

    // View Permissions
    public function viewPermissions(int $id, string $name)
    {
        $this->viewRoleId = $id;
        $this->viewRoleName = $name;
        $this->showPermissionsModal = true;
    }

    public function closePermissionsModal()
    {
        $this->showPermissionsModal = false;
        $this->reset(['viewRoleId', 'viewRoleName']);
    }

    public function getGroupedPermissions(): array
    {
        $permissions = Permission::orderBy('name')->get();
        $grouped = [];

        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $group = $parts[0] ?? 'other';
            $grouped[$group][] = $permission;
        }

        return $grouped;
    }

    public function render()
    {
        $query = Role::withCount(['permissions', 'users']);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $roles = $query->orderBy('name')->paginate(15);

        $viewRolePermissions = [];
        if ($this->viewRoleId) {
            $viewRolePermissions = Role::find($this->viewRoleId)?->permissions->pluck('name')->toArray() ?? [];
        }

        return view('livewire.role-manager', [
            'roles' => $roles,
            'groupedPermissions' => $this->getGroupedPermissions(),
            'viewRolePermissions' => $viewRolePermissions,
        ]);
    }
}
