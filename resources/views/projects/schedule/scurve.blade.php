<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Jadwal', 'url' => route('projects.schedule.index', $project)],
        ['label' => 'Kurva S']
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Kurva S - {{ $project->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->code }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('projects.schedule.export-excel', $project) }}"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export Excel
                </a>
                <a href="{{ route('projects.schedule.export-pdf', $project) }}"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export PDF
                </a>
                <form action="{{ route('projects.schedule.regenerate', $project) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Regenerate
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- S-Curve Chart -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Grafik Kurva S</h3>
                <div id="scurve-chart" style="height: 400px;"></div>
            </div>

            <!-- Schedule Table with Tabs -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <!-- Tab Switcher (Chips) -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Data Mingguan</h3>
                    <div class="flex space-x-2">
                        <button onclick="switchTab('summary')" id="tab-summary"
                            class="px-4 py-2 text-sm font-medium rounded-full transition-colors duration-200 bg-gold-500 text-white">
                            Tabel Ringkasan
                        </button>
                        <button onclick="switchTab('matrix')" id="tab-matrix"
                            class="px-4 py-2 text-sm font-medium rounded-full transition-colors duration-200 bg-gray-200 text-gray-700 dark:bg-dark-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                            Time Schedule
                        </button>
                    </div>
                </div>

                <!-- Tab 1: Summary Table (Original) -->
                <div id="content-summary" class="overflow-x-auto">
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
                                    Realisasi (%)</th>
                                <th
                                    class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Kum. Rencana</th>
                                <th
                                    class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Kum. Realisasi</th>
                                <th
                                    class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Deviasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($schedules as $schedule)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-3 py-1.5 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $schedule->week_label }}</td>
                                    <td class="px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $schedule->week_start->format('d M') }} -
                                        {{ $schedule->week_end->format('d M Y') }}
                                    </td>
                                    <td class="px-3 py-1.5 text-sm text-gray-900 dark:text-white text-right">
                                        {{ number_format($schedule->planned_weight, 2) }}</td>
                                    <td class="px-3 py-1.5 text-sm text-gray-900 dark:text-white text-right">
                                        {{ number_format($schedule->actual_weight, 2) }}</td>
                                    <td class="px-3 py-1.5 text-sm text-gray-900 dark:text-white text-right">
                                        {{ number_format($schedule->planned_cumulative, 2) }}</td>
                                    <td class="px-3 py-1.5 text-sm text-gray-900 dark:text-white text-right">
                                        {{ number_format($schedule->actual_cumulative, 2) }}</td>
                                    <td class="px-3 py-1.5 text-sm text-right font-medium 
                                                @if($schedule->deviation > 0) text-green-600 dark:text-green-400
                                                @elseif($schedule->deviation < 0) text-red-600 dark:text-red-400
                                                @else text-gray-900 dark:text-white @endif">
                                        {{ $schedule->deviation > 0 ? '+' : '' }}{{ number_format($schedule->deviation, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        Belum ada data jadwal. Silakan tambahkan item RAB dengan tanggal rencana terlebih
                                        dahulu.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Tab 2: Time Schedule Matrix (PDF-style) -->
                <div id="content-matrix" class="hidden overflow-x-auto">
                    @php
                        $startDate = $project->start_date;
                        $endDate = $project->end_date;
                        $totalWeeks = max(1, (int) ceil($startDate->diffInDays($endDate) / 7));

                        // Group weeks by month
                        $months = [];
                        for ($w = 0; $w < $totalWeeks; $w++) {
                            $weekDate = $startDate->copy()->addWeeks($w);
                            $monthKey = $weekDate->format('M-Y');
                            if (!isset($months[$monthKey])) {
                                $months[$monthKey] = ['label' => $weekDate->format('M Y'), 'weeks' => []];
                            }
                            $months[$monthKey]['weeks'][] = [
                                'num' => $w + 1,
                                'date' => $weekDate->format('d'),
                                'full' => $weekDate
                            ];
                        }
                    @endphp

                    <table class="min-w-full border-collapse text-xs">
                        <!-- Month Headers -->
                        <thead>
                            <tr class="bg-gold-500 text-white">
                                <th class="border border-indigo-700 px-2 py-2 text-left font-semibold sticky left-0 bg-gold-500 z-20"
                                    rowspan="2" style="min-width: 40px;">NO</th>
                                <th class="border border-indigo-700 px-2 py-2 text-left font-semibold sticky left-10 bg-gold-500 z-20"
                                    rowspan="2" style="min-width: 200px;">URAIAN PEKERJAAN</th>
                                <th class="border border-indigo-700 px-2 py-2 text-center font-semibold" rowspan="2"
                                    style="min-width: 60px;">BOBOT %</th>
                                @foreach($months as $monthData)
                                    <th class="border border-indigo-700 px-1 py-1 text-center font-semibold bg-gold-500"
                                        colspan="{{ count($monthData['weeks']) }}">
                                        {{ $monthData['label'] }}
                                    </th>
                                @endforeach
                            </tr>
                            <tr class="bg-gold-500 text-white">
                                @foreach($months as $monthData)
                                    @foreach($monthData['weeks'] as $week)
                                        <th class="border border-indigo-600 px-1 py-1 text-center" style="min-width: 30px;">
                                            M{{ $week['num'] }}</th>
                                    @endforeach
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rabSections as $section)
                                @include('projects.schedule.partials.recursive-scurve-row', [
                                    'section' => $section,
                                    'startDate' => $startDate,
                                    'totalWeeks' => $totalWeeks
                                ])
                            @endforeach
                        </tbody>
                        <!-- Summary Footer -->
                        <tfoot>
                            <tr class="bg-green-50 dark:bg-green-900/30 font-medium">
                                <td colspan="3"
                                    class="border border-gray-300 dark:border-dark-600 px-2 py-2 text-right text-gray-700 dark:text-gray-300 sticky left-0 bg-green-50 dark:bg-green-900/30 z-10">
                                    Rencana Mingguan (%)</td>
                                @foreach($schedules as $schedule)
                                    <td
                                        class="border border-gray-300 dark:border-dark-600 px-1 py-1 text-center text-gray-900 dark:text-white">
                                        {{ number_format($schedule->planned_weight, 1) }}</td>
                                @endforeach
                            </tr>
                            <tr class="bg-green-100 dark:bg-green-900/50 font-medium">
                                <td colspan="3"
                                    class="border border-gray-300 dark:border-dark-600 px-2 py-2 text-right text-gray-700 dark:text-gray-300 sticky left-0 bg-green-100 dark:bg-green-900/50 z-10">
                                    Rencana Kumulatif (%)</td>
                                @foreach($schedules as $schedule)
                                    <td
                                        class="border border-gray-300 dark:border-dark-600 px-1 py-1 text-center font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($schedule->planned_cumulative, 1) }}</td>
                                @endforeach
                            </tr>
                            <tr class="bg-blue-50 dark:bg-blue-900/30 font-medium">
                                <td colspan="3"
                                    class="border border-gray-300 dark:border-dark-600 px-2 py-2 text-right text-gray-700 dark:text-gray-300 sticky left-0 bg-blue-50 dark:bg-blue-900/30 z-10">
                                    Realisasi Mingguan (%)</td>
                                @foreach($schedules as $schedule)
                                    <td
                                        class="border border-gray-300 dark:border-dark-600 px-1 py-1 text-center text-gray-900 dark:text-white">
                                        {{ number_format($schedule->actual_weight, 1) }}</td>
                                @endforeach
                            </tr>
                            <tr class="bg-blue-100 dark:bg-blue-900/50 font-medium">
                                <td colspan="3"
                                    class="border border-gray-300 dark:border-dark-600 px-2 py-2 text-right text-gray-700 dark:text-gray-300 sticky left-0 bg-blue-100 dark:bg-blue-900/50 z-10">
                                    Realisasi Kumulatif (%)</td>
                                @foreach($schedules as $schedule)
                                    <td
                                        class="border border-gray-300 dark:border-dark-600 px-1 py-1 text-center font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($schedule->actual_cumulative, 1) }}</td>
                                @endforeach
                            </tr>
                            <tr class="bg-yellow-50 dark:bg-yellow-900/30 font-medium">
                                <td colspan="3"
                                    class="border border-gray-300 dark:border-dark-600 px-2 py-2 text-right text-gray-700 dark:text-gray-300 sticky left-0 bg-yellow-50 dark:bg-yellow-900/30 z-10">
                                    Deviasi (%)</td>
                                @foreach($schedules as $schedule)
                                    <td class="border border-gray-300 dark:border-dark-600 px-1 py-1 text-center font-semibold
                                            @if($schedule->deviation > 0) text-green-600 dark:text-green-400
                                            @elseif($schedule->deviation < 0) text-red-600 dark:text-red-400
                                            @else text-gray-700 dark:text-gray-300 @endif">
                                        {{ $schedule->deviation > 0 ? '+' : '' }}{{ number_format($schedule->deviation, 1) }}
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-6">
                <a href="{{ route('projects.show', $project) }}"
                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                    ← Kembali ke Detail Proyek
                </a>
            </div>
        </div>
    </div>

    {{-- Tab Switching Script - defined globally for onclick handlers --}}
    <script>
        function switchTab(tab) {
            const summaryContent = document.getElementById('content-summary');
            const matrixContent = document.getElementById('content-matrix');
            const tabSummary = document.getElementById('tab-summary');
            const tabMatrix = document.getElementById('tab-matrix');
            
            const activeClasses = 'bg-gold-500 text-white';
            const inactiveClasses = 'bg-gray-200 text-gray-700 dark:bg-dark-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600';
            
            if (tab === 'summary') {
                summaryContent.classList.remove('hidden');
                matrixContent.classList.add('hidden');
                tabSummary.className = 'px-4 py-2 text-sm font-medium rounded-full transition-colors duration-200 ' + activeClasses;
                tabMatrix.className = 'px-4 py-2 text-sm font-medium rounded-full transition-colors duration-200 ' + inactiveClasses;
            } else {
                summaryContent.classList.add('hidden');
                matrixContent.classList.remove('hidden');
                tabSummary.className = 'px-4 py-2 text-sm font-medium rounded-full transition-colors duration-200 ' + inactiveClasses;
                tabMatrix.className = 'px-4 py-2 text-sm font-medium rounded-full transition-colors duration-200 ' + activeClasses;
            }
        }
    </script>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var options = {
                    series: [{
                        name: 'Rencana (%)',
                        data: @json($chartData['planned'])
                    }, {
                        name: 'Realisasi (%)',
                        data: @json($chartData['actual'])
                    }],
                    chart: {
                        height: 400,
                        type: 'line',
                        toolbar: {
                            show: true
                        },
                        background: 'transparent'
                    },
                    colors: ['#3B82F6', '#10B981'],
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    xaxis: {
                        categories: @json($chartData['labels']),
                        labels: {
                            style: {
                                colors: '#9CA3AF'
                            }
                        }
                    },
                    yaxis: {
                        min: 0,
                        max: 100,
                        labels: {
                            style: {
                                colors: '#9CA3AF'
                            },
                            formatter: function (val) {
                                return val.toFixed(0) + '%';
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        labels: {
                            colors: '#9CA3AF'
                        }
                    },
                    grid: {
                        borderColor: '#374151'
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: function (val) {
                                return val.toFixed(2) + '%';
                            }
                        }
                    }
                };

                var chart = new ApexCharts(document.querySelector("#scurve-chart"), options);
                chart.render();
            });
        </script>
    @endpush
</x-app-layout>


