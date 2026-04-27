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
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Purchase Requests -
                        {{ $project->name }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->code }}</p>
                </div>
                <div class="flex gap-2">
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-xs font-semibold rounded-md uppercase hover:bg-green-700">
                            <x-heroicon-o-document-arrow-down class="w-4 h-4 mr-2" />Dari MR
                        </button>
                        <div x-show="open" @click.away="open = false"
                            class="absolute right-0 mt-2 w-64 bg-white dark:bg-dark-800 rounded-md shadow-lg z-50 py-1">
                            @forelse($approvedMrs as $mr)
                                <button wire:click="openModal({{ $mr->id }})"
                                    class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700">
                                    {{ $mr->code }} ({{ $mr->items->count() }} items)
                                </button>
                            @empty
                                <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 italic">
                                    Tidak ada MR yang disetujui (Approved) untuk proyek ini.
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <button wire:click="openModal"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-md uppercase hover:bg-blue-700">
                        <x-heroicon-o-plus class="w-4 h-4 mr-2" />Buat PR Baru
                    </button>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg mb-4 p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                            </div>
                            <input wire:model.live.debounce.300ms="search" type="text"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-dark-700 rounded-md bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:ring-gold-500 focus:border-gold-500 sm:text-sm"
                                placeholder="Cari nomor PR...">
                        </div>
                    </div>
                    <select wire:model.live="statusFilter"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-dark-700 rounded-md bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 focus:ring-gold-500 focus:border-gold-500 sm:text-sm">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="completed">Completed</option>
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
                                    class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    No. PR</th>
                                <th
                                    class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Tanggal</th>
                                <th
                                    class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Required</th>
                                <th
                                    class="px-3 py-1.5 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Item</th>
                                <th
                                    class="px-3 py-1.5 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Priority</th>
                                <th
                                    class="px-3 py-1.5 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Status</th>
                                <th
                                    class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($prs as $pr)
                                <tr class="hover:bg-gray-50 dark:hover:bg-dark-700">
                                    <td
                                        class="px-3 py-1.5 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $pr->pr_number }}
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $pr->request_date->format('d M Y') }}
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $pr->required_date->format('d M Y') }}
                                    </td>
                                    <td
                                        class="px-3 py-1.5 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                        {{ $pr->items->count() }}
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-center">
                                        @php $priorityColors = ['low' => 'bg-gray-100 text-gray-800', 'normal' => 'bg-blue-100 text-blue-800', 'high' => 'bg-orange-100 text-orange-800', 'urgent' => 'bg-red-100 text-red-800']; @endphp
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full {{ $priorityColors[$pr->priority] ?? 'bg-gray-100 text-gray-800' }}">{{ ucfirst($pr->priority) }}</span>
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-center">
                                        @php $statusColors = ['pending' => 'bg-yellow-100 text-yellow-800', 'approved' => 'bg-green-100 text-green-800', 'rejected' => 'bg-red-100 text-red-800', 'completed' => 'bg-blue-100 text-blue-800']; @endphp
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$pr->status] ?? 'bg-gray-100 text-gray-800' }}">{{ ucfirst($pr->status) }}</span>
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-right text-sm space-x-2">
                                        @if($pr->status === 'pending')
                                            <button wire:click="openApprovalModal({{ $pr->id }}, 'approved')"
                                                class="text-green-600 hover:text-green-900 dark:text-green-400">Approve</button>
                                            <button wire:click="openApprovalModal({{ $pr->id }}, 'rejected')"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400">Reject</button>
                                        @elseif($pr->status === 'approved')
                                            <a href="{{ route('projects.po.create', ['project' => $project]) }}"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400">Buat PO</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada
                                        Purchase Request.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $prs->links() }}</div>
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
                        <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Buat Purchase Request</h3>
                                <button type="button" wire:click="closeModal"
                                    class="text-gray-400 hover:text-gray-500"><x-heroicon-o-x-circle
                                        class="w-6 h-6" /></button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div>
                                    <x-input-label for="requiredDate" value="Tanggal Dibutuhkan" />
                                    <x-text-input wire:model="requiredDate" id="requiredDate" type="date"
                                        class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <x-input-label for="priority" value="Prioritas" />
                                    <select wire:model="priority" id="priority"
                                        class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 rounded-md shadow-sm">
                                        <option value="low">Low</option>
                                        <option value="normal">Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                                <div>
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
                                            <div class="col-span-5">
                                                <x-input-label value="Material" class="text-xs" />
                                                <div wire:ignore x-data="{
                                                                                tomSelect: null,
                                                                                value: @entangle('items.{{ $index }}.material_id'),
                                                                                init() {
                                                                                    let checkInterval = setInterval(() => {
                                                                                        if (window.TomSelect) {
                                                                                            clearInterval(checkInterval);
                                                                                            this.initTomSelect();
                                                                                        }
                                                                                    }, 100);
                                                                                },
                                                                                initTomSelect() {
                                                                                    if(this.tomSelect) return;
                                                                                    this.tomSelect = new TomSelect(this.$refs.select, {
                                                                                        create: false,
                                                                                        sortField: {field: 'text', direction: 'asc'},
                                                                                        valueField: 'value',
                                                                                        labelField: 'text',
                                                                                        searchField: 'text',
                                                                                        plugins: ['remove_button'],
                                                                                        onInitialize: function() {
                                                                                            this.control.classList.add('dark:bg-dark-900', 'dark:text-gray-300', 'dark:border-dark-700');
                                                                                            this.dropdown.classList.add('dark:bg-dark-900', 'dark:text-gray-300', 'dark:border-dark-700');
                                                                                        },
                                                                                        render: {
                                                                                            option: function(data, escape) {
                                                                                                return '<div class=\'dark:text-gray-300\'>' + escape(data.text) + '</div>';
                                                                                            },
                                                                                            item: function(data, escape) {
                                                                                                return '<div class=\'dark:text-gray-300\'>' + escape(data.text) + '</div>';
                                                                                            }
                                                                                        }
                                                                                    });

                                                                                    if(this.value) this.tomSelect.setValue(this.value, true);

                                                                                    this.$watch('value', value => {
                                                                                        if(value !== this.tomSelect.getValue()){
                                                                                            this.tomSelect.setValue(value, true);
                                                                                        }
                                                                                    });

                                                                                    this.tomSelect.on('change', value => {
                                                                                        this.value = value;
                                                                                    });
                                                                                }
                                                                            }" x-init="init()" class="w-full">
                                                    <select x-ref="select" class="hidden" autocomplete="off"
                                                        style="display: none!important">
                                                        <option value="">-- Pilih Material --</option>
                                                        @foreach($materials as $material)
                                                            <option value="{{ $material->id }}">{{ $material->code }} -
                                                                {{ $material->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @if(isset($item['material_request_item_id']))
                                                    <div class="mt-1">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                            <x-heroicon-s-link class="w-3 h-3 mr-1" /> Source MR Item
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-span-2">
                                                <x-input-label value="Qty" class="text-xs" />
                                                <x-text-input wire:model="items.{{ $index }}.quantity" type="number" step="0.01"
                                                    min="0.01" class="block w-full text-sm" required />
                                            </div>
                                            <div class="col-span-2">
                                                <x-input-label value="Est. Harga" class="text-xs" />
                                                <x-text-input wire:model="items.{{ $index }}.estimated_price" type="number"
                                                    step="1" min="0" class="block w-full text-sm" />
                                            </div>
                                            <div class="col-span-2">
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

    {{-- Approval Modal --}}
    @if($showApprovalModal)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeApprovalModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div
                    class="relative inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" wire:click="closeApprovalModal" class="text-gray-400 hover:text-gray-500">
                            <x-heroicon-o-x-circle class="w-6 h-6" />
                        </button>
                    </div>
                    <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full {{ $approvalAction === 'approved' ? 'bg-green-100' : 'bg-red-100' }} sm:mx-0 sm:h-10 sm:w-10">
                                @if($approvalAction === 'approved')<x-heroicon-o-check-circle
                                class="h-6 w-6 text-green-600" />@else<x-heroicon-o-x-circle
                                    class="h-6 w-6 text-red-600" />@endif
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $approvalAction === 'approved' ? 'Approve' : 'Reject' }} Purchase Request
                                </h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Yakin ingin
                                    {{ $approvalAction === 'approved' ? 'menyetujui' : 'menolak' }} PR ini?
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-dark-700 px-3 py-1.5 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="processApproval"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 {{ $approvalAction === 'approved' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }} text-base font-medium text-white sm:ml-3 sm:w-auto sm:text-sm">{{ $approvalAction === 'approved' ? 'Approve' : 'Reject' }}</button>
                        <button wire:click="closeApprovalModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600">Batal</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>


