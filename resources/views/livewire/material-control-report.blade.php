<div>
    <div class="bg-white dark:bg-dark-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-dark-700">
        <div class="p-4 border-b border-gray-100 dark:border-dark-700 bg-gray-50/50 dark:bg-dark-800/50 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h3 class="text-lg font-extrabold text-gray-900 dark:text-gray-100">Kontrol Pemakaian Material</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Monitoring perbandingan Kebutuhan RAB (RAP) vs Pengeluaran Riil Gudang</p>
            </div>
            
            <div class="relative w-full md:w-64">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari material..." 
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
                        <th class="px-3 py-2 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Material</th>
                        <th class="px-3 py-2 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">Budget (RAP)</th>
                        <th class="px-3 py-2 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">Terpakai (Usage)</th>
                        <th class="px-3 py-2 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">Sisa / Deviasi</th>
                        <th class="px-3 py-2 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">Konsumsi (%)</th>
                        <th class="px-3 py-2 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-dark-700">
                    @forelse ($data as $row)
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-indigo-900/10 transition-colors">
                        <td class="px-3 py-3">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $row['material_name'] }}</span>
                                <span class="text-[10px] text-gray-400 font-mono">ID: {{ $row['material_id'] }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-right whitespace-nowrap">
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($row['budget_qty'], 2) }} {{ $row['unit'] }}</span>
                        </td>
                        <td class="px-3 py-3 text-right whitespace-nowrap">
                            <span class="text-sm font-bold text-orange-600 dark:text-orange-400">{{ number_format($row['actual_qty'], 2) }} {{ $row['unit'] }}</span>
                        </td>
                        <td class="px-3 py-3 text-right whitespace-nowrap">
                            <span class="text-sm font-bold {{ $row['variance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $row['variance'] >= 0 ? '+' : '' }}{{ number_format($row['variance'], 2) }} {{ $row['unit'] }}
                            </span>
                        </td>
                        <td class="px-3 py-3 min-w-[150px]">
                            <div class="flex flex-col space-y-1.5">
                                <div class="w-full bg-gray-100 dark:bg-dark-700 rounded-full h-2 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $row['percent_used'] > 100 ? 'bg-red-500' : ($row['percent_used'] > 90 ? 'bg-yellow-500' : 'bg-blue-500') }}" 
                                        style="width: {{ min(100, $row['percent_used']) }}%"></div>
                                </div>
                                <span class="text-[10px] text-center font-bold {{ $row['percent_used'] > 100 ? 'text-red-500' : 'text-gray-400' }}">
                                    {{ round($row['percent_used'], 1) }}% Kapasitas Terpakai
                                </span>
                            </div>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-center">
                            @if ($row['variance'] >= 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                    <x-heroicon-s-check-circle class="w-3 h-3 mr-1" />
                                    Aman
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 animate-pulse">
                                    <x-heroicon-s-exclamation-triangle class="w-3 h-3 mr-1" />
                                    Over-Usage
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center text-gray-400">
                                <x-heroicon-o-archive-box-x-mark class="w-12 h-12 mb-4 opacity-20" />
                                <p class="font-medium">Belum ada data pemakaian atau peramalan material.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>


