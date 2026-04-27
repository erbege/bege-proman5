<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Proyek', 'url' => route('projects.index')],
            ['label' => $project->name, 'url' => route('projects.show', $project)],
            ['label' => 'Purchase Request', 'url' => route('projects.pr.index', $project)],
            ['label' => 'Buat PR Baru']
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Buat Purchase Request - {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('projects.pr.store', $project) }}" method="POST" x-data="prForm()">
                        @csrf
                        
                        @if($mr)
                            <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 text-blue-700 dark:text-blue-300">
                                <p class="font-medium">Membuat PR dari Material Request: {{ $mr->code }}</p>
                                <input type="hidden" name="from_mr_id" value="{{ $mr->id }}">
                            </div>
                        @else
                            <input type="hidden" name="from_mr_id" value="">
                        @endif

                        <!-- Header Form -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <x-input-label for="required_date" value="Tanggal Dibutuhkan (Required Date)" />
                                <x-text-input id="required_date" name="required_date" type="date" class="mt-1 block w-full" :value="old('required_date', date('Y-m-d', strtotime('+7 days')))" required />
                                <x-input-error :messages="$errors->get('required_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="priority" value="Prioritas" />
                                <select id="priority" name="priority" class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">
                                    <option value="low">Low (Rendah)</option>
                                    <option value="normal" selected>Normal</option>
                                    <option value="high">High (Tinggi)</option>
                                    <option value="urgent">Urgent (Mendesak)</option>
                                </select>
                                <x-input-error :messages="$errors->get('priority')" class="mt-2" />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="notes" value="Catatan" />
                                <textarea id="notes" name="notes" class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm" rows="2">{{ old('notes') }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Item PR</h3>
                                <div class="flex gap-2">
                                    <button type="button" @click="showMrModal = true" class="inline-flex items-center px-3 py-1 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none transition ease-in-out duration-150">
                                        <x-heroicon-o-document-arrow-down class="w-4 h-4 mr-1" /> Tambah dari MR
                                    </button>
                                    <button type="button" @click="addItem()" class="inline-flex items-center px-3 py-1 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none transition ease-in-out duration-150">
                                        + Tambah Item Manual
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-x-auto border rounded-lg dark:border-dark-700">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-dark-700">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Material</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-32">Qty</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-32">Est. Harga @</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                                            <th class="px-4 py-2 w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-dark-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr>
                                                <td class="px-4 py-2">
                                                    <div x-show="!item.material_request_item_id">
                                                        <x-searchable-select
                                                            x-bind:name="'items['+index+'][material_id]'"
                                                            x-model="item.material_id"
                                                            :options="$materials"
                                                            options-label="name"
                                                            options-value="id"
                                                            placeholder="-- Pilih Material --"
                                                        />
                                                    </div>
                                                    <div x-show="item.material_request_item_id" class="flex flex-col">
                                                        <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="item.material_name"></span>
                                                        <input type="hidden" :name="'items['+index+'][material_id]'" x-model="item.material_id">
                                                        <input type="hidden" :name="'items['+index+'][material_request_item_id]'" x-model="item.material_request_item_id">
                                                        <div class="mt-1">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                                <x-heroicon-s-link class="w-3 h-3 mr-1" /> <span x-text="item.mr_code"></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input type="number" :name="'items['+index+'][quantity]'" step="0.01" x-model="item.quantity" class="block w-full text-sm border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 rounded-md shadow-sm" required>
                                                    <template x-if="item.remaining_to_order">
                                                        <span class="text-[10px] text-gray-500">Max: <span x-text="item.remaining_to_order"></span></span>
                                                    </template>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input type="number" :name="'items['+index+'][estimated_price]'" step="1" x-model="item.estimated_price" class="block w-full text-sm border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 rounded-md shadow-sm">
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                                    <span x-text="formatCurrency(item.quantity * item.estimated_price)"></span>
                                                </td>
                                                <td class="px-4 py-2 text-center">
                                                    <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900">
                                                        <x-heroicon-o-trash class="w-5 h-5" />
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('projects.pr.index', $project) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 underline">Batal</a>
                            <x-primary-button>Buat Purchase Request</x-primary-button>
                        </div>

                        <!-- Modal Picker MR -->
                        <div x-show="showMrModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                <div x-show="showMrModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" @click="showMrModal = false">
                                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                                </div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                <div x-show="showMrModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                                    <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-4 sm:pb-4">
                                        <div class="flex justify-between items-center mb-4">
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Pilih Item dari Material Request</h3>
                                            <button type="button" @click="showMrModal = false" class="text-gray-400 hover:text-gray-500">
                                                <x-heroicon-o-x-mark class="w-6 h-6" />
                                            </button>
                                        </div>
                                        <div class="max-h-96 overflow-y-auto">
                                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                <thead class="bg-gray-50 dark:bg-dark-700">
                                                    <tr>
                                                        <th class="px-4 py-2 w-10"></th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">MR Code</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Material</th>
                                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Sisa</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Unit</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                                    @foreach($availableMrItems as $mrItem)
                                                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-700">
                                                            <td class="px-4 py-2">
                                                                <input type="checkbox" @change="toggleMrItem({{ json_encode([
                                                                    'material_id' => $mrItem->material_id,
                                                                    'material_request_item_id' => $mrItem->id,
                                                                    'material_name' => $mrItem->material->name,
                                                                    'mr_code' => $mrItem->materialRequest->code,
                                                                    'quantity' => $mrItem->remaining_to_order,
                                                                    'remaining_to_order' => $mrItem->remaining_to_order,
                                                                    'unit' => $mrItem->unit,
                                                                    'estimated_price' => 0,
                                                                    'notes' => ''
                                                                ]) }})" class="rounded border-gray-300 text-gold-600 focus:ring-gold-500">
                                                            </td>
                                                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">{{ $mrItem->materialRequest->code }}</td>
                                                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">{{ $mrItem->material->name }}</td>
                                                            <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-white">{{ number_format($mrItem->remaining_to_order, 2) }}</td>
                                                            <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $mrItem->unit }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-dark-700 px-3 py-1.5 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button type="button" @click="addSelectedMrItems()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                            Tambah Terpilih
                                        </button>
                                        <button type="button" @click="showMrModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600">
                                            Batal
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function prForm() {
            return {
                items: @json($items),
                showMrModal: false,
                selectedMrItems: [],
                init() {
                    if (this.items.length === 0) {
                        this.addItem();
                    }
                },
                addItem() {
                    this.items.push({ 
                        material_id: '', 
                        material_request_item_id: null, 
                        material_name: '', 
                        mr_code: '', 
                        quantity: '', 
                        remaining_to_order: null,
                        estimated_price: 0, 
                        notes: '' 
                    });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                toggleMrItem(item) {
                    const index = this.selectedMrItems.findIndex(i => i.material_request_item_id === item.material_request_item_id);
                    if (index > -1) {
                        this.selectedMrItems.splice(index, 1);
                    } else {
                        this.selectedMrItems.push(item);
                    }
                },
                addSelectedMrItems() {
                    // Remove first empty manual item if it exists
                    if (this.items.length === 1 && !this.items[0].material_id && !this.items[0].material_request_item_id) {
                        this.items = [];
                    }
                    
                    this.selectedMrItems.forEach(item => {
                        // Avoid duplicates
                        if (!this.items.some(i => i.material_request_item_id === item.material_request_item_id)) {
                            this.items.push({...item});
                        }
                    });
                    
                    this.showMrModal = false;
                    this.selectedMrItems = [];
                },
                formatCurrency(value) {
                    if (!value) return 'Rp 0';
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
                }
            }
        }
    </script>
</x-app-layout>


