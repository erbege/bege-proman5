<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Penerimaan Barang', 'url' => route('projects.gr.index', $project)],
        ['label' => 'Input Penerimaan']
    ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Input Penerimaan Barang - {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-4" x-data="grForm()">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Header Info -->
            <div class="bg-white dark:bg-dark-800 shadow sm:rounded-lg mb-6">
                <div class="p-4">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Referensi PO</span>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $po->po_number }}</h3>
                        </div>
                        <div class="text-right">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">Supplier</span>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $po->supplier->name }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('projects.gr.store', $project) }}" method="POST">
                @csrf
                <input type="hidden" name="purchase_order_id" value="{{ $po->id }}">

                <div class="bg-white dark:bg-dark-800 shadow sm:rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <x-input-label for="receipt_date" value="Tanggal Penerimaan" />
                            <x-text-input id="receipt_date" name="receipt_date" type="date" class="mt-1 block w-full"
                                :value="old('receipt_date', date('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('receipt_date')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="delivery_note_number" value="No. Surat Jalan (Delivery Note)" />
                            <x-text-input id="delivery_note_number" name="delivery_note_number" type="text"
                                class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('delivery_note_number')" class="mt-2" />
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="notes" value="Catatan Penerimaan" />
                            <textarea id="notes" name="notes"
                                class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 rounded-md shadow-sm"
                                rows="2"></textarea>
                        </div>
                    </div>

                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Item yang Diterima</h3>

                    <div class="overflow-x-auto border rounded-lg dark:border-dark-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-dark-700">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Material
                                    </th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Dipesan
                                    </th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Sudah
                                        Diterima</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Sisa
                                    </th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-40">
                                        Terima Sekarang</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-48">
                                        Catatan Item</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td class="px-4 py-2">
                                            <span x-text="item.material_name"></span> <span
                                                class="text-xs text-gray-500" x-text="'(' + item.unit + ')'"></span>
                                            <input type="hidden" :name="'items['+index+'][purchase_order_item_id]'"
                                                :value="item.purchase_order_item_id">
                                        </td>
                                        <td class="px-4 py-2 text-right" x-text="item.ordered_qty"></td>
                                        <td class="px-4 py-2 text-right" x-text="item.received_previously"></td>
                                        <td class="px-4 py-2 text-right font-medium text-blue-600"
                                            x-text="item.remaining_qty"></td>
                                        <td class="px-4 py-2">
                                            <input type="number" :name="'items['+index+'][received_qty]'" step="0.01"
                                                min="0" :max="item.remaining_qty" x-model="item.received_qty"
                                                class="block w-full text-sm border-gray-300 dark:border-dark-600 dark:bg-dark-700 dark:text-gray-300 rounded shadow-sm py-1 text-right"
                                                required>
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="text" :name="'items['+index+'][notes]'" x-model="item.notes"
                                                class="block w-full text-sm border-gray-300 dark:border-dark-600 dark:bg-dark-700 dark:text-gray-300 rounded shadow-sm py-1"
                                                placeholder="Optional">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('projects.gr.index', $project) }}"
                        class="text-gray-600 dark:text-gray-400 hover:text-gray-900 underline">Batal</a>
                    <x-primary-button>Simpan Penerimaan</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function grForm() {
            return {
                items: @json($items),
            }
        }
    </script>
</x-app-layout>


