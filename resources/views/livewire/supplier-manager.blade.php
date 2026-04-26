<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- Flash Messages --}}
        @if (session()->has('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Master Data Supplier</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kelola data supplier/vendor</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button wire:click="openImportModal" type="button"
                    class="inline-flex items-center px-4 py-2 bg-gold-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gold-600 focus:outline-none focus:ring-2 focus:ring-gold-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-2" />
                    Import
                </button>
                <button wire:click="export" type="button"
                    class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-2" />
                    Export
                </button>
                <button wire:click="openModal" type="button"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                    Tambah Supplier
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg mb-6">
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Search --}}
                    <div class="md:col-span-2">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                            </div>
                            <input wire:model.live.debounce.300ms="search" type="text"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-dark-700 rounded-md leading-5 bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-gold-500 focus:border-gold-500 sm:text-sm"
                                placeholder="Cari nama, kode, atau kontak...">
                        </div>
                    </div>

                    {{-- Status Filter --}}
                    <div>
                        <select wire:model.live="statusFilter"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-dark-700 rounded-md bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-gold-500 focus:border-gold-500 sm:text-sm">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>

                {{-- Trash Toggle --}}
                <div class="mt-4 flex items-center">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.live="showTrashed"
                            class="rounded border-gray-300 dark:border-dark-700 text-gold-600 shadow-sm focus:ring-gold-500">
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-trash class="w-4 h-4 inline mr-1" />
                            Tampilkan data yang dihapus
                        </span>
                    </label>
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
                                Kode</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Nama</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Kontak</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Telepon</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Kota</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Status</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($suppliers as $supplier)
                            <tr
                                class="hover:bg-gray-50 dark:hover:bg-dark-700 {{ $supplier->trashed() ? 'opacity-60' : '' }}">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $supplier->code }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ $supplier->name }}
                                    @if ($supplier->trashed())
                                        <span class="ml-2 text-xs text-red-500">(Dihapus)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $supplier->contact_person ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $supplier->phone ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $supplier->city ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($supplier->is_active)
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Aktif</span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm space-x-2">
                                    @if ($supplier->trashed())
                                        <button wire:click="restore({{ $supplier->id }})" title="Restore"
                                            class="text-green-600 hover:text-green-900 dark:text-green-400"><x-heroicon-o-arrow-path
                                                class="w-5 h-5" /></button>
                                        <button wire:click="forceDelete({{ $supplier->id }})" title="Hapus Permanen"
                                            wire:confirm="Yakin ingin menghapus permanen?"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400"><x-heroicon-o-x-circle
                                                class="w-5 h-5" /></button>
                                    @else
                                        <button wire:click="openModal({{ $supplier->id }})" title="Edit"
                                            class="text-gold-600 hover:text-gold-900 dark:text-gold-400"><x-heroicon-o-pencil-square
                                                class="w-5 h-5" /></button>
                                        <button wire:click="confirmDelete({{ $supplier->id }}, '{{ $supplier->name }}')"
                                            title="Hapus"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400"><x-heroicon-o-trash
                                                class="w-5 h-5" /></button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    @if ($showTrashed)
                                        Tidak ada supplier yang dihapus.
                                    @else
                                        Belum ada supplier. <button wire:click="openModal"
                                            class="text-blue-600 hover:underline">Tambah supplier</button>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>

    {{-- Add/Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit="save">
                        <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $editingId ? 'Edit Supplier' : 'Tambah Supplier' }}
                                </h3>
                                <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                                    <x-heroicon-o-x-circle class="w-6 h-6" />
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="code" value="Kode Supplier" />
                                    <x-text-input wire:model="code" id="code" type="text" class="mt-1 block w-full"
                                        placeholder="Kosongkan untuk otomatis" :disabled="$editingId" />
                                    @if (!$editingId)
                                    <p class="mt-1 text-xs text-gray-500">Kosongkan untuk kode otomatis (SUP-XXXX)</p>@endif
                                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="name" value="Nama Supplier" />
                                    <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full"
                                        required />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="contactPerson" value="Nama Kontak" />
                                    <x-text-input wire:model="contactPerson" id="contactPerson" type="text"
                                        class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label for="phone" value="Telepon" />
                                    <x-text-input wire:model="phone" id="phone" type="text" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label for="email" value="Email" />
                                    <x-text-input wire:model="email" id="email" type="email" class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="city" value="Kota" />
                                    <x-text-input wire:model="city" id="city" type="text" class="mt-1 block w-full" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="address" value="Alamat" />
                                    <textarea wire:model="address" id="address" rows="2"
                                        class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 rounded-md shadow-sm"></textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="notes" value="Catatan" />
                                    <textarea wire:model="notes" id="notes" rows="2"
                                        class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 rounded-md shadow-sm"></textarea>
                                </div>
                                @if ($editingId)
                                    <div class="md:col-span-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" wire:model="isActive"
                                                class="rounded border-gray-300 text-gold-600 focus:ring-gold-500">
                                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Supplier Aktif</span>
                                        </label>
                                    </div>
                                @endif
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
    @if ($showDeleteModal)
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
                    <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Hapus Supplier</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Yakin ingin menghapus
                                    <strong>{{ $deleteName }}</strong>?
                                </p>
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

    {{-- Import Modal --}}
    @if ($showImportModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeImportModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div
                    class="relative inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" wire:click="closeImportModal" class="text-gray-400 hover:text-gray-500">
                            <x-heroicon-o-x-circle class="w-6 h-6" />
                        </button>
                    </div>
                    <form wire:submit="import">
                        <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Import Supplier</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Upload file Excel dengan kolom:
                                <strong>code, name, contact_person, phone, email, address, city</strong>
                            </p>
                            <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <x-input-error :messages="$errors->get('importFile')" class="mt-2" />
                        </div>
                        <div class="bg-gray-50 dark:bg-dark-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm"
                                wire:loading.attr="disabled">Upload</button>
                            <button type="button" wire:click="closeImportModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>