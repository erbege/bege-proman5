<div>
    {{-- Loading Overlay - Positioned at root level for proper z-index --}}
    <div wire:loading.delay wire:target="regenerateSchedule"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[9999] flex items-center justify-center">
        <div class="bg-white dark:bg-dark-800 rounded-xl p-4 shadow-2xl text-center max-w-sm mx-4">
            <div class="animate-spin rounded-full h-16 w-16 border-4 border-gold-500 border-t-transparent mx-auto mb-4">
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Regenerating Jadwal</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Menghitung ulang distribusi bobot dan jadwal mingguan...
            </p>
        </div>
    </div>

    @include('projects.navigation')

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @if (session()->has('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Time Schedule -
                        {{ $project->name }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->code }}</p>
                </div>
                <button wire:click="regenerateSchedule" wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-xs font-semibold rounded-md uppercase hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <x-heroicon-o-arrow-path class="w-4 h-4 mr-2" wire:loading.class="animate-spin"
                        wire:target="regenerateSchedule" />
                    <span wire:loading.remove wire:target="regenerateSchedule">Generate Ulang</span>
                    <span wire:loading wire:target="regenerateSchedule">Memproses...</span>
                </button>
            </div>

            {{-- View Mode Tabs --}}
            <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg mb-4">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex space-x-8 px-4">
                        <button wire:click="setViewMode('table')"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $viewMode === 'table' ? 'border-gold-500 text-gold-600 dark:text-gold-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <x-heroicon-o-table-cells class="w-5 h-5 inline mr-2" />Tabel Jadwal
                        </button>
                        <button wire:click="setViewMode('gantt')"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $viewMode === 'gantt' ? 'border-gold-500 text-gold-600 dark:text-gold-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <x-heroicon-o-chart-bar class="w-5 h-5 inline mr-2" />Gantt Chart
                        </button>
                        <button wire:click="setViewMode('scurve')"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $viewMode === 'scurve' ? 'border-gold-500 text-gold-600 dark:text-gold-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <x-heroicon-o-presentation-chart-line class="w-5 h-5 inline mr-2" />S-Curve
                        </button>
                    </nav>
                </div>
            </div>

            {{-- Table View --}}
            @if($viewMode === 'table')
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-dark-700">
                                <tr>
                                    <th
                                        class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Minggu</th>
                                    <th
                                        class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Periode</th>
                                    <th
                                        class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Rencana (%)</th>
                                    <th
                                        class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Kumulatif Rencana</th>
                                    <th
                                        class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Realisasi (%)</th>
                                    <th
                                        class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Kumulatif Realisasi</th>
                                    <th
                                        class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Deviasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($schedules as $schedule)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-dark-700">
                                        <td
                                            class="px-3 py-1.5 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            Minggu {{ $schedule->week_number }}</td>
                                        <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $schedule->start_date?->format('d/m') ?? '-' }} -
                                            {{ $schedule->end_date?->format('d/m/Y') ?? '-' }}
                                        </td>
                                        <td
                                            class="px-3 py-1.5 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                            {{ number_format($schedule->planned_progress, 2) }}%
                                        </td>
                                        <td class="px-3 py-1.5 whitespace-nowrap text-sm text-right font-medium text-blue-600">
                                            {{ number_format($schedule->cumulative_planned, 2) }}%
                                        </td>
                                        <td
                                            class="px-3 py-1.5 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                            {{ number_format($schedule->actual_progress ?? 0, 2) }}%
                                        </td>
                                        <td class="px-3 py-1.5 whitespace-nowrap text-sm text-right font-medium text-green-600">
                                            {{ number_format($schedule->cumulative_actual ?? 0, 2) }}%
                                        </td>
                                        <td
                                            class="px-3 py-1.5 whitespace-nowrap text-sm text-right font-medium {{ ($schedule->cumulative_actual ?? 0) >= $schedule->cumulative_planned ? 'text-green-600' : 'text-red-600' }}">
                                            {{ number_format(($schedule->cumulative_actual ?? 0) - $schedule->cumulative_planned, 2) }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada
                                            jadwal. Klik "Generate Ulang" untuk membuat jadwal.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Gantt View --}}
            @if($viewMode === 'gantt')
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Gantt Chart</h3>
                    @if(count($ganttItems) > 0)
                        <div class="overflow-x-auto">
                            <div class="min-w-full">
                                @foreach($ganttItems as $item)
                                    <div class="flex items-center mb-2 border-b border-gray-100 dark:border-gray-700 pb-2">
                                        <div class="w-64 flex-shrink-0 pr-4">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                {{ $item['name'] }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item['section'] }}</p>
                                        </div>
                                        <div class="flex-1 relative h-8 bg-gray-100 dark:bg-dark-700 rounded">
                                            <div class="absolute h-full bg-blue-500 rounded opacity-60"
                                                style="left: 0; width: 100%;"></div>
                                            <div class="absolute h-full bg-green-500 rounded"
                                                style="left: 0; width: {{ $item['progress'] }}%;"></div>
                                            <span
                                                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-xs font-medium text-gray-700 dark:text-gray-300">{{ $item['progress'] }}%</span>
                                        </div>
                                        <div class="w-32 flex-shrink-0 pl-4 text-right">
                                            <p class="text-xs text-gray-500">{{ $item['start'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $item['end'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-center text-gray-500 py-8">Tidak ada item jadwal. Pastikan RAB item memiliki tanggal
                            rencana.</p>
                    @endif
                </div>
            @endif

            {{-- S-Curve View --}}
            @if($viewMode === 'scurve')
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">S-Curve Progress</h3>
                    @if(!empty($scurveData['labels']))
                        <div class="h-96" x-data="{
                                    init() {
                                        const ctx = this.$refs.scurveCanvas.getContext('2d');
                                        new Chart(ctx, {
                                            type: 'line',
                                            data: {
                                                labels: @js($scurveData['labels']),
                                                datasets: [
                                                    {
                                                        label: 'Rencana',
                                                        data: @js($scurveData['planned']),
                                                        borderColor: 'rgb(59, 130, 246)',
                                                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                                        fill: true,
                                                        tension: 0.3
                                                    },
                                                    {
                                                        label: 'Realisasi',
                                                        data: @js($scurveData['actual']),
                                                        borderColor: 'rgb(34, 197, 94)',
                                                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                                        fill: true,
                                                        tension: 0.3
                                                    }
                                                ]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                scales: {
                                                    y: {
                                                        beginAtZero: true,
                                                        max: 100,
                                                        title: { display: true, text: 'Progress (%)' }
                                                    }
                                                }
                                            }
                                        });
                                    }
                                }">
                            <canvas x-ref="scurveCanvas"></canvas>
                        </div>
                    @else
                        <p class="text-center text-gray-500 py-8">Tidak ada data S-Curve. Pastikan jadwal sudah di-generate.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush


