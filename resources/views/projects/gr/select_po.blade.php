<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Pilih Purchase Order - {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Pilih PO untuk diterima barangnya:</h3>

                    @if($activePos->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($activePos as $po)
                                <div class="border rounded-lg p-4 hover:shadow-lg transition dark:border-dark-700">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-bold text-lg">{{ $po->po_number }}</h4>
                                            <p class="text-sm text-gray-500">{{ $po->supplier->name }}</p>
                                        </div>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $po->status_label }}
                                        </span>
                                    </div>
                                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        <p>Total: {{ $po->formatted_total_amount }}</p>
                                        <p>Tgl Kirim: {{ $po->expected_delivery->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="mt-4">
                                        <a href="{{ route('projects.gr.create', ['project' => $project, 'po_id' => $po->id]) }}"
                                            class="block w-full text-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                            Proses Penerimaan
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 bg-gray-50 dark:bg-dark-700 rounded-lg">
                            <p class="text-gray-500 dark:text-gray-400">Tidak ada Purchase Order aktif (Sent/Partial) yang
                                perlu diterima.</p>
                            <a href="{{ route('projects.po.index', $project) }}"
                                class="mt-4 inline-block text-blue-600 hover:underline">Lihat semua PO</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
