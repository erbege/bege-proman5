<div>
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <!-- Total Budget -->
        <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-lg border-l-4 border-blue-500 rounded-xl transition-all duration-300 hover:shadow-xl">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        <x-heroicon-o-banknotes class="w-6 h-6" />
                    </div>
                    <div class="ml-4">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Anggaran (RAB)</p>
                        <p class="text-xl font-extrabold text-gray-900 dark:text-gray-100">
                            Rp {{ number_format($summary['total_budget'], 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earned Value -->
        <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-lg border-l-4 border-indigo-500 rounded-xl transition-all duration-300 hover:shadow-xl">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                        <x-heroicon-o-chart-bar class="w-6 h-6" />
                    </div>
                    <div class="ml-4">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Nilai Hasil (EV)</p>
                        <p class="text-xl font-extrabold text-gray-900 dark:text-gray-100">
                            Rp {{ number_format($summary['earned_value'], 0, ',', '.') }}
                        </p>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs text-indigo-600 dark:text-indigo-400 font-semibold">
                    <span>Hasil Progress Fisik</span>
                </div>
            </div>
        </div>

        <!-- Actual Cost -->
        <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-lg border-l-4 border-orange-500 rounded-xl transition-all duration-300 hover:shadow-xl">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
                        <x-heroicon-o-shopping-cart class="w-6 h-6" />
                    </div>
                    <div class="ml-4">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Realisasi (AC)</p>
                        <p class="text-xl font-extrabold text-gray-900 dark:text-gray-100">
                            Rp {{ number_format($summary['actual_cost'], 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cost Variance -->
        <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-lg border-l-4 {{ $summary['cost_variance'] >= 0 ? 'border-green-500' : 'border-red-500' }} rounded-xl transition-all duration-300 hover:shadow-xl">
            <div class="p-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-xl {{ $summary['cost_variance'] >= 0 ? 'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400' }}">
                        <x-heroicon-o-scale class="w-6 h-6" />
                    </div>
                    <div class="ml-4">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Efisiensi (CV)</p>
                        <p class="text-xl font-extrabold {{ $summary['cost_variance'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $summary['cost_variance'] >= 0 ? '+' : '' }}Rp {{ number_format($summary['cost_variance'], 0, ',', '.') }}
                        </p>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-xs font-bold {{ $summary['cost_variance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    <span>CPI: {{ $summary['cpi'] }} ({{ $summary['health_status'] }})</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Forecasting Card -->
    <div class="bg-gradient-to-r from-gray-900 to-dark-800 rounded-2xl p-4 mb-4 shadow-xl border border-gray-700">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center">
                <div class="p-4 rounded-2xl bg-gold-500/10 text-gold-500 border border-gold-500/20">
                    <x-heroicon-o-presentation-chart-line class="w-8 h-8" />
                </div>
                <div class="ml-5">
                    <h4 class="text-lg font-bold text-white">Forecasting Proyek</h4>
                    <p class="text-sm text-gray-400">Estimasi biaya akhir berdasarkan performa saat ini (CPI)</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Estimasi Akhir (EAC)</p>
                    <p class="text-lg font-extrabold text-white">Rp {{ number_format($summary['eac'], 0, ',', '.') }}</p>
                    <p class="text-[10px] mt-1 {{ $summary['eac'] <= $summary['total_budget'] ? 'text-green-400' : 'text-red-400' }}">
                        {{ $summary['forecast_status'] }}
                    </p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Sisa Anggaran (ETC)</p>
                    <p class="text-lg font-extrabold text-white">Rp {{ number_format($summary['etc'], 0, ',', '.') }}</p>
                </div>
                <div class="hidden md:block">
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Status Profitabilitas</p>
                    @php 
                        $margin = $project->contract_value - $summary['eac'];
                    @endphp
                    <p class="text-lg font-extrabold {{ $margin >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $margin >= 0 ? '+' : '' }}Rp {{ number_format($margin, 0, ',', '.') }}
                    </p>
                    <p class="text-[10px] text-gray-400 mt-1 italic">Estimasi margin kotor</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Container -->
    <div class="bg-white dark:bg-dark-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-dark-700">
        <div class="p-4 border-b border-gray-100 dark:border-dark-700 bg-gray-50/50 dark:bg-dark-800/50 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h3 class="text-lg font-extrabold text-gray-900 dark:text-gray-100">Evaluasi Pekerjaan RAB</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Analisis perbandingan Anggaran vs Realisasi Penggunaan Material</p>
            </div>
            
            <div class="relative w-full md:w-64">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari pekerjaan..." 
                    class="w-full pl-10 pr-4 py-2 border-gray-200 dark:border-dark-600 dark:bg-dark-700 dark:text-gray-200 rounded-xl text-sm focus:ring-gold-500 focus:border-gold-500">
                <div class="absolute left-3 top-2.5 text-gray-400">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-dark-700">
                <thead class="bg-white dark:bg-dark-800">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Pekerjaan</th>
                        <th class="px-3 py-2 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">Anggaran</th>
                        <th class="px-3 py-2 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">Nilai Hasil</th>
                        <th class="px-3 py-2 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">Biaya Riil</th>
                        <th class="px-3 py-2 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">Variansi</th>
                        <th class="px-3 py-2 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">Visualisasi Budget</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-dark-700">
                    @forelse ($details as $row)
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-indigo-900/10 transition-colors group">
                        <td class="px-3 py-3">
                            <div class="flex flex-col">
                                <span class="text-xs font-mono text-gray-400 leading-none mb-1">{{ $row['code'] }}</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-gray-100 group-hover:text-blue-600 transition-colors">{{ $row['work_name'] }}</span>
                                <span class="text-[10px] mt-1 font-semibold text-indigo-500">Progres: {{ number_format($row['actual_progress'], 1) }}%</span>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-right whitespace-nowrap">
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format($row['budget_cost'], 0, ',', '.') }}</span>
                        </td>
                        <td class="px-3 py-3 text-right whitespace-nowrap">
                            <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">Rp {{ number_format($row['earned_value'], 0, ',', '.') }}</span>
                        </td>
                        <td class="px-3 py-3 text-right whitespace-nowrap">
                            <span class="text-sm font-bold text-orange-600 dark:text-orange-400">Rp {{ number_format($row['actual_cost'], 0, ',', '.') }}</span>
                        </td>
                        <td class="px-3 py-3 text-right whitespace-nowrap">
                            <div class="flex flex-col items-end">
                                <span class="text-sm font-bold {{ $row['cost_variance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $row['cost_variance'] >= 0 ? '+' : '' }}Rp {{ number_format($row['cost_variance'], 0, ',', '.') }}
                                </span>
                                <span class="text-[10px] font-bold opacity-70 {{ $row['cost_variance'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $row['status'] }}
                                </span>
                            </div>
                        </td>
                        <td class="px-3 py-3 min-w-[200px]">
                            @php
                                $percentUsed = $row['budget_cost'] > 0 ? min(100, ($row['actual_cost'] / $row['budget_cost']) * 100) : 0;
                                $percentEarned = $row['budget_cost'] > 0 ? min(100, ($row['earned_value'] / $row['budget_cost']) * 100) : 0;
                                $isOverBudget = $row['actual_cost'] > $row['earned_value'];
                            @endphp
                            <div class="flex flex-col space-y-1.5">
                                <div class="w-full bg-gray-100 dark:bg-dark-700 rounded-full h-2.5 overflow-hidden relative">
                                    <!-- Earned Value Bar (Background) -->
                                    <div class="bg-indigo-500/30 absolute h-full rounded-full transition-all duration-500" style="width: {{ $percentEarned }}%"></div>
                                    <!-- Actual Cost Bar (Foreground) -->
                                    <div class="{{ $isOverBudget ? 'bg-red-500' : 'bg-green-500' }} h-full rounded-full transition-all duration-500 relative z-10" style="width: {{ $percentUsed }}%"></div>
                                </div>
                                <div class="flex justify-between text-[10px] font-bold text-gray-400">
                                    <span>HPP: {{ round($percentUsed) }}%</span>
                                    <span class="{{ $isOverBudget ? 'text-red-500' : 'text-green-500' }}">
                                        {{ $isOverBudget ? 'Melebihi Progres' : 'Di Bawah Progres' }}
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <x-heroicon-o-inbox class="w-12 h-12 text-gray-200 mb-4" />
                                <p class="text-gray-500 dark:text-gray-400 font-medium">Tidak ada data yang ditemukan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>


