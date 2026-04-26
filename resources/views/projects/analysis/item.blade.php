<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Proyek', 'url' => route('projects.index')],
            ['label' => $project->name, 'url' => route('projects.show', $project)],
            ['label' => 'Analisis Material', 'url' => route('projects.analysis.index', $project)],
            ['label' => $item->work_name]
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Detail Analisis - {{ $item->work_name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->code }} - {{ $project->name }}</p>
            </div>
            <a href="{{ route('projects.analysis.index', $project) }}"
                class="text-gray-600 hover:text-gray-800 dark:text-gray-400">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Item Details -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Info Pekerjaan</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nama Pekerjaan</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $item->work_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Volume</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ number_format($item->volume, 2) }}
                            {{ $item->unit }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Harga Satuan</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ $item->formatted_unit_price ?? 'Rp ' . number_format($item->unit_price, 0, ',', '.') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Harga</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $item->formatted_total_price }}</p>
                    </div>
                </div>
            </div>

            <!-- Material Forecasts -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Material yang Dibutuhkan</h3>
                    <button type="button" onclick="document.getElementById('reanalyzeModal').classList.remove('hidden')"
                        class="text-orange-600 hover:text-orange-800 dark:text-orange-400 text-sm">
                        Re-analisis
                    </button>
                </div>

                @if($item->materialForecasts->count() > 0)
                    <div class="overflow-x-auto">
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
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Satuan</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Koefisien</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Match</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($item->materialForecasts as $forecast)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                            {{ $forecast->raw_material_name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right font-medium">
                                            {{ number_format($forecast->estimated_qty, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-center">
                                            {{ $forecast->unit }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-right">
                                            {{ number_format($forecast->coefficient, 4) }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if($forecast->material)
                                                <span
                                                    class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                    {{ $forecast->material->name }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $forecast->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        Belum ada data material. Silakan jalankan analisis.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Re-analyze Modal --}}
    <x-confirm-modal id="reanalyzeModal" title="Re-analisis Item"
        message="Apakah Anda yakin ingin re-analisis item ini? Hasil analisis sebelumnya akan dihapus." 
        confirmColor="yellow" icon="arrow-path">
        <form action="{{ route('projects.analysis.reanalyze', [$project, $item]) }}" method="POST">
            @csrf
            <button type="submit"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 sm:ml-3 sm:w-auto sm:text-sm">
                Ya, Re-analisis
            </button>
        </form>
    </x-confirm-modal>
</x-app-layout>

