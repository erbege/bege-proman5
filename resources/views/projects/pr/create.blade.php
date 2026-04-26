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

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
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
                                <button type="button" @click="addItem()" class="inline-flex items-center px-3 py-1 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none transition ease-in-out duration-150">
                                    + Tambah Item
                                </button>
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
                                                    <x-searchable-select
                                                        x-bind:name="'items['+index+'][material_id]'"
                                                        x-model="item.material_id"
                                                        :options="$materials"
                                                        options-label="name"
                                                        options-value="id"
                                                        placeholder="-- Pilih Material --"
                                                    />
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input type="number" :name="'items['+index+'][quantity]'" step="0.01" x-model="item.quantity" class="block w-full text-sm border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 rounded-md shadow-sm" required>
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
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function prForm() {
            return {
                items: @json($items),
                init() {
                    if (this.items.length === 0) {
                        this.addItem();
                    }
                },
                addItem() {
                    this.items.push({ material_id: '', quantity: '', estimated_price: 0, notes: '' });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                formatCurrency(value) {
                    if (!value) return 'Rp 0';
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
                }
            }
        }
    </script>
</x-app-layout>
