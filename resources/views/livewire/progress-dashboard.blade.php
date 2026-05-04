<div class="space-y-6" wire:ignore.self>
    <!-- Header Section -->
    <div class="bg-white dark:bg-dark-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-dark-700 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-black text-gray-900 dark:text-white flex items-center tracking-tight">
                <span class="p-2 bg-gradient-to-br from-gold-400 to-gold-600 rounded-xl mr-3 text-white shadow-lg shadow-gold-500/30">
                    <x-heroicon-s-chart-bar class="w-6 h-6" />
                </span>
                Dashboard Progres
            </h2>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1 ml-14">
                Analisis & Performa Proyek: <span class="text-gray-900 dark:text-white font-bold">{{ $project->name }}</span>
            </p>
        </div>
        <div class="flex items-center space-x-3 w-full md:w-auto">
            <button wire:click="exportExcel" class="flex-1 md:flex-none inline-flex items-center justify-center px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 rounded-xl text-xs font-bold uppercase tracking-widest transition-all group border border-emerald-200 dark:border-emerald-800/50">
                <x-heroicon-o-table-cells class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" />
                Excel
            </button>
            <button wire:click="exportPdf" class="flex-1 md:flex-none inline-flex items-center justify-center px-4 py-2.5 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40 text-red-700 dark:text-red-400 rounded-xl text-xs font-bold uppercase tracking-widest transition-all group border border-red-200 dark:border-red-800/50">
                <x-heroicon-o-document-arrow-down class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" />
                PDF
            </button>
        </div>
    </div>

    <!-- KPIs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Progress Variance Card -->
        <div class="bg-gradient-to-br from-white to-gray-50 dark:from-dark-800 dark:to-dark-900 rounded-2xl p-6 border border-gray-100 dark:border-dark-700 shadow-sm relative overflow-hidden group hover:shadow-lg transition-all duration-300">
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-blue-500/10 blur-2xl group-hover:bg-blue-500/20 transition-all duration-500"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Progress Variance</p>
                    <h3 class="text-3xl font-black {{ $variance >= 0 ? 'text-emerald-500' : 'text-red-500' }} tracking-tighter">
                        {{ $variance > 0 ? '+' : '' }}{{ number_format($variance, 1) }}%
                    </h3>
                </div>
                <div class="p-3 {{ $variance >= 0 ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' }} rounded-2xl shadow-inner">
                    @if($variance >= 0)
                        <x-heroicon-s-arrow-trending-up class="w-6 h-6" />
                    @else
                        <x-heroicon-s-arrow-trending-down class="w-6 h-6" />
                    @endif
                </div>
            </div>
            <div class="mt-4 relative z-10">
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                    @if($variance >= 0)
                        <span class="text-emerald-500 font-bold">✓ Ahead of schedule</span> compared to baseline plan.
                    @else
                        <span class="text-red-500 font-bold">⚠️ Delayed</span> compared to baseline plan.
                    @endif
                </p>
            </div>
        </div>

        <!-- Productivity Index Card -->
        <div class="bg-gradient-to-br from-white to-gray-50 dark:from-dark-800 dark:to-dark-900 rounded-2xl p-6 border border-gray-100 dark:border-dark-700 shadow-sm relative overflow-hidden group hover:shadow-lg transition-all duration-300">
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-purple-500/10 blur-2xl group-hover:bg-purple-500/20 transition-all duration-500"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Productivity Index</p>
                    <div class="flex items-baseline space-x-2">
                        <h3 class="text-3xl font-black text-purple-600 dark:text-purple-400 tracking-tighter">
                            {{ number_format($productivityIndex, 3) }}
                        </h3>
                        <span class="text-xs font-bold text-gray-400 uppercase">%/worker</span>
                    </div>
                </div>
                <div class="p-3 bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400 rounded-2xl shadow-inner">
                    <x-heroicon-s-bolt class="w-6 h-6" />
                </div>
            </div>
            <div class="mt-4 relative z-10">
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                    Average progress generated per worker this week.
                </p>
            </div>
        </div>

        <!-- Safety Score Card -->
        <div class="bg-gradient-to-br from-white to-gray-50 dark:from-dark-800 dark:to-dark-900 rounded-2xl p-6 border border-gray-100 dark:border-dark-700 shadow-sm relative overflow-hidden group hover:shadow-lg transition-all duration-300">
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-sky-500/10 blur-2xl group-hover:bg-sky-500/20 transition-all duration-500"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Safety Score</p>
                    <h3 class="text-3xl font-black {{ $safetyScore == 100 ? 'text-sky-500' : 'text-amber-500' }} tracking-tighter">
                        {{ number_format($safetyScore, 1) }}%
                    </h3>
                </div>
                <div class="p-3 {{ $safetyScore == 100 ? 'bg-sky-100 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400' : 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400' }} rounded-2xl shadow-inner">
                    <x-heroicon-s-shield-check class="w-6 h-6" />
                </div>
            </div>
            <div class="mt-4 relative z-10">
                <div class="w-full bg-gray-100 dark:bg-dark-700 rounded-full h-1.5 mb-2">
                    <div class="h-1.5 rounded-full {{ $safetyScore == 100 ? 'bg-sky-500' : 'bg-amber-500' }}" style="width: {{ $safetyScore }}%"></div>
                </div>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider">
                    Reports without incidents
                </p>
            </div>
        </div>
    </div>

    <!-- S-Curve Analytics Chart -->
    <div class="bg-white dark:bg-dark-800 rounded-2xl shadow-sm border border-gray-100 dark:border-dark-700 overflow-hidden">
        <!-- Chart Header -->
        <div class="px-6 py-4 border-b border-gray-100 dark:border-dark-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl text-white shadow-lg shadow-blue-500/20">
                    <x-heroicon-s-presentation-chart-line class="w-5 h-5" />
                </div>
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">S-Curve Analytics</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kurva Rencana vs Realisasi</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button wire:click="refreshScurve" wire:loading.attr="disabled" wire:target="refreshScurve"
                    class="inline-flex items-center px-3 py-2 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 text-blue-700 dark:text-blue-400 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all border border-blue-200 dark:border-blue-800/50 disabled:opacity-50">
                    <x-heroicon-o-arrow-path class="w-3.5 h-3.5 mr-1.5" wire:loading.class="animate-spin" wire:target="refreshScurve" />
                    <span wire:loading.remove wire:target="refreshScurve">Refresh</span>
                    <span wire:loading wire:target="refreshScurve">Memuat...</span>
                </button>
            </div>
        </div>

        @if($scurveHasData)
            <!-- S-Curve Summary Mini Cards -->
            <div class="px-6 py-3 bg-gray-50/50 dark:bg-dark-900/30 border-b border-gray-100 dark:border-dark-700 grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="text-center p-2">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Minggu</p>
                    <p class="text-lg font-black text-gray-900 dark:text-white">{{ $scurveSummary['currentWeek'] }}<span class="text-xs font-bold text-gray-400">/{{ $scurveSummary['totalWeeks'] }}</span></p>
                </div>
                <div class="text-center p-2">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Deviasi</p>
                    <p class="text-lg font-black {{ $scurveSummary['variance'] >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                        {{ $scurveSummary['variance'] > 0 ? '+' : '' }}{{ number_format($scurveSummary['variance'], 1) }}%
                    </p>
                </div>
                <div class="text-center p-2">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">SPI</p>
                    <p class="text-lg font-black {{ $scurveSummary['spiIndex'] >= 1 ? 'text-emerald-500' : ($scurveSummary['spiIndex'] >= 0.9 ? 'text-amber-500' : 'text-red-500') }}">
                        {{ number_format($scurveSummary['spiIndex'], 2) }}
                    </p>
                </div>
                <div class="text-center p-2">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Est. Selesai</p>
                    <p class="text-lg font-black text-gray-900 dark:text-white">
                        @if($scurveSummary['estimatedCompletionWeek'])
                            M{{ $scurveSummary['estimatedCompletionWeek'] }}
                            @if($scurveSummary['estimatedCompletionWeek'] > $scurveSummary['totalWeeks'])
                                <span class="text-[9px] text-red-500 font-bold">+{{ $scurveSummary['estimatedCompletionWeek'] - $scurveSummary['totalWeeks'] }}</span>
                            @endif
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Chart Canvas -->
            <div class="p-6" wire:ignore>
                <div class="relative h-[400px]" x-data="scurveChart(@js($scurveData))" x-init="initChart()">
                    <canvas x-ref="scurveCanvas"></canvas>
                </div>
            </div>

            <!-- Legend -->
            <div class="px-6 pb-4 flex flex-wrap justify-center gap-4 text-xs">
                <span class="inline-flex items-center"><span class="w-5 h-0.5 bg-blue-500 rounded mr-2 border-dashed border-t-2 border-blue-500"></span><span class="font-bold text-gray-500 dark:text-gray-400">Rencana (Planned)</span></span>
                <span class="inline-flex items-center"><span class="w-5 h-1 bg-emerald-500 rounded mr-2"></span><span class="font-bold text-gray-500 dark:text-gray-400">Realisasi (Actual)</span></span>
                <span class="inline-flex items-center"><span class="w-5 h-0.5 border-t-2 border-dashed border-amber-500 mr-2"></span><span class="font-bold text-gray-500 dark:text-gray-400">Proyeksi (Projected)</span></span>
                <span class="inline-flex items-center"><span class="w-3 h-3 bg-indigo-200 dark:bg-indigo-800 rounded mr-2"></span><span class="font-bold text-gray-500 dark:text-gray-400">Deviasi</span></span>
            </div>
        @else
            <!-- Empty State -->
            <div class="p-12 flex flex-col items-center justify-center text-center min-h-[300px]">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 dark:bg-dark-900 mb-4 border border-gray-100 dark:border-dark-700 shadow-inner">
                    <x-heroicon-o-presentation-chart-line class="w-8 h-8 text-gray-300 dark:text-gray-600" />
                </div>
                <h3 class="text-lg font-black text-gray-900 dark:text-white">Belum Ada Data S-Curve</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">
                    Pastikan proyek memiliki RAB dengan jadwal (planned start/end) dan laporan progress untuk menampilkan kurva S.
                </p>
                <button wire:click="refreshScurve"
                    class="mt-4 inline-flex items-center px-4 py-2 bg-gold-500 text-white rounded-lg text-xs font-bold uppercase tracking-widest hover:bg-gold-600 transition shadow-lg shadow-gold-500/30">
                    <x-heroicon-o-arrow-path class="w-4 h-4 mr-2" />
                    Generate S-Curve
                </button>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('scurveChart', (chartData) => ({
        chart: null,
        data: chartData,

        initChart() {
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js not loaded yet');
                return;
            }
            this.renderChart();
        },

        renderChart() {
            const ctx = this.$refs.scurveCanvas.getContext('2d');
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
            const textColor = isDark ? '#9ca3af' : '#6b7280';

            // Current week vertical line plugin
            const currentWeekPlugin = {
                id: 'currentWeekLine',
                afterDraw: (chart) => {
                    const cwi = this.data.currentWeekIndex;
                    if (cwi === null || cwi === undefined) return;
                    const meta = chart.getDatasetMeta(0);
                    if (!meta.data[cwi]) return;
                    const x = meta.data[cwi].x;
                    const yAxis = chart.scales.y;
                    const ctx2 = chart.ctx;
                    ctx2.save();
                    ctx2.beginPath();
                    ctx2.setLineDash([4, 4]);
                    ctx2.strokeStyle = isDark ? 'rgba(251,191,36,0.5)' : 'rgba(217,119,6,0.4)';
                    ctx2.lineWidth = 2;
                    ctx2.moveTo(x, yAxis.top);
                    ctx2.lineTo(x, yAxis.bottom);
                    ctx2.stroke();
                    ctx2.restore();
                    // Label
                    ctx2.save();
                    ctx2.fillStyle = isDark ? '#fbbf24' : '#d97706';
                    ctx2.font = 'bold 9px Inter, sans-serif';
                    ctx2.textAlign = 'center';
                    ctx2.fillText('HARI INI', x, yAxis.top - 6);
                    ctx2.restore();
                }
            };

            this.chart = new Chart(ctx, {
                type: 'line',
                plugins: [currentWeekPlugin],
                data: {
                    labels: this.data.labels,
                    datasets: [
                        {
                            label: 'Rencana',
                            data: this.data.planned,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.08)',
                            borderWidth: 2.5,
                            borderDash: [6, 3],
                            fill: true,
                            tension: 0.4,
                            pointRadius: 2,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'rgb(59, 130, 246)',
                            order: 2
                        },
                        {
                            label: 'Realisasi',
                            data: this.data.actual,
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.12)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 3,
                            pointHoverRadius: 7,
                            pointBackgroundColor: 'rgb(16, 185, 129)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 1.5,
                            order: 1
                        },
                        {
                            label: 'Proyeksi',
                            data: this.data.projected,
                            borderColor: 'rgb(245, 158, 11)',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            borderDash: [8, 4],
                            fill: false,
                            tension: 0.3,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointBackgroundColor: 'rgb(245, 158, 11)',
                            spanGaps: true,
                            order: 3
                        },
                        {
                            label: 'Deviasi',
                            data: this.data.deviation,
                            type: 'bar',
                            backgroundColor: this.data.deviation.map(v =>
                                v >= 0 ? (isDark ? 'rgba(16,185,129,0.25)' : 'rgba(16,185,129,0.15)')
                                       : (isDark ? 'rgba(239,68,68,0.25)' : 'rgba(239,68,68,0.15)')
                            ),
                            borderColor: this.data.deviation.map(v =>
                                v >= 0 ? 'rgba(16,185,129,0.4)' : 'rgba(239,68,68,0.4)'
                            ),
                            borderWidth: 1,
                            borderRadius: 3,
                            yAxisID: 'yDeviation',
                            order: 4,
                            barPercentage: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: isDark ? '#1f2937' : '#fff',
                            titleColor: isDark ? '#f3f4f6' : '#111827',
                            bodyColor: isDark ? '#d1d5db' : '#4b5563',
                            borderColor: isDark ? '#374151' : '#e5e7eb',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 10,
                            titleFont: { weight: 'bold', size: 12 },
                            bodyFont: { size: 11 },
                            usePointStyle: true,
                            callbacks: {
                                label: function(ctx) {
                                    if (ctx.raw === null || ctx.raw === undefined) return null;
                                    const suffix = ctx.dataset.label === 'Deviasi' ? '' : '%';
                                    const prefix = (ctx.dataset.label === 'Deviasi' && ctx.raw > 0) ? '+' : '';
                                    return ' ' + ctx.dataset.label + ': ' + prefix + ctx.raw.toFixed(2) + suffix;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: gridColor, drawBorder: false },
                            ticks: {
                                color: textColor,
                                font: { size: 9, weight: 'bold' },
                                maxRotation: 45,
                                autoSkip: true,
                                maxTicksLimit: 20,
                                callback: function(val, i) {
                                    // Show short label
                                    const full = this.getLabelForValue(val);
                                    return full.split(' ')[0]; // Just "M1", "M2" etc
                                }
                            }
                        },
                        y: {
                            position: 'left',
                            beginAtZero: true,
                            max: 100,
                            grid: { color: gridColor, drawBorder: false },
                            ticks: {
                                color: textColor,
                                font: { size: 10, weight: 'bold' },
                                callback: v => v + '%',
                                stepSize: 20
                            },
                            title: {
                                display: true,
                                text: 'Kumulatif (%)',
                                color: textColor,
                                font: { size: 10, weight: 'bold' }
                            }
                        },
                        yDeviation: {
                            position: 'right',
                            grid: { display: false },
                            ticks: {
                                color: textColor,
                                font: { size: 9 },
                                callback: v => (v > 0 ? '+' : '') + v.toFixed(1)
                            },
                            title: {
                                display: true,
                                text: 'Deviasi',
                                color: textColor,
                                font: { size: 10, weight: 'bold' }
                            }
                        }
                    }
                }
            });
        },

        destroy() {
            if (this.chart) this.chart.destroy();
        }
    }));
});
</script>
