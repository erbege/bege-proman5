@extends('portal.owner.layouts.app')

@section('header')
    <div>
        <div
            class="flex items-center space-x-2 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">
            <a href="{{ route('owner.dashboard') }}"
                class="hover:text-primary-700 dark:hover:text-primary-300 transition-colors">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
            </svg>
            <span class="text-slate-500">{{ $project->name }}</span>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white font-outfit tracking-tight">{{ $project->name }}</h2>
    </div>
@endsection

@section('content')
    <!-- S-Curve Chart -->
    <div class="portal-card p-5 mb-6">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-4">
            <div>
                <h3 class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest">S-Curve Progress
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Perbandingan rencana vs realisasi</p>
            </div>
            <div class="flex items-center gap-4 text-xs font-bold">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 bg-primary-500 rounded-full"></span>
                    <span>Aktual: {{ number_format($stats['actual_progress'], 1) }}%</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 bg-slate-300 dark:bg-slate-600 rounded-full"></span>
                    <span>Rencana: {{ number_format($stats['planned_progress'], 1) }}%</span>
                </div>
            </div>
        </div>
        <div class="h-48 w-full">
            <canvas id="scurveChart"></canvas>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Weekly Reports -->
        <div class="lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest">Laporan Mingguan
                </h3>
                <span
                    class="px-2.5 py-1 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 text-[10px] font-bold rounded uppercase tracking-tight">
                    {{ $weeklyReports->count() }} Laporan
                </span>
            </div>

            <div class="space-y-3">
                @forelse($weeklyReports as $report)
                    <div class="portal-card portal-card-hover p-4 hover:border-primary-200 dark:hover:border-primary-800/50 group">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                <div
                                    class="w-9 h-9 bg-primary-50 dark:bg-primary-900/20 rounded flex items-center justify-center text-primary-700 dark:text-primary-300 font-bold text-sm flex-shrink-0 group-hover:bg-primary-600 group-hover:text-white dark:group-hover:bg-primary-600 dark:group-hover:text-white transition-all">
                                    {{ $report->week_number }}
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Minggu
                                        {{ $report->week_number }}</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ $report->period_start->format('d M') }} -
                                        {{ $report->period_end->format('d M') }}
                                    </p>
                                    <p class="text-xs text-slate-600 dark:text-slate-300 font-semibold mt-1">
                                        Progress:
                                        {{ number_format($report->cumulative_data['totals']['actual_cumulative'] ?? 0, 1) }}%
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('owner.weekly-reports.show', $report) }}"
                                class="flex-shrink-0 p-2 text-primary-700 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                    </path>
                                </svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="portal-card p-8 text-center">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-20" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest italic">Belum ada laporan.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Project Info Sidebar -->
        <div class="space-y-4">
            <div class="portal-card p-5">
                <h3
                    class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest mb-4 border-b border-slate-100 dark:border-dark-700 pb-2">
                    Informasi</h3>
                <div class="space-y-4 text-sm">
                    <div>
                        <label
                            class="text-xs uppercase font-bold text-slate-400 dark:text-slate-500 tracking-tight block mb-1">Kode</label>
                        <p class="font-semibold text-slate-700 dark:text-slate-300">{{ $project->code }}</p>
                    </div>
                    <div>
                        <label
                            class="text-xs uppercase font-bold text-slate-400 dark:text-slate-500 tracking-tight block mb-1">Lokasi</label>
                        <p class="font-semibold text-slate-700 dark:text-slate-300">{{ $project->location }}</p>
                    </div>
                    <div>
                        <label
                            class="text-xs uppercase font-bold text-slate-400 dark:text-slate-500 tracking-tight block mb-1">Mulai</label>
                        <p class="font-semibold text-slate-700 dark:text-slate-300">
                            {{ $project->start_date ? $project->start_date->format('d M Y') : 'N/A' }}</p>
                    </div>
                    <div>
                        <label
                            class="text-xs uppercase font-bold text-slate-400 dark:text-slate-500 tracking-widest block mb-1.5">Klien
                            / Owner</label>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ Auth::user()->name }}</p>
                    </div>
                </div>
            </div>

            <!-- Photo Gallery -->
            <div
                class="portal-card p-5 bg-gradient-to-br from-primary-600 to-primary-800 text-white relative overflow-hidden group">
                <div
                    class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-bl-full translate-x-8 -translate-y-8 group-hover:scale-110 transition-transform duration-500">
                </div>
                <div class="flex justify-between items-center mb-4 relative z-10">
                    <h3 class="text-xs font-bold uppercase tracking-widest">Galeri</h3>
                    <a href="{{ route('owner.projects.gallery', $project) }}"
                        class="text-xs font-bold text-primary-100 hover:text-white uppercase tracking-tight transition-colors">Lihat
                        Semua →</a>
                </div>
                <div class="grid grid-cols-2 gap-2 relative z-10">
                    @php $count = 0; @endphp
                    @foreach ($weeklyReports as $report)
                        @if ($report->cover_image_url && $count < 4)
                            <div class="aspect-square rounded-md overflow-hidden border border-white/20 shadow-lg">
                                <img src="{{ $report->cover_image_url }}"
                                    class="w-full h-full object-cover hover:scale-110 transition duration-500 cursor-pointer">
                            </div>
                            @php $count++; @endphp
                        @endif
                    @endforeach
                    @if ($count === 0)
                        <div
                            class="col-span-2 p-4 text-center border border-dashed border-white/20 rounded-md text-[9px] font-bold uppercase tracking-widest opacity-50">
                            Tidak ada foto
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('scurveChart');
            const isDark = document.documentElement.classList.contains('dark');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($project->schedules->pluck('week_number')->map(fn($w) => "W$w")) !!},
                    datasets: [{
                            label: 'Aktual Cumulative (%)',
                            data: {!! json_encode($project->schedules->pluck('actual_cumulative')) !!},
                            borderColor: '#eab308',
                            backgroundColor: 'rgba(234, 179, 8, 0.15)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointBackgroundColor: '#eab308',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Rencana Cumulative (%)',
                            data: {!! json_encode($project->schedules->pluck('planned_cumulative')) !!},
                            borderColor: isDark ? '#475569' : '#cbd5e1',
                            borderDash: [5, 5],
                            borderWidth: 2,
                            fill: false,
                            tension: 0,
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: isDark ? '#1e293b' : '#fff',
                            titleColor: isDark ? '#f8fafc' : '#1e293b',
                            bodyColor: isDark ? '#f8fafc' : '#1e293b',
                            borderColor: isDark ? '#334155' : '#e2e8f0',
                            borderWidth: 1,
                            padding: 10,
                            bodyFont: {
                                family: 'Inter',
                                size: 11
                            },
                            titleFont: {
                                family: 'Outfit',
                                size: 12,
                                weight: 'bold'
                            },
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                color: isDark ? '#64748b' : '#94a3b8',
                                font: {
                                    size: 10
                                },
                                callback: function(value) {
                                    return value + '%';
                                }
                            },
                            grid: {
                                color: isDark ? 'rgba(51, 65, 85, 0.5)' : 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            }
                        },
                        x: {
                            ticks: {
                                color: isDark ? '#64748b' : '#94a3b8',
                                font: {
                                    size: 10
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        </script>
    @endpush
@endsection
