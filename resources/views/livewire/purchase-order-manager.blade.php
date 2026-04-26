<div>
    @include('projects.navigation')

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
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Purchase Orders -
                        {{ $project->name }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->code }}</p>
                </div>
                <button wire:click="openModal"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-md uppercase hover:bg-blue-700">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />Buat PO Baru
                </button>
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg mb-6 p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                            </div>
                            <input wire:model.live.debounce.300ms="search" type="text"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-dark-700 rounded-md bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:ring-gold-500 focus:border-gold-500 sm:text-sm"
                                placeholder="Cari nomor PO...">
                        </div>
                    </div>
                    <select wire:model.live="statusFilter"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-dark-700 rounded-md bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 focus:ring-gold-500 focus:border-gold-500 sm:text-sm">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="partial">Partial</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
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
                                    No. PO</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Supplier</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Tanggal</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Total</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($orders as $po)
                                <tr class="hover:bg-gray-50 dark:hover:bg-dark-700">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $po->po_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $po->supplier->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $po->order_date->format('d M Y') }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">
                                        Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @php $statusColors = ['draft' => 'bg-gray-100 text-gray-800', 'sent' => 'bg-blue-100 text-blue-800', 'partial' => 'bg-yellow-100 text-yellow-800', 'completed' => 'bg-green-100 text-green-800', 'cancelled' => 'bg-red-100 text-red-800']; @endphp
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$po->status] ?? 'bg-gray-100 text-gray-800' }}">{{ ucfirst($po->status) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                        <a href="{{ route('projects.po.show', [$project, $po]) }}" title="Detail"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400"><x-heroicon-o-eye
                                                class="w-5 h-5" /></a>
                                        @if(in_array($po->status, ['sent', 'partial']))
                                            <a href="{{ route('projects.gr.create', ['project' => $project, 'po_id' => $po->id]) }}"
                                                title="Terima Barang"
                                                class="text-green-600 hover:text-green-900 dark:text-green-400"><x-heroicon-o-archive-box-arrow-down
                                                    class="w-5 h-5" /></a>
                                        @endif
                                        @if(in_array($po->status, ['draft', 'sent']))
                                            <button wire:click="deletePo({{ $po->id }})" wire:confirm="Yakin hapus PO ini?"
                                                title="Hapus"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400"><x-heroicon-o-trash
                                                    class="w-5 h-5" /></button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada
                                        Purchase Order.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $orders->links() }}</div>
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
                    class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
                    <form wire:submit="save">
                        <div
                            class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6 max-h-[80vh] overflow-y-auto scrollbar-overlay">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Buat Purchase Order</h3>
                                class="text-gray-400 hover:text-gray-500"><x-heroicon-o-x-circle class="w-6 h-6" /></button>
                            </div>

                            {{-- Load from PR --}}
                            @if($approvedPrs->count() > 0)
                                <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <p class="text-sm text-blue-800 dark:text-blue-300 mb-2">Load items dari PR yang sudah
                                        diapprove:</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($approvedPrs as $pr)
                                            <button type="button" wire:click="loadFromPr({{ $pr->id }})"
                                                class="px-3 py-1 bg-blue-100 text-blue-800 text-xs rounded-full hover:bg-blue-200">{{ $pr->pr_number }}
                                                ({{ $pr->items->count() }} items)</button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div>
                                    <x-input-label for="supplierId" value="Supplier" />
                                    <select wire:model="supplierId" id="supplierId"
                                        class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 rounded-md shadow-sm"
                                        required>
                                        <option value="">-- Pilih Supplier --</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('supplierId')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="orderDate" value="Tanggal Order" />
                                    <x-text-input wire:model="orderDate" id="orderDate" type="date"
                                        class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <x-input-label for="expectedDelivery" value="Estimasi Pengiriman" />
                                    <x-text-input wire:model="expectedDelivery" id="expectedDelivery" type="date"
                                        class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <x-input-label for="paymentTerms" value="Syarat Pembayaran" />
                                    <x-text-input wire:model="paymentTerms" id="paymentTerms" type="text"
                                        class="mt-1 block w-full" placeholder="e.g. Net 30" />
                                </div>
                                <div>
                                    <x-input-label for="taxAmount" value="Pajak (Rp)" />
                                    <x-text-input wire:model="taxAmount" id="taxAmount" type="number" step="1" min="0"
                                        class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label for="discountAmount" value="Diskon (Rp)" />
                                    <x-text-input wire:model="discountAmount" id="discountAmount" type="number" step="1"
                                        min="0" class="mt-1 block w-full" />
                                </div>
                                <div class="md:col-span-3">
                                    <x-input-label for="notes" value="Catatan" />
                                    <x-text-input wire:model="notes" id="notes" type="text" class="mt-1 block w-full" />
                                </div>
                            </div>

                            {{-- Items --}}
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-md font-medium text-gray-900 dark:text-white">Item Material</h4>
                                    <button type="button" wire:click="addItem"
                                        class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700"><x-heroicon-o-plus
                                            class="w-4 h-4 mr-1" />Tambah</button>
                                </div>
                                <div class="space-y-3">
                                    @foreach($items as $index => $item)
                                        <div class="grid grid-cols-12 gap-2 items-end bg-gray-50 dark:bg-dark-700 p-3 rounded-lg"
                                            wire:key="item-{{ $index }}">
                                            <div class="col-span-4">
                                                <x-input-label value="Material" class="text-xs" />
                                                <select wire:model="items.{{ $index }}.material_id"
                                                    class="block w-full text-sm border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 rounded-md shadow-sm">
                                                    <option value="">-- Pilih --</option>
                                                    @foreach($materials as $material)
                                                        <option value="{{ $material->id }}">{{ $material->code }} -
                                                            {{ $material->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-span-2">
                                                <x-input-label value="Qty" class="text-xs" />
                                                <x-text-input wire:model="items.{{ $index }}.quantity" type="number" step="0.01"
                                                    min="0.01" class="block w-full text-sm" required />
                                            </div>
                                            <div class="col-span-2">
                                                <x-input-label value="Harga Satuan" class="text-xs" />
                                                <x-text-input wire:model="items.{{ $index }}.unit_price" type="number" step="1"
                                                    min="0" class="block w-full text-sm" required />
                                            </div>
                                            <div class="col-span-3">
                                                <x-input-label value="Catatan" class="text-xs" />
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
</div>