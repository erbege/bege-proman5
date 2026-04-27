<div class="py-4">
    <div class="max-w-full mx-auto sm:px-6 lg:px-8">
        {{-- Flash Messages --}}
        @if (session()->has('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">{{ session('error') }}</div>
        @endif

        {{-- Header --}}
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Tim Proyek</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kelola anggota tim untuk proyek {{ $project->name }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button wire:click="openModal" type="button"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />Tambah Anggota
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg mb-4">
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                            </div>
                            <input wire:model.live.debounce.300ms="search" type="text"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-dark-700 rounded-md leading-5 bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:ring-gold-500 focus:border-gold-500 sm:text-sm"
                                placeholder="Cari nama atau email...">
                        </div>
                    </div>
                    <div>
                        <select wire:model.live="roleFilter"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-dark-700 rounded-md bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 focus:ring-gold-500 focus:border-gold-500 sm:text-sm">
                            <option value="">Semua Role</option>
                            @foreach($roles as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <select wire:model.live="statusFilter"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-dark-700 rounded-md bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 focus:ring-gold-500 focus:border-gold-500 sm:text-sm">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
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
                                class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Nama</th>
                            <th
                                class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Email</th>
                            <th
                                class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Role</th>
                            <th
                                class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Periode</th>
                            <th
                                class="px-3 py-1.5 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Status</th>
                            <th
                                class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($members as $member)
                            <tr class="hover:bg-gray-50 dark:hover:bg-dark-700">
                                <td class="px-3 py-1.5 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $member->user->name }}
                                </td>
                                <td class="px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400">{{ $member->user->email }}
                                </td>
                                <td class="px-3 py-1.5 text-sm">
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                        {{ $member->role_label }}
                                    </span>
                                </td>
                                <td class="px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400">
                                    @if($member->assigned_from || $member->assigned_until)
                                        {{ $member->assigned_from?->format('d M Y') ?? '-' }}
                                        <span class="text-gray-400">s/d</span>
                                        {{ $member->assigned_until?->format('d M Y') ?? '-' }}
                                    @else
                                        <span class="text-gray-400">Tidak ditentukan</span>
                                    @endif
                                </td>
                                <td class="px-3 py-1.5 text-center">
                                    <button wire:click="toggleStatus({{ $member->id }})" class="cursor-pointer">
                                        @if($member->is_active)
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Aktif</span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">Nonaktif</span>
                                        @endif
                                    </button>
                                </td>
                                <td class="px-3 py-1.5 text-right text-sm space-x-2">
                                    <button wire:click="openModal({{ $member->id }})" title="Edit"
                                        class="text-gold-600 hover:text-gold-900 dark:text-gold-400">
                                        <x-heroicon-o-pencil-square class="w-5 h-5" />
                                    </button>
                                    <button wire:click="confirmDelete({{ $member->id }}, '{{ $member->user->name }}')"
                                        title="Hapus" class="text-red-600 hover:text-red-900 dark:text-red-400">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Belum ada anggota tim.
                                    <button wire:click="openModal" class="text-blue-600 hover:underline">Tambah
                                        anggota</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $members->links() }}</div>
        </div>
    </div>

    {{-- Add/Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit="save">
                        <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $editingId ? 'Edit Anggota Tim' : 'Tambah Anggota Tim' }}
                                </h3>
                                <button type="button" wire:click="closeModal"
                                    class="text-gray-400 hover:text-gray-500"><x-heroicon-o-x-circle
                                        class="w-6 h-6" /></button>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="userId" value="User" />
                                    <select wire:model="userId" id="userId"
                                        class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 rounded-md shadow-sm"
                                        @if($editingId) disabled @endif>
                                        <option value="">Pilih User</option>
                                        @foreach($availableUsers as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}
                                                ({{ $user->roles->pluck('name')->first() ?? 'No Role' }})</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('userId')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="role" value="Role" />
                                    <select wire:model="role" id="role"
                                        class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 rounded-md shadow-sm">
                                        <option value="">Pilih Role</option>
                                        @foreach($roles as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="assignedFrom" value="Tanggal Mulai" />
                                        <x-text-input wire:model="assignedFrom" id="assignedFrom" type="date"
                                            class="mt-1 block w-full" />
                                        <x-input-error :messages="$errors->get('assignedFrom')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="assignedUntil" value="Tanggal Selesai" />
                                        <x-text-input wire:model="assignedUntil" id="assignedUntil" type="date"
                                            class="mt-1 block w-full" />
                                        <x-input-error :messages="$errors->get('assignedUntil')" class="mt-2" />
                                    </div>
                                </div>
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="isActive"
                                            class="rounded border-gray-300 text-gold-600 focus:ring-gold-500">
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Anggota Aktif</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-dark-700 px-3 py-1.5 sm:px-6 sm:flex sm:flex-row-reverse">
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
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" wire:click="closeDeleteModal" class="text-gray-400 hover:text-gray-500">
                            <x-heroicon-o-x-circle class="w-6 h-6" />
                        </button>
                    </div>
                    <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Hapus Anggota Tim</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Yakin ingin menghapus
                                    <strong>{{ $deleteName }}</strong> dari tim proyek?
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-dark-700 px-3 py-1.5 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="delete"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">Hapus</button>
                        <button wire:click="closeDeleteModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600">Batal</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>


