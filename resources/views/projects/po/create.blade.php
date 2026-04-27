<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Purchase Order', 'url' => route('projects.po.index', $project)],
        ['label' => 'Buat PO Baru']
    ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Buat Purchase Order - {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-4" x-data="poForm()">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('projects.po.store', $project) }}" method="POST">
                @csrf

                <!-- Hidden inputs for selected PRs -->
                <template x-for="id in selectedPrIds">
                    <input type="hidden" name="pr_ids[]" :value="id">
                </template>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <!-- Left Column: Form -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Header Box -->
                        <div class="bg-white dark:bg-dark-800 shadow sm:rounded-lg p-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informasi Order</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="supplier_id" value="Supplier" />
                                    <select id="supplier_id" name="supplier_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm"
                                        required>
                                        <option value="">-- Pilih Supplier --</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="order_date" value="Tanggal Order" />
                                    <x-text-input id="order_date" name="order_date" type="date"
                                        class="mt-1 block w-full" :value="old('order_date', date('Y-m-d'))" required />
                                    <x-input-error :messages="$errors->get('order_date')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="expected_delivery" value="Tgl Pengiriman (Exp)" />
                                    <x-text-input id="expected_delivery" name="expected_delivery" type="date"
                                        class="mt-1 block w-full" :value="old('expected_delivery', date('Y-m-d', strtotime('+3 days')))" required />
                                    <x-input-error :messages="$errors->get('expected_delivery')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="payment_terms" value="Syarat Pembayaran" />
                                    <x-text-input id="payment_terms" name="payment_terms" type="text"
                                        class="mt-1 block w-full" placeholder="Contoh: Net 30, COD"
                                        :value="old('payment_terms')" />
                                </div>
                            </div>
                        </div>

                        <!-- Items Box -->
                        <div class="bg-white dark:bg-dark-800 shadow sm:rounded-lg p-4">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Item Order</h3>
                                <div class="space-x-2">
                                    <button type="button" @click="showPrModal = true"
                                        class="inline-flex items-center px-3 py-1 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                        Import dari PR
                                    </button>
                                    <button type="button" @click="addItem()"
                                        class="inline-flex items-center px-3 py-1 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white">
                                        + Manual Item
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-x-auto border rounded-lg dark:border-dark-700">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-dark-700">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Material</th>
                                            <th
                                                class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-20">
                                                Qty</th>
                                            <th
                                                class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase w-32">
                                                Harga Satuan</th>
                                            <th
                                                class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                                Total</th>
                                            <th class="px-3 py-2 w-8"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr>
                                                <td class="px-3 py-2">
                                                    <!-- Uses data-materials logic ideally, but simplified here -->
                                                    <select :name="'items['+index+'][material_id]'"
                                                        x-model="item.material_id"
                                                        class="block w-full text-sm border-gray-300 dark:border-dark-600 dark:bg-dark-700 dark:text-gray-300 rounded shadow-sm py-1"
                                                        required>
                                                        <option value="">Sales/Item</option>
                                                        <!-- Populate options via JS or server loop. -->
                                                        <!-- Simplest: Render full options here. -->
                                                        @foreach(\App\Models\Material::orderBy('name')->get() as $m)
                                                            <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->unit }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="text-xs text-gray-500 mt-1" x-text="item.notes"></div>
                                                    <input type="hidden" :name="'items['+index+'][notes]'"
                                                        x-model="item.notes">
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="number" :name="'items['+index+'][quantity]'"
                                                        step="0.01" x-model="item.quantity"
                                                        class="block w-full text-sm border-gray-300 dark:border-dark-600 dark:bg-dark-700 dark:text-gray-300 rounded shadow-sm py-1 text-right"
                                                        required>
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="number" :name="'items['+index+'][unit_price]'" step="1"
                                                        x-model="item.unit_price"
                                                        class="block w-full text-sm border-gray-300 dark:border-dark-600 dark:bg-dark-700 dark:text-gray-300 rounded shadow-sm py-1 text-right"
                                                        required>
                                                </td>
                                                <td class="px-3 py-2 text-right text-sm">
                                                    <span
                                                        x-text="formatCurrency(item.quantity * item.unit_price)"></span>
                                                </td>
                                                <td class="px-3 py-2 text-center">
                                                    <button type="button" @click="removeItem(index)"
                                                        class="text-red-600 hover:text-red-900">x</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Summary -->
                    <div class="space-y-6">
                        <div class="bg-white dark:bg-dark-800 shadow sm:rounded-lg p-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Ringkasan</h3>

                            <div class="flex justify-between mb-2">
                                <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                                <span class="font-medium dark:text-white" x-text="formatCurrency(subtotal)"></span>
                            </div>

                            <div class="mb-2">
                                <x-input-label for="tax_percent" value="Pajak / PPN (%)" />
                                <div class="flex items-center gap-2">
                                    <input type="number" id="tax_percent" name="tax_percent" x-model="taxPercent"
                                        step="0.1" min="0" max="100"
                                        class="block w-24 text-sm border-gray-300 dark:border-dark-600 dark:bg-dark-700 dark:text-gray-300 rounded shadow-sm text-right">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">%</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-300 ml-auto"
                                        x-text="formatCurrency(taxAmount)"></span>
                                </div>
                                <input type="hidden" name="tax_amount" :value="taxAmount">
                            </div>

                            <div class="mb-4">
                                <x-input-label for="discount_amount" value="Diskon" />
                                <input type="number" id="discount_amount" name="discount_amount" x-model="discount"
                                    class="block w-full text-sm border-gray-300 dark:border-dark-600 dark:bg-dark-700 dark:text-gray-300 rounded shadow-sm text-right">
                            </div>

                            <div class="border-t dark:border-dark-600 pt-4 flex justify-between items-center">
                                <span class="text-lg font-bold text-gray-900 dark:text-white">Total</span>
                                <span class="text-lg font-bold text-gold-600 dark:text-gold-400"
                                    x-text="formatCurrency(total)"></span>
                            </div>

                            <div class="mt-6">
                                <x-input-label for="notes" value="Catatan Tambahan" />
                                <textarea id="notes" name="notes"
                                    class="mt-1 block w-full border-gray-300 dark:border-dark-600 dark:bg-dark-700 dark:text-gray-300 rounded-md shadow-sm"
                                    rows="3"></textarea>
                            </div>

                            <div class="mt-6">
                                <x-primary-button class="w-full justify-center">Simpan PO</x-primary-button>
                            </div>
                        </div>

                        <!-- Selected PR Info -->
                        <div class="bg-white dark:bg-dark-800 shadow sm:rounded-lg p-4"
                            x-show="selectedPrInfo.length > 0">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">PR Terpilih:</h4>
                            <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300">
                                <template x-for="code in selectedPrInfo">
                                    <li x-text="code"></li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- PR Selection Modal -->
        <div x-show="showPrModal" class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay" style="display: none;">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showPrModal = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <div
                    class="relative inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" @click="showPrModal = false" class="text-gray-400 hover:text-gray-500">
                            <x-heroicon-o-x-circle class="w-6 h-6" />
                        </button>
                    </div>
                    <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-4 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white"
                                    id="modal-title">
                                    Pilih Purchase Request
                                </h3>
                                <div class="mt-4 max-h-96 overflow-y-auto scrollbar-overlay">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr>
                                                <th class="px-2 py-1"></th>
                                                <th class="px-2 py-1 text-left">No. PR</th>
                                                <th class="px-2 py-1 text-left">Tgl</th>
                                                <th class="px-2 py-1 text-left">Prioritas</th>
                                                <th class="px-2 py-1 text-left">Item</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingPrs as $pr)
                                                <tr>
                                                    <td class="px-2 py-2">
                                                        <input type="checkbox" value="{{ $pr->id }}"
                                                            x-model="tempSelectedPrs"
                                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                                    </td>
                                                    <td class="px-2 py-2 text-sm">{{ $pr->pr_number }}</td>
                                                    <td class="px-2 py-2 text-sm">{{ $pr->required_date->format('d/m/Y') }}
                                                    </td>
                                                    <td class="px-2 py-2 text-sm">{{ $pr->priority }}</td>
                                                    <td class="px-2 py-2 text-sm">
                                                        <ul class="list-disc list-inside text-xs">
                                                            @foreach($pr->items as $item)
                                                                <li>{{ $item->material->name }} ({{ $item->quantity }})</li>
                                                            @endforeach
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-dark-700 px-3 py-1.5 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="importPrs()"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Import Selected
                        </button>
                        <button type="button" @click="showPrModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function poForm() {
            return {
                items: [{ material_id: '', quantity: '', unit_price: 0, notes: '', total: 0 }],
                taxPercent: 11,
                discount: 0,
                showPrModal: false,
                tempSelectedPrs: [],
                selectedPrIds: [],
                selectedPrInfo: [],

                // Data from server
                availablePrs: @json($pendingPrs),

                get subtotal() {
                    return this.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
                },
                get taxAmount() {
                    return this.subtotal * Number(this.taxPercent) / 100;
                },
                get total() {
                    return Number(this.subtotal) + Number(this.taxAmount) - Number(this.discount);
                },

                addItem() {
                    this.items.push({ material_id: '', quantity: '', unit_price: 0, notes: '' });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },

                importPrs() {
                    // Logic to find selected PRs from availablePrs
                    // Merge items

                    let newItems = [];
                    // Clear existing? No, append.

                    this.tempSelectedPrs.forEach(prId => {
                        // Check if already added?
                        if (!this.selectedPrIds.includes(prId)) {
                            this.selectedPrIds.push(prId);

                            let pr = this.availablePrs.find(p => p.id == prId);
                            if (pr) {
                                this.selectedPrInfo.push(pr.pr_number);
                                pr.items.forEach(prItem => {
                                    // Check if we want to merge quantities for same material?
                                    // For simplicity, just add as new line.
                                    this.items.push({
                                        material_id: prItem.material_id,
                                        quantity: prItem.quantity,
                                        unit_price: prItem.estimated_price || 0, // Use estimated from PR
                                        notes: 'From ' + pr.pr_number
                                    });
                                });
                            }
                        }
                    });

                    // Remove empty initial row if it's untouched
                    if (this.items.length > 1 && this.items[0].material_id === '') {
                        this.items.shift();
                    }

                    this.showPrModal = false;
                    this.tempSelectedPrs = []; // Reset check
                },

                formatCurrency(value) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
                }
            }
        }
    </script>
</x-app-layout>


