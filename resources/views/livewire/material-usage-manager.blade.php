<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session()->has('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">{{ session('error') }}</div>
            @endif

            {{-- Header --}}
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Penggunaan Material -
                        {{ $project->name }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->code }}</p>
                </div>
                <button wire:click="openModal"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-md uppercase hover:bg-blue-700">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />Catat Penggunaan
                </button>
            </div>

            {{-- Filter --}}
            <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg mb-6 p-4">
                <div class="relative max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-dark-700 rounded-md bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:ring-gold-500 focus:border-gold-500 sm:text-sm"
                        placeholder="Cari nomor atau pekerjaan...">
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-dark-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    No.</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Tanggal</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Pekerjaan (RAB)</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Item</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Dibuat Oleh</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Catatan</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 cursor-pointer">
                            @forelse($usages as $usage)
                                <tr class="hover:bg-gray-50 dark:hover:bg-dark-700"
                                    wire:click="showDetail({{ $usage->id }})">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $usage->usage_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $usage->usage_date->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                        {{ $usage->rabItem->work_name ?? '-' }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                        {{ $usage->items->count() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $usage->createdBy->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                        {{ $usage->notes ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click.stop="showDetail({{ $usage->id }})"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                            title="Detail"><x-heroicon-o-eye class="w-5 h-5" /></button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada
                                        penggunaan material.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $usages->links() }}</div>
            </div>
        </div>
    </div>

    {{-- Add Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <form wire:submit="save">
                        <div
                            class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6 max-h-[80vh] overflow-y-auto scrollbar-overlay">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Catat Penggunaan Material</h3>
                                <button type="button" wire:click="closeModal"
                                    class="text-gray-400 hover:text-gray-500"><x-heroicon-o-x-circle
                                        class="w-6 h-6" /></button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div>
                                    <x-input-label for="usageDate" value="Tanggal Penggunaan" />
                                    <x-text-input wire:model="usageDate" id="usageDate" type="date"
                                        class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <x-input-label for="rabItemId" value="Pekerjaan (RAB Item)" />
                                    <x-searchable-select
                                        wire:model="rabItemId"
                                        :options="$rabItemOptions"
                                        placeholder="-- Pilih Pekerjaan --"
                                        class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="notes" value="Catatan" />
                                    <x-text-input wire:model="notes" id="notes" type="text" class="mt-1 block w-full" />
                                </div>
                            </div>

                            {{-- Items --}}
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-md font-medium text-gray-900 dark:text-white">Material yang Digunakan
                                    </h4>
                                    <button type="button" wire:click="addItem"
                                        class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700"><x-heroicon-o-plus
                                            class="w-4 h-4 mr-1" />Tambah</button>
                                </div>
                                <div class="space-y-3">
                                    @foreach($items as $index => $item)
                                        <div class="grid grid-cols-12 gap-2 items-end bg-gray-50 dark:bg-dark-700 p-3 rounded-lg"
                                            wire:key="item-{{ $index }}">
                                            <div class="col-span-5">
                                                <x-input-label value="Material (Stok tersedia)" class="text-xs" />
                                                <x-searchable-select
                                                    wire:model="items.{{ $index }}.material_id"
                                                    :options="$materialOptions"
                                                    placeholder="-- Pilih Material --" />
                                            </div>
                                            <div class="col-span-2">
                                                <x-input-label value="Qty" class="text-xs" />
                                                <x-text-input wire:model="items.{{ $index }}.quantity" type="number"
                                                    step="0.0001" min="0.0001" class="block w-full text-sm" required />
                                                @if($item['available'] > 0)
                                                    <p class="text-xs text-gray-500 mt-1">Max:
                                                        {{ number_format($item['available'], 2) }} {{ $item['unit'] }}
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="col-span-4">
                                                <x-input-label value="Catatan Item" class="text-xs" />
                                                <x-text-input wire:model="items.{{ $index }}.notes" type="text"
                                                    class="block w-full text-sm" />
                                            </div>
                                            <div class="col-span-1 text-center">@if(count($items) > 1)<button type="button"
                                                wire:click="removeItem({{ $index }})"
                                                class="text-red-600 hover:text-red-900"><x-heroicon-o-trash
                                            class="w-5 h-5" /></button>@endif</div>
                                        </div>
                                    @endforeach
                                </div>
                                <x-input-error :messages="$errors->get('items')" class="mt-2" />
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

    {{-- Detail Modal --}}
    @if($showDetailModal && $selectedUsage)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeDetailModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">

                    <div class="bg-white dark:bg-dark-800 px-6 py-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ $selectedUsage->usage_number }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Dicatat pada
                                    {{ $selectedUsage->usage_date->format('d F Y') }}</p>
                            </div>
                            <button type="button" wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-500">
                                <x-heroicon-o-x-circle class="w-6 h-6" />
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="bg-gray-50 dark:bg-dark-700 p-4 rounded-lg">
                                <p class="text-xs uppercase text-gray-500 dark:text-gray-400 font-semibold mb-1">Pekerjaan
                                    Terkait</p>
                                <p class="text-gray-900 dark:text-white font-medium">
                                    {{ $selectedUsage->rabItem->work_name ?? '-' }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-dark-700 p-4 rounded-lg">
                                <p class="text-xs uppercase text-gray-500 dark:text-gray-400 font-semibold mb-1">Dicatat
                                    Oleh</p>
                                <p class="text-gray-900 dark:text-white font-medium">
                                    {{ $selectedUsage->createdBy->name ?? '-' }}</p>
                            </div>
                            @if($selectedUsage->notes)
                                <div class="col-span-1 md:col-span-2 bg-gray-50 dark:bg-dark-700 p-4 rounded-lg">
                                    <p class="text-xs uppercase text-gray-500 dark:text-gray-400 font-semibold mb-1">Catatan</p>
                                    <p class="text-gray-900 dark:text-white">{{ $selectedUsage->notes }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="border rounded-lg overflow-hidden dark:border-dark-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-dark-700">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Material</th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Jumlah</th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Satuan</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Catatan Item</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-dark-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($selectedUsage->items as $item)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">
                                                {{ $item->material->name }} <span
                                                    class="text-gray-500">({{ $item->material->code }})</span></td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">
                                                {{ number_format($item->quantity, 2) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">
                                                {{ $item->material->unit }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $item->notes ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-dark-700 px-6 py-4 flex justify-end">
                        <button type="button" wire:click="closeDetailModal"
                            class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600 transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>