<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Purchase Order', 'url' => route('projects.po.index', $project)],
        ['label' => $po->po_number]
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Purchase Order: {{ $po->po_number }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('projects.po.index', $project) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-dark-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-dark-600">
                    ← Kembali
                </a>
                @if($po->status === 'draft' || $po->status === 'sent')
                    <button type="button" onclick="document.getElementById('deletePOModal').classList.remove('hidden')"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                        Hapus
                    </button>
                @endif
                <a href="{{ route('projects.po.print', [$project, $po]) }}" target="_blank"
                    class="inline-flex items-center px-4 py-2 bg-gold-500 border border-transparent rounded-md font-semibold text-xs text-gray-900 uppercase tracking-widest hover:bg-gold-600">
                    <x-heroicon-o-printer class="w-4 h-4 mr-1" />
                    Print / PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- PO Header Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Supplier</p>
                            <p class="font-bold text-lg">{{ $po->supplier->name }}</p>
                            <p class="text-sm">{{ $po->supplier->address }}</p>
                            <p class="text-sm">{{ $po->supplier->phone }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Order</p>
                            <p class="font-medium">{{ $po->order_date->format('d F Y') }}</p>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Diharapkan Tiba</p>
                            <p class="font-medium">{{ $po->expected_delivery->format('d F Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ strtoupper($po->status) }}
                            </span>

                            @if($po->purchaseRequest)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Ref PR</p>
                                <p class="font-medium text-blue-600">{{ $po->purchaseRequest->pr_number }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Items Table -->
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Daftar Item</h3>
                    <div class="overflow-x-auto border rounded-lg dark:border-dark-700 mb-6">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-dark-700">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Material</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Qty</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Harga Satuan</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($po->items as $item)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $item->material->name }}
                                            </div>
                                            @if($item->notes)
                                                <div class="text-xs text-gray-500">{{ $item->notes }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">
                                            {{ number_format($item->quantity, 2) }} {{ $item->material->unit }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">
                                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-white">
                                            Rp {{ number_format($item->total_price, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-dark-700">
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-right text-sm font-medium text-gray-500">
                                        Subtotal</td>
                                    <td class="px-4 py-2 text-right text-sm font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($po->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @if($po->tax_amount > 0)
                                    <tr>
                                        <td colspan="3" class="px-4 py-2 text-right text-sm font-medium text-gray-500">Pajak
                                        </td>
                                        <td class="px-4 py-2 text-right text-sm font-bold text-gray-900 dark:text-white">
                                            + Rp {{ number_format($po->tax_amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif
                                @if($po->discount_amount > 0)
                                    <tr>
                                        <td colspan="3" class="px-4 py-2 text-right text-sm font-medium text-gray-500">
                                            Diskon</td>
                                        <td class="px-4 py-2 text-right text-sm font-bold text-green-600">
                                            - Rp {{ number_format($po->discount_amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="3"
                                        class="px-4 py-3 text-right text-base font-bold text-gray-900 dark:text-white">
                                        Total Akhir</td>
                                    <td
                                        class="px-4 py-3 text-right text-base font-bold text-blue-600 dark:text-blue-400">
                                        {{ $po->formatted_total_amount }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($po->notes)
                        <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded border border-yellow-200">
                            <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Catatan Order</h4>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">{{ $po->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delete PO Modal -->
    <x-confirm-modal id="deletePOModal" title="Hapus Purchase Order" message="Apakah Anda yakin ingin menghapus PO ini?"
        confirmColor="red" icon="trash">
        <form action="{{ route('projects.po.destroy', [$project, $po]) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">Ya,
                Hapus</button>
        </form>
    </x-confirm-modal>
</x-app-layout>