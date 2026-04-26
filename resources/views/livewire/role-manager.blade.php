<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- Flash Messages --}}
        @if (session()->has('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">{{ session('error') }}</div>
        @endif

        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Manajemen Role</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kelola role dan assign permissions</p>
            </div>
            <button wire:click="openModal" type="button"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                <x-heroicon-o-plus class="w-4 h-4 mr-2" />Tambah Role
            </button>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg mb-6">
            <div class="p-4">
                <div class="relative max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-dark-700 rounded-md leading-5 bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:ring-gold-500 focus:border-gold-500 sm:text-sm"
                        placeholder="Cari nama role...">
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-dark-700">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Nama Role</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Permissions</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Users</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($roles as $role)
                            <tr class="hover:bg-gray-50 dark:hover:bg-dark-700">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300">
                                        {{ $role->name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-center">
                                    <button wire:click="viewPermissions({{ $role->id }}, '{{ $role->name }}')"
                                        class="text-blue-600 hover:underline">
                                        {{ $role->permissions_count }} permissions
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-sm text-center text-gray-500 dark:text-gray-400">
                                    {{ $role->users_count }} users
                                </td>
                                <td class="px-4 py-3 text-right text-sm space-x-1">
                                    <button wire:click="openModal({{ $role->id }})" title="Edit"
                                        class="text-gold-600 hover:text-gold-900 dark:text-gold-400">
                                        <x-heroicon-o-pencil-square class="w-5 h-5 inline" />
                                    </button>
                                    @if(!in_array($role->name, ['super-admin', 'Superadmin', 'administrator']))
                                        <button wire:click="confirmDelete({{ $role->id }}, '{{ $role->name }}')" title="Hapus"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400">
                                            <x-heroicon-o-trash class="w-5 h-5 inline" />
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Tidak ada
                                    role.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $roles->links() }}</div>
        </div>
    </div>

    {{-- Add/Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit="save">
                        <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $editingId ? 'Edit Role' : 'Tambah Role' }}</h3>
                                <button type="button" wire:click="closeModal"
                                    class="text-gray-400 hover:text-gray-500"><x-heroicon-o-x-circle
                                        class="w-6 h-6" /></button>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="name" value="Nama Role" />
                                    <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full"
                                        required />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label value="Permissions" />
                                    <div
                                        class="mt-2 max-h-64 overflow-y-auto p-3 border border-gray-200 dark:border-dark-600 rounded-md space-y-4">
                                        @foreach($groupedPermissions as $group => $permissions)
                                            <div>
                                                <h4 class="font-medium text-sm text-gray-700 dark:text-gray-300 mb-2 uppercase">
                                                    {{ $group }}</h4>
                                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                                    @foreach($permissions as $permission)
                                                        <label class="flex items-center">
                                                            <input type="checkbox" wire:model.live="selectedPermissions"
                                                                value="{{ $permission->name }}"
                                                                class="rounded border-gray-300 text-gold-600 focus:ring-gold-500">
                                                            <span
                                                                class="ml-2 text-xs text-gray-600 dark:text-gray-400">{{ $permission->name }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-dark-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <x-primary-button type="submit" class="sm:ml-3"
                                wire:loading.attr="disabled">Simpan</x-primary-button>
                            <button type="button" wire:click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeDeleteModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div
                    class="relative inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Hapus Role</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Yakin ingin menghapus role
                                    <strong>{{ $deleteName }}</strong>?</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-dark-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="delete"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">Hapus</button>
                        <button wire:click="closeDeleteModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600">Batal</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- View Permissions Modal --}}
    @if($showPermissionsModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closePermissionsModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Permissions: <span class="text-purple-600">{{ $viewRoleName }}</span>
                            </h3>
                            <button type="button" wire:click="closePermissionsModal"
                                class="text-gray-400 hover:text-gray-500"><x-heroicon-o-x-circle class="w-6 h-6" /></button>
                        </div>
                        <div class="max-h-96 overflow-y-auto space-y-4">
                            @foreach($groupedPermissions as $group => $permissions)
                                <div>
                                    <h4 class="font-medium text-sm text-gray-700 dark:text-gray-300 mb-2 uppercase">{{ $group }}
                                    </h4>
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                        @foreach($permissions as $permission)
                                            <span
                                                class="text-xs px-2 py-1 rounded {{ in_array($permission->name, $viewRolePermissions) ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500' }}">
                                                @if(in_array($permission->name, $viewRolePermissions))
                                                    <x-heroicon-o-check class="w-3 h-3 inline mr-1" />
                                                @endif
                                                {{ $permission->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-dark-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="closePermissionsModal"
                            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>