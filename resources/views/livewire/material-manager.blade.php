<div class="py-4">
    <div class="max-w-full mx-auto sm:px-6 lg:px-8">
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
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Master Data Material</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Data material tersinkron otomatis dari Harga Satuan
                    Dasar AHSP</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button wire:click="export" type="button"
                    class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-2" />
                    Export
                </button>
                <button wire:click="openAddModal" type="button"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                    Tambah
                </button>
                <a href="{{ route('ahsp.prices.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gold-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gold-600 focus:outline-none focus:ring-2 focus:ring-gold-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <x-heroicon-o-arrow-path class="w-4 h-4 mr-2" />
                    Kelola AHSP
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg mb-4">
            <div class="p-4 space-y-4">
                {{-- Search Row --}}
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-dark-700 rounded-md leading-5 bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-gold-500 focus:border-gold-500 sm:text-sm"
                        placeholder="Cari nama atau kode material...">
                </div>

                {{-- Dropdowns Row - 3 columns on desktop --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Region Filter --}}
                    <div>
                        <select wire:model.live="regionFilter"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-dark-700 rounded-md bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-gold-500 focus:border-gold-500 sm:text-sm">
                            <option value="">Semua Wilayah</option>
                            @foreach ($regions as $region)
                                <option value="{{ $region->region_code }}">
                                    {{ $region->region_name ?? $region->region_code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Category Filter --}}
                    <div>
                        <select wire:model.live="categoryFilter"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-dark-700 rounded-md bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-gold-500 focus:border-gold-500 sm:text-sm">
                            <option value="">Semua Kategori</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
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
                <div class="mt-4 flex items-center justify-between">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.live="showTrashed"
                            class="rounded border-gray-300 dark:border-dark-700 text-gold-600 shadow-sm focus:ring-gold-500 dark:focus:ring-gold-600">
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-trash class="w-4 h-4 inline mr-1" />
                            Tampilkan data yang dihapus
                        </span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Bulk Action Bar --}}
        @if(count($selectedIds) > 0)
            <div
                class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg flex items-center justify-between">
                <div class="flex items-center gap-2 text-red-700 dark:text-red-300">
                    <x-heroicon-o-check-circle class="w-5 h-5" />
                    <span class="text-sm font-medium">{{ count($selectedIds) }} item dipilih</span>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="$set('selectedIds', [])" type="button"
                        class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        Batal Pilih
                    </button>
                    <button wire:click="confirmBulkDelete" type="button"
                        class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition">
                        <x-heroicon-o-trash class="w-4 h-4 mr-1" />
                        Hapus Terpilih
                    </button>
                </div>
            </div>
        @endif

        {{-- Table --}}
        <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-dark-700">
                        <tr>
                                <th class="px-3 py-2 text-center w-10">
                                    <input type="checkbox" wire:model.live="selectAll" wire:click="toggleSelectAll"
                                        class="rounded border-gray-300 dark:border-dark-700 text-gold-600 shadow-sm focus:ring-gold-500">
                                </th>
                                <th
                                    class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Kode</th>
                                <th
                                    class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Nama</th>
                                <th
                                    class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Kategori</th>
                                <th
                                    class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Wilayah</th>
                                <th
                                    class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Satuan</th>
                                <th
                                    class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Harga</th>
                                <th
                                    class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Min Stok</th>
                                <th
                                    class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Status</th>
                                <th
                                    class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($materials as $material)
                            <tr
                                class="hover:bg-gray-50 dark:hover:bg-dark-700 {{ $material->trashed() ? 'opacity-60' : '' }} {{ in_array($material->id, $selectedIds) ? 'bg-gold-50 dark:bg-gold-900/20' : '' }}">
                                <td class="px-3 py-2 text-center">
                                    <input type="checkbox" wire:model.live="selectedIds" value="{{ $material->id }}"
                                        class="rounded border-gray-300 dark:border-dark-700 text-gold-600 shadow-sm focus:ring-gold-500">
                                </td>
                                <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $material->code }}
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-white">
                                    {{ $material->name }}
                                    @if ($material->trashed())
                                        <span class="ml-2 text-xs text-red-500">(Dihapus)</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $material->category }}
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                                    @if($material->region_name)
                                        <span
                                            class="px-2 py-0.5 text-xs rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                            {{ $material->region_name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-white text-center">
                                    {{ $material->unit }}
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-white text-right">
                                    Rp {{ number_format($material->unit_price ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-center"
                                    x-data="{ editing: false, value: {{ $material->min_stock ?? 0 }} }">
                                    @if(!$material->trashed())
                                        <div x-show="!editing" @click="editing = true"
                                            class="cursor-pointer hover:bg-gray-100 dark:hover:bg-dark-700 px-2 py-1 rounded text-sm"
                                            title="Klik untuk edit">
                                            {{ number_format($material->min_stock ?? 0, 0, ',', '.') }}
                                        </div>
                                        <input x-show="editing" x-ref="minStockInput" type="number" step="0.01" min="0"
                                            x-model="value"
                                            @blur="editing = false; $wire.updateMinStock({{ $material->id }}, value)"
                                            @keydown.enter="editing = false; $wire.updateMinStock({{ $material->id }}, value)"
                                            @keydown.escape="editing = false"
                                            x-init="$watch('editing', v => { if(v) setTimeout(() => $refs.minStockInput.focus(), 50) })"
                                            class="w-20 px-2 py-1 text-sm text-center border border-gray-300 dark:border-dark-600 rounded bg-white dark:bg-dark-900 text-gray-900 dark:text-white focus:ring-gold-500 focus:border-gold-500">
                                    @else
                                        <span
                                            class="text-sm text-gray-400">{{ number_format($material->min_stock ?? 0, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @if ($material->is_active)
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Aktif</span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right text-sm space-x-2">
                                    @if ($material->trashed())
                                        <button wire:click="restore({{ $material->id }})" title="Restore"
                                            class="text-green-600 hover:text-green-900 dark:text-green-400">
                                            <x-heroicon-o-arrow-path class="w-5 h-5" />
                                        </button>
                                        <button wire:click="forceDelete({{ $material->id }})" title="Hapus Permanen"
                                            wire:confirm="Yakin ingin menghapus permanen material ini? Data AHSP terkait juga akan terhapus."
                                            class="text-red-600 hover:text-red-900 dark:text-red-400">
                                            <x-heroicon-o-x-circle class="w-5 h-5" />
                                        </button>
                                    @else
                                        <button wire:click="showDetail({{ $material->id }})" title="Lihat Detail"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                            <x-heroicon-o-eye class="w-5 h-5" />
                                        </button>
                                        <button
                                            wire:click="confirmDelete({{ $material->id }}, '{{ addslashes($material->name) }}')"
                                            title="Hapus" class="text-red-600 hover:text-red-900 dark:text-red-400">
                                            <x-heroicon-o-trash class="w-5 h-5" />
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    @if ($showTrashed)
                                        Tidak ada material yang dihapus.
                                    @else
                                        Belum ada material. Import dari
                                        <a href="{{ route('ahsp.prices.index') }}" class="text-blue-600 hover:underline">Harga
                                            Satuan Dasar AHSP</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                {{ $materials->links() }}
            </div>
        </div>
    </div>

    {{-- Detail Modal --}}
    @if ($showDetailModal && $viewingMaterial)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeDetailModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Detail Material</h3>
                            <button type="button" wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-500">
                                <x-heroicon-o-x-circle class="w-6 h-6" />
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Kode</label>
                                    <p class="text-gray-900 dark:text-white font-medium">{{ $viewingMaterial->code }}</p>
                                </div>
                                <div>
                                    <label
                                        class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</label>
                                    <p>
                                        @if ($viewingMaterial->is_active)
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Aktif</span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">Nonaktif</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nama
                                    Material</label>
                                <p class="text-gray-900 dark:text-white">{{ $viewingMaterial->name }}</p>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label
                                        class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Kategori</label>
                                    <p class="text-gray-900 dark:text-white">{{ $viewingMaterial->category ?? '-' }}</p>
                                </div>
                                <div>
                                    <label
                                        class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Satuan</label>
                                    <p class="text-gray-900 dark:text-white">{{ $viewingMaterial->unit }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Harga
                                        Satuan</label>
                                    <p class="text-gray-900 dark:text-white font-semibold">Rp
                                        {{ number_format($viewingMaterial->unit_price ?? 0, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>

                            @if($viewingMaterial->region_name || $viewingMaterial->region_code)
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Wilayah</label>
                                        <p class="text-gray-900 dark:text-white">
                                            {{ $viewingMaterial->region_name ?? $viewingMaterial->region_code }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tanggal
                                            Efektif</label>
                                        <p class="text-gray-900 dark:text-white">
                                            {{ $viewingMaterial->effective_date?->format('d M Y') ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            @if($viewingMaterial->source)
                                <div>
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Sumber
                                        Data</label>
                                    <p class="text-gray-900 dark:text-white">{{ $viewingMaterial->source }}</p>
                                </div>
                            @endif

                            @if($viewingMaterial->description)
                                <div>
                                    <label
                                        class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Deskripsi</label>
                                    <p class="text-gray-900 dark:text-white">{{ $viewingMaterial->description }}</p>
                                </div>
                            @endif

                            @if($viewingMaterial->ahspBasePrice)
                                <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                                    <label class="text-xs font-medium text-blue-700 dark:text-blue-300 uppercase">Terhubung ke
                                        AHSP</label>
                                    <p class="text-blue-800 dark:text-blue-200 text-sm mt-1">
                                        {{ $viewingMaterial->ahspBasePrice->code }} -
                                        {{ $viewingMaterial->ahspBasePrice->name }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-dark-700 px-3 py-1.5 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="closeDetailModal"
                            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold-500 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600 dark:hover:bg-gray-700">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Add Modal --}}
    @if ($showAddModal)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeAddModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit="save">
                        <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Tambah Material Manual</h3>
                                <button type="button" wire:click="closeAddModal" class="text-gray-400 hover:text-gray-500">
                                    <x-heroicon-o-x-circle class="w-6 h-6" />
                                </button>
                            </div>

                            <div
                                class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3 mb-4">
                                <p class="text-xs text-yellow-700 dark:text-yellow-300">
                                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 inline mr-1" />
                                    Sebaiknya gunakan import dari <a href="{{ route('ahsp.prices.index') }}"
                                        class="underline">Harga Satuan Dasar AHSP</a> agar data tersinkron otomatis.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="code" value="Kode Material" />
                                    <x-text-input wire:model="code" id="code" type="text" class="mt-1 block w-full"
                                        placeholder="Kosongkan untuk otomatis" />
                                    <p class="mt-1 text-xs text-gray-500">Kosongkan untuk kode otomatis (MAT-XXXX)</p>
                                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="name" value="Nama Material" />
                                    <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full"
                                        required />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="category" value="Kategori" />
                                    <x-text-input wire:model="category" id="category" type="text" class="mt-1 block w-full"
                                        list="categories" required />
                                    <datalist id="categories">
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat }}">
                                        @endforeach
                                    </datalist>
                                    <x-input-error :messages="$errors->get('category')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="unit" value="Satuan" />
                                    <select wire:model="unit" id="unit"
                                        class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm"
                                        required>
                                        @foreach ($units as $u)
                                            <option value="{{ $u }}">{{ $u }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="unitPrice" value="Harga Satuan (Rp)" />
                                    <x-text-input wire:model="unitPrice" id="unitPrice" type="number" step="0.01" min="0"
                                        class="mt-1 block w-full" required />
                                    <x-input-error :messages="$errors->get('unitPrice')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="minStock" value="Stok Minimum" />
                                    <x-text-input wire:model="minStock" id="minStock" type="number" step="0.01" min="0"
                                        class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('minStock')" class="mt-2" />
                                </div>

                                <div class="md:col-span-2">
                                    <x-input-label for="description" value="Deskripsi" />
                                    <textarea wire:model="description" id="description" rows="2"
                                        class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm"></textarea>
                                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-dark-700 px-3 py-1.5 sm:px-6 sm:flex sm:flex-row-reverse">
                            <x-primary-button type="submit" class="sm:ml-3" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="save">Simpan</span>
                                <span wire:loading wire:target="save">Menyimpan...</span>
                            </x-primary-button>
                            <button type="button" wire:click="closeAddModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold-500 sm:mt-0 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600 dark:hover:bg-gray-700">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeDeleteModal"></div>
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
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Hapus Material</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Apakah Anda yakin ingin menghapus material <strong>{{ $deleteName }}</strong>?
                                    </p>
                                    <p class="text-sm text-orange-600 dark:text-orange-400 mt-2">
                                        <x-heroicon-o-exclamation-triangle class="w-4 h-4 inline mr-1" />
                                        Data AHSP terkait juga akan dihapus.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-dark-700 px-3 py-1.5 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="delete"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Hapus
                        </button>
                        <button type="button" wire:click="closeDeleteModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold-500 sm:mt-0 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600 dark:hover:bg-gray-700">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Bulk Delete Confirmation Modal --}}
    @if ($showBulkDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeBulkDeleteModal">
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div
                    class="relative inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" wire:click="closeBulkDeleteModal" class="text-gray-400 hover:text-gray-500">
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
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Hapus
                                    {{ count($selectedIds) }} Material
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Apakah Anda yakin ingin menghapus <strong>{{ count($selectedIds) }}</strong>
                                        material yang dipilih?
                                    </p>
                                    <p class="text-sm text-orange-600 dark:text-orange-400 mt-2">
                                        <x-heroicon-o-exclamation-triangle class="w-4 h-4 inline mr-1" />
                                        Data AHSP terkait juga akan dihapus.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-dark-700 px-3 py-1.5 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="bulkDelete"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <span wire:loading.remove wire:target="bulkDelete">Hapus Semua</span>
                            <span wire:loading wire:target="bulkDelete">Menghapus...</span>
                        </button>
                        <button type="button" wire:click="closeBulkDeleteModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold-500 sm:mt-0 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600 dark:hover:bg-gray-700">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Info: Data synced from AHSP --}}
    <div class="fixed bottom-4 right-4 z-10">
        <div
            class="bg-blue-50 dark:bg-blue-900/50 border border-blue-200 dark:border-blue-800 rounded-lg p-3 shadow-lg max-w-xs">
            <div class="flex items-start gap-2">
                <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" />
                <div class="text-xs text-blue-700 dark:text-blue-300">
                    <strong>Info:</strong> Data material otomatis tersinkron dari
                    <a href="{{ route('ahsp.prices.index') }}" class="underline hover:no-underline">Harga Satuan Dasar
                        AHSP</a>.
                </div>
            </div>
        </div>
    </div>
</div>


