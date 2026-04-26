<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserManager extends Component
{
    use WithPagination;

    // Search & Filter
    public string $search = '';
    public string $roleFilter = '';

    // Modal states
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showResetPasswordModal = false;

    // Form data
    public ?int $editingId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $passwordConfirmation = '';
    public array $selectedRoles = [];

    // Delete
    public ?int $deleteId = null;
    public string $deleteName = '';

    // Reset Password
    public ?int $resetPasswordId = null;
    public string $resetPasswordName = '';
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    // Modal CRUD
    public function openModal(?int $id = null)
    {
        $this->resetValidation();
        $this->reset(['name', 'email', 'password', 'passwordConfirmation', 'selectedRoles', 'editingId']);

        if ($id) {
            $user = User::with('roles')->find($id);
            $this->editingId = $id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->selectedRoles = $user->roles->pluck('name')->toArray();
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['name', 'email', 'password', 'passwordConfirmation', 'selectedRoles', 'editingId']);
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email' . ($this->editingId ? ',' . $this->editingId : ''),
            'selectedRoles' => 'array',
        ];

        if (!$this->editingId) {
            $rules['password'] = 'required|string|min:8|same:passwordConfirmation';
        } elseif ($this->password) {
            $rules['password'] = 'string|min:8|same:passwordConfirmation';
        }

        $this->validate($rules, [
            'name.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.same' => 'Konfirmasi password tidak cocok.',
        ]);

        if ($this->editingId) {
            $user = User::find($this->editingId);
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            if ($this->password) {
                $user->update(['password' => Hash::make($this->password)]);
            }

            $user->syncRoles($this->selectedRoles);
            session()->flash('success', 'User berhasil diperbarui.');
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);

            $user->syncRoles($this->selectedRoles);
            session()->flash('success', 'User berhasil ditambahkan.');
        }

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
        $user = User::find($this->deleteId);
        if ($user && $user->id !== auth()->id()) {
            $user->delete();
            session()->flash('success', 'User berhasil dihapus.');
        } else {
            session()->flash('error', 'Tidak dapat menghapus user yang sedang login.');
        }
        $this->closeDeleteModal();
    }

    // Reset Password
    public function openResetPasswordModal(int $id, string $name)
    {
        $this->resetPasswordId = $id;
        $this->resetPasswordName = $name;
        $this->reset(['newPassword', 'newPasswordConfirmation']);
        $this->showResetPasswordModal = true;
    }

    public function closeResetPasswordModal()
    {
        $this->showResetPasswordModal = false;
        $this->reset(['resetPasswordId', 'resetPasswordName', 'newPassword', 'newPasswordConfirmation']);
    }

    public function resetPassword()
    {
        $this->validate([
            'newPassword' => 'required|string|min:8|same:newPasswordConfirmation',
        ], [
            'newPassword.required' => 'Password baru harus diisi.',
            'newPassword.min' => 'Password minimal 8 karakter.',
            'newPassword.same' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::find($this->resetPasswordId);
        if ($user) {
            $user->update(['password' => Hash::make($this->newPassword)]);
            session()->flash('success', 'Password berhasil direset.');
        }

        $this->closeResetPasswordModal();
    }

    public function render()
    {
        $query = User::with('roles');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->roleFilter) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', $this->roleFilter);
            });
        }

        $users = $query->orderBy('name')->paginate(15);
        $roles = Role::orderBy('name')->get();

        return view('livewire.user-manager', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
}
