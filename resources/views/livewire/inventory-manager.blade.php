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
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Manajemen Stok (Inventory)</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Pantau dan sesuaikan stok material per proyek</p>
            </div>
        </div>

        {{-- Filter & Tabs --}}
        <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg mb-6">
            <div class="p-4">
                <div class="flex flex-col md:flex-row md:items-end gap-4">
                    {{-- Search --}}
                    <div class="flex-1">
                        <x-input-label for="search" value="Cari Material" />
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                            </div>
                            <input wire:model.live.debounce.300ms="search" type="text" id="search"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-dark-700 rounded-md leading-5 bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:ring-gold-500 focus:border-gold-500 sm:text-sm"
                                placeholder="Nama atau kode material...">
                        </div>
                    </div>

                    {{-- Project Filter --}}
                    <div class="w-full md:w-64">
                        <x-input-label for="projectFilter" value="Proyek" />
                        <select wire:model.live="projectFilter" id="projectFilter"
                            class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 rounded-md shadow-sm">
                            <option value="">Semua Proyek</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- View Mode Tabs --}}
                <div class="mt-4 border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8">
                        <button wire:click="setViewMode('stock')"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $viewMode === 'stock' ? 'border-gold-500 text-gold-600 dark:text-gold-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400' }}">
                            <x-heroicon-o-cube class="w-5 h-5 inline mr-2" />Data Stok
                        </button>
                        <button wire:click="setViewMode('history')"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $viewMode === 'history' ? 'border-gold-500 text-gold-600 dark:text-gold-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400' }}">
                            <x-heroicon-o-clock class="w-5 h-5 inline mr-2" />Riwayat Log
                        </button>
                    </nav>
                </div>
            </div>
        </div>

        {{-- Stock Table --}}
        @if ($viewMode === 'stock')
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-dark-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Proyek</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Material</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Stok Saat Ini</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($inventories as $inventory)
                                <tr class="hover:bg-gray-50 dark:hover:bg-dark-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $inventory->project->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $inventory->material->name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $inventory->material->code }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <span
                                            class="font-bold {{ $inventory->quantity > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($inventory->quantity, 2) }}
                                        </span>
                                        <span class="text-gray-500 dark:text-gray-400">{{ $inventory->material->unit }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <button wire:click="openAdjustModal({{ $inventory->id }})"
                                            class="text-gold-600 hover:text-gold-900 dark:text-gold-400 text-sm font-medium">Sesuaikan</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Tidak ada
                                        data stok.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $inventories->links() }}</div>
            </div>
        @endif

        {{-- History Table --}}
        @if ($viewMode === 'history')
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-dark-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Waktu</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Proyek</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Material</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Tipe</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Qty</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Catatan</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    User</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50 dark:hover:bg-dark-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $log->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $log->inventory->project->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $log->inventory->material->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($log->type === 'in')
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Masuk</span>
                                        @elseif($log->type === 'out')
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">Keluar</span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">{{ ucfirst($log->type) }}</span>
                                        @endif
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium {{ $log->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $log->quantity > 0 ? '+' : '' }}{{ number_format($log->quantity, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                        {{ $log->notes ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $log->user->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Tidak ada
                                        riwayat log.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $logs->links() }}</div>
            </div>
        @endif
    </div>

    {{-- Adjustment Modal --}}
    @if ($showAdjustModal)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeAdjustModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit="saveAdjustment">
                        <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Sesuaikan Stok</h3>
                                <button type="button" wire:click="closeAdjustModal"
                                    class="text-gray-400 hover:text-gray-500"><x-heroicon-o-x-circle
                                        class="w-6 h-6" /></button>
                            </div>

                            <div class="mb-4 p-3 bg-gray-100 dark:bg-dark-700 rounded-lg">
                                <div class="text-sm text-gray-500 dark:text-gray-400">Material</div>
                                <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $adjustingMaterialName }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Stok saat ini: <span
                                        class="font-bold">{{ number_format($currentStock, 2) }}</span></div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="adjustType" value="Jenis Penyesuaian" />
                                    <select wire:model="adjustType" id="adjustType"
                                        class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 rounded-md shadow-sm">
                                        <option value="in">Masuk (In) - Tambah stok</option>
                                        <option value="out">Keluar (Out) - Kurangi stok</option>
                                        <option value="adjustment">Set Ulang (Opname) - Atur ke nilai baru</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="adjustQuantity" value="Jumlah" />
                                    <x-text-input wire:model="adjustQuantity" id="adjustQuantity" type="number" step="0.01"
                                        min="0" class="mt-1 block w-full" required />
                                    <x-input-error :messages="$errors->get('adjustQuantity')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="adjustNotes" value="Catatan / Alasan" />
                                    <textarea wire:model="adjustNotes" id="adjustNotes" rows="3"
                                        class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 rounded-md shadow-sm"
                                        required></textarea>
                                    <x-input-error :messages="$errors->get('adjustNotes')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-dark-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <x-primary-button type="submit" class="sm:ml-3"
                                wire:loading.attr="disabled">Simpan</x-primary-button>
                            <button type="button" wire:click="closeAdjustModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>