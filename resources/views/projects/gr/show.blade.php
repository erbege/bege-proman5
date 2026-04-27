<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Penerimaan Barang', 'url' => route('projects.gr.index', $project)],
        ['label' => $gr->gr_number]
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Detail Penerimaan: {{ $gr->gr_number }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('projects.gr.index', $project) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Header Info -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">No. Surat Jalan</p>
                            <p class="font-bold text-lg">{{ $gr->delivery_note_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Penerimaan</p>
                            <p class="font-medium">{{ $gr->receipt_date->format('d F Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Diterima Oleh</p>
                            <p class="font-medium">{{ $gr->receivedBy?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Referensi PO</p>
                            <a href="{{ route('projects.po.show', [$project, $gr->purchaseOrder]) }}"
                                class="font-medium text-blue-600 hover:underline">
                                {{ $gr->purchaseOrder->po_number }}
                            </a>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Supplier</p>
                            <p class="font-medium">{{ $gr->purchaseOrder?->supplier?->name ?? '-' }}</p>
                        </div>
                    </div>
                    @if($gr->notes)
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-dark-700">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Catatan</p>
                            <p>{{ $gr->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Item Diterima</h3>
                    <div class="overflow-x-auto border rounded-lg dark:border-dark-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-dark-700">
                                <tr>
                                    <th
                                        class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Material</th>
                                    <th
                                        class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Qty Diterima</th>
                                    <th
                                        class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($gr->items as $item)
                                    <tr>
                                        <td class="px-3 py-1.5 text-sm text-gray-900 dark:text-white font-medium">
                                            {{ $item->material?->name ?? 'Material Dihapus' }} <span
                                                class="text-gray-500">({{ $item->material?->unit ?? '-' }})</span>
                                        </td>
                                        <td class="px-3 py-1.5 text-right text-sm font-bold text-green-600">
                                            {{ number_format($item->quantity, 2) }}
                                        </td>
                                        <td class="px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $item->notes ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


