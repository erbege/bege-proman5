<div>
    @include('projects.navigation')

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @if (session()->has('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Header --}}
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Penerimaan Barang -
                        {{ $project->name }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->code }}</p>
                </div>
                @if($activePOs->count() > 0)
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-xs font-semibold rounded-md uppercase hover:bg-green-700">
                            <x-heroicon-o-plus class="w-4 h-4 mr-2" />Terima Barang
                        </button>
                        <div x-show="open" @click.away="open = false"
                            class="absolute right-0 mt-2 w-72 bg-white dark:bg-dark-800 rounded-md shadow-lg z-50">
                            @foreach($activePOs as $po)
                                <button wire:click="openModal({{ $po->id }})"
                                    class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700">
                                    {{ $po->po_number }} - {{ $po->supplier->name ?? 'N/A' }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @else
                    <span class="text-sm text-gray-500 dark:text-gray-400">Tidak ada PO aktif untuk diterima</span>
                @endif
            </div>

            {{-- Filter --}}
            <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg mb-4 p-4">
                <div class="relative max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-dark-700 rounded-md bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:ring-gold-500 focus:border-gold-500 sm:text-sm"
                        placeholder="Cari nomor GR atau surat jalan...">
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
                                    No. GR</th>
                                <th
                                    class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    No. PO</th>
                                <th
                                    class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Supplier</th>
                                <th
                                    class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Tanggal</th>
                                <th
                                    class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    No. Surat Jalan</th>
                                <th
                                    class="px-3 py-1.5 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Item</th>
                                <th
                                    class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Diterima Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($receipts as $gr)
                                <tr class="hover:bg-gray-50 dark:hover:bg-dark-700">
                                    <td
                                        class="px-3 py-1.5 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $gr->gr_number }}
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $gr->purchaseOrder->po_number ?? '-' }}
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $gr->purchaseOrder->supplier->name ?? '-' }}
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $gr->receipt_date->format('d M Y') }}
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $gr->delivery_note_number }}
                                    </td>
                                    <td
                                        class="px-3 py-1.5 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                        {{ $gr->items->count() }}
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $gr->receivedBy->name ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada
                                        penerimaan barang.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $receipts->links() }}</div>
            </div>
        </div>
    </div>

    {{-- Receive Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <form wire:submit="save">
                        <div
                            class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-4 max-h-[80vh] overflow-y-auto scrollbar-overlay">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Terima Barang</h3>
                                <button type="button" wire:click="closeModal"
                                    class="text-gray-400 hover:text-gray-500"><x-heroicon-o-x-circle
                                        class="w-6 h-6" /></button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div>
                                    <x-input-label for="receiptDate" value="Tanggal Terima" />
                                    <x-text-input wire:model="receiptDate" id="receiptDate" type="date"
                                        class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <x-input-label for="deliveryNoteNumber" value="No. Surat Jalan" />
                                    <x-text-input wire:model="deliveryNoteNumber" id="deliveryNoteNumber" type="text"
                                        class="mt-1 block w-full" required />
                                    <x-input-error :messages="$errors->get('deliveryNoteNumber')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="notes" value="Catatan" />
                                    <x-text-input wire:model="notes" id="notes" type="text" class="mt-1 block w-full" />
                                </div>
                            </div>

                            {{-- Items --}}
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Item yang Diterima</h4>
                                <div class="space-y-3">
                                    @foreach($items as $index => $item)
                                        <div class="grid grid-cols-12 gap-2 items-center bg-gray-50 dark:bg-dark-700 p-3 rounded-lg"
                                            wire:key="item-{{ $index }}">
                                            <div class="col-span-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $item['material_name'] }}
                                                </div>
                                                <div class="text-xs text-gray-500">Order: {{ $item['ordered_qty'] }}
                                                    {{ $item['unit'] }} | Sisa: {{ $item['remaining_qty'] }}
                                                </div>
                                            </div>
                                            <div class="col-span-3">
                                                <x-input-label value="Qty Diterima" class="text-xs" />
                                                <x-text-input wire:model="items.{{ $index }}.received_qty" type="number"
                                                    step="0.01" min="0.01" max="{{ $item['remaining_qty'] }}"
                                                    class="block w-full text-sm" required />
                                            </div>
                                            <div class="col-span-5">
                                                <x-input-label value="Catatan" class="text-xs" />
                                                <x-text-input wire:model="items.{{ $index }}.notes" type="text"
                                                    class="block w-full text-sm" placeholder="Kondisi barang..." />
                                            </div>
                                        </div>
                                    @endforeach
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
</div>


