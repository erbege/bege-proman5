<div>


    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @if (session()->has('success'))
                <div
                    class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 rounded-lg flex items-center">
                    <x-heroicon-o-check-circle class="w-5 h-5 mr-2" />
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <!-- Header with View Mode Toggle -->
                <div
                    class="p-4 border-b border-gray-200 dark:border-dark-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center space-x-4">
                        <div>
                            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                                Laporan Progress - {{ $project->name }}
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->code }}</p>
                        </div>
                        <span
                            class="px-3 py-1 bg-gray-100 dark:bg-dark-700 text-gray-600 dark:text-gray-400 rounded-full text-xs font-medium">{{ $reports->total() }}
                            laporan</span>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Search -->
                        <div class="relative max-w-xs">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-heroicon-o-magnifying-glass class="h-4 w-4 text-gray-400" />
                            </div>
                            <input wire:model.live.debounce.300ms="search" type="text"
                                class="block w-full pl-9 pr-3 py-1.5 border border-gray-300 dark:border-dark-700 rounded-md bg-white dark:bg-dark-900 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:ring-gold-500 focus:border-gold-500 text-sm"
                                placeholder="Cari...">
                        </div>

                        <!-- View Mode -->
                        <div class="flex items-center space-x-1 bg-gray-100 dark:bg-dark-700 rounded-lg p-1">
                            <button wire:click="$set('viewMode', 'list')"
                                class="p-2 rounded-md transition-all duration-200 {{ $viewMode === 'list' ? 'bg-white dark:bg-dark-600 text-gold-600 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700' }}"
                                title="List View">
                                <x-heroicon-o-bars-3 class="w-5 h-5" />
                            </button>
                            <button wire:click="$set('viewMode', 'grid')"
                                class="p-2 rounded-md transition-all duration-200 {{ $viewMode === 'grid' ? 'bg-white dark:bg-dark-600 text-gold-600 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700' }}"
                                title="Grid View">
                                <x-heroicon-o-squares-2x2 class="w-5 h-5" />
                            </button>
                            <button wire:click="$set('viewMode', 'table')"
                                class="p-2 rounded-md transition-all duration-200 {{ $viewMode === 'table' ? 'bg-white dark:bg-dark-600 text-gold-600 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700' }}"
                                title="Compact Table">
                                <x-heroicon-o-table-cells class="w-5 h-5" />
                            </button>
                        </div>

                        <button wire:click="openModal"
                            class="inline-flex items-center px-4 py-2 bg-gold-500 border border-transparent rounded-md font-semibold text-xs text-gray-900 uppercase tracking-widest hover:bg-gold-600 transform active:scale-95 transition-all">
                            <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                            Tambah
                        </button>
                    </div>
                </div>

                <div class="p-4">
                    @if ($reports->count() > 0)
                        <!-- LIST VIEW -->
                        @if ($viewMode === 'list')
                            <div class="space-y-4">
                                @foreach ($reports as $report)
                                    <div class="border dark:border-dark-700 rounded-lg p-3 hover:shadow-md transition-shadow duration-200 hover:border-gold-300 dark:hover:border-gold-700 cursor-pointer"
                                        wire:click="showDetail({{ $report->id }})"
                                        wire:loading.class="opacity-50 pointer-events-none"
                                        wire:target="showDetail({{ $report->id }})">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-3">
                                                    <div
                                                        class="flex-shrink-0 w-12 h-12 bg-gold-100 dark:bg-gold-900/30 rounded-lg flex items-center justify-center">
                                                        <span
                                                            class="text-lg font-bold text-gold-600 dark:text-gold-400">{{ $report->report_date->format('d') }}</span>
                                                    </div>
                                                    <div>
                                                        <span
                                                            class="text-lg font-semibold text-gray-900 dark:text-white">
                                                            {{ $report->report_date->format('F Y') }}
                                                        </span>
                                                        @if ($report->rabItem)
                                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                                {{ $report->rabItem->work_name }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="flex flex-wrap items-center gap-3 mt-3">
                                                    <!-- Status Badge -->
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black uppercase tracking-tighter
                                                                {{ $report->status === 'draft'
                                                                    ? 'bg-gray-100 text-gray-700 dark:bg-gray-900/50 dark:text-gray-300'
                                                                    : ($report->status === 'submitted'
                                                                        ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300'
                                                                        : ($report->status === 'reviewed'
                                                                            ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300'
                                                                            : ($report->status === 'rejected'
                                                                                ? 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300'
                                                                                : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300'))) }}">
                                                        @switch($report->status)
                                                            @case('draft')
                                                                <x-heroicon-s-document class="w-3 h-3 mr-1" /> Draft
                                                            @break

                                                            @case('submitted')
                                                                <x-heroicon-s-arrow-up-tray class="w-3 h-3 mr-1" /> Diajukan
                                                            @break

                                                            @case('reviewed')
                                                                <x-heroicon-s-check-circle class="w-3 h-3 mr-1" /> Diverifikasi
                                                            @break

                                                            @case('rejected')
                                                                <x-heroicon-s-x-circle class="w-3 h-3 mr-1" /> Ditolak
                                                            @break

                                                            @case('published')
                                                                <x-heroicon-s-star class="w-3 h-3 mr-1" /> Dipublikasi
                                                            @break
                                                        @endswitch
                                                    </span>

                                                    <!-- Progress Badge -->
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                                                                                                                                                                                                        {{ $report->progress_percentage >= 80
                                                                                                                                                                                                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300'
                                                                                                                                                                                                                            : ($report->progress_percentage >= 50
                                                                                                                                                                                                                                ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300'
                                                                                                                                                                                                                                : 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300') }}">
                                                        <x-heroicon-s-arrow-trending-up class="w-4 h-4 mr-1" />
                                                        {{ number_format($report->progress_percentage, 1) }}%
                                                    </span>

                                                    @if ($report->weather)
                                                        @php $weatherIcons = ['sunny' => '☀️', 'cloudy' => '☁️', 'rainy' => '🌧️', 'stormy' => '⛈️']; @endphp
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-sky-100 text-sky-800 dark:bg-sky-900/50 dark:text-sky-300">
                                                            {{ $weatherIcons[$report->weather] ?? '' }}
                                                            {{ $weatherOptions[$report->weather] ?? ucfirst($report->weather) }}
                                                        </span>
                                                    @endif

                                                    @if ($report->workers_count)
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700 dark:bg-dark-700 dark:text-gray-300">
                                                            <x-heroicon-o-user-group class="w-4 h-4 mr-1" />
                                                            {{ $report->workers_count }} pekerja
                                                        </span>
                                                    @endif
                                                </div>

                                                @if ($report->description)
                                                    <p
                                                        class="text-sm text-gray-600 dark:text-gray-400 mt-3 line-clamp-2">
                                                        {{ $report->description }}
                                                    </p>
                                                @endif

                                                @if (!empty($report->next_day_plan))
                                                    <div
                                                        class="mt-3 p-2 rounded-lg bg-sky-50 dark:bg-sky-900/20 border border-sky-100 dark:border-sky-800/50">
                                                        <p
                                                            class="text-[10px] font-bold uppercase tracking-wider text-sky-700 dark:text-sky-300 mb-1">
                                                            Rencana Esok Hari
                                                        </p>
                                                        <p class="text-xs text-sky-800 dark:text-sky-200 line-clamp-2">
                                                            {{ $report->next_day_plan }}
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="flex items-center space-x-2 ml-4">
                                                <button type="button"
                                                    wire:click.stop="showDetail({{ $report->id }})"
                                                    class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition"
                                                    title="Lihat Detail">
                                                    <x-heroicon-o-eye class="w-5 h-5" />
                                                </button>
                                                @if ($report->is_editable)
                                                    <button type="button"
                                                        wire:click.stop="openModal({{ $report->id }})"
                                                        class="p-2 text-gray-500 hover:text-gold-600 hover:bg-gold-50 dark:hover:bg-gold-900/20 rounded-lg transition"
                                                        title="Edit">
                                                        <x-heroicon-o-pencil-square class="w-5 h-5" />
                                                    </button>
                                                    <button type="button"
                                                        wire:click.stop="confirmDelete({{ $report->id }})"
                                                        class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"
                                                        title="Hapus">
                                                        <x-heroicon-o-trash class="w-5 h-5" />
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- GRID VIEW -->
                        @if ($viewMode === 'grid')
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($reports as $report)
                                    <div class="border dark:border-dark-700 rounded-xl overflow-hidden hover:shadow-lg transition-all duration-200 hover:border-gold-300 dark:hover:border-gold-700 cursor-pointer"
                                        wire:click="showDetail({{ $report->id }})">
                                        <div class="bg-gradient-to-r from-gold-500 to-gold-600 px-3 py-1.5 text-white">
                                            <div class="flex justify-between items-center">
                                                <span
                                                    class="font-bold">{{ $report->report_date->format('d M Y') }}</span>
                                                @if ($report->weather)
                                                    @php $weatherIcons = ['sunny' => '☀️', 'cloudy' => '☁️', 'rainy' => '🌧️', 'stormy' => '⛈️']; @endphp
                                                    <span class="text-white/90 text-lg">
                                                        {{ $weatherIcons[$report->weather] ?? '' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="p-4">
                                            <div class="flex items-center justify-center mb-4">
                                                <div class="relative w-20 h-20">
                                                    <svg class="w-20 h-20 transform -rotate-90">
                                                        <circle cx="40" cy="40" r="36" stroke-width="8"
                                                            fill="transparent"
                                                            class="stroke-gray-200 dark:stroke-dark-600" />
                                                        <circle cx="40" cy="40" r="36" stroke-width="8"
                                                            fill="transparent" stroke-dasharray="{{ 226 }}"
                                                            stroke-dashoffset="{{ 226 - (226 * min($report->progress_percentage, 100)) / 100 }}"
                                                            class="stroke-gold-500 transition-all duration-500" />
                                                    </svg>
                                                    <span
                                                        class="absolute inset-0 flex items-center justify-center text-lg font-bold text-gray-900 dark:text-white">
                                                        {{ number_format($report->progress_percentage, 0) }}%
                                                    </span>
                                                </div>
                                            </div>

                                            @if ($report->rabItem)
                                                <h4
                                                    class="font-medium text-gray-900 dark:text-white text-center text-sm truncate mb-2">
                                                    {{ $report->rabItem->work_name }}
                                                </h4>
                                            @endif

                                            @if ($report->workers_count)
                                                <p class="text-center text-xs text-gray-500 dark:text-gray-400">
                                                    <x-heroicon-o-user-group class="w-4 h-4 inline" />
                                                    {{ $report->workers_count }}
                                                    pekerja
                                                </p>
                                            @endif

                                            @php
                                                $incidents = (int) data_get($report->safety_details, 'incidents', 0);
                                            @endphp
                                            @if ($incidents > 0)
                                                <p
                                                    class="text-center text-xs text-red-600 dark:text-red-400 mt-1 font-semibold">
                                                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 inline" />
                                                    {{ $incidents }} insiden K3
                                                </p>
                                            @endif

                                            @if (!empty($report->next_day_plan))
                                                <p
                                                    class="text-xs text-sky-700 dark:text-sky-300 mt-2 line-clamp-2 bg-sky-50 dark:bg-sky-900/20 rounded-md p-2">
                                                    {{ $report->next_day_plan }}
                                                </p>
                                            @endif

                                            <div
                                                class="flex justify-center space-x-2 mt-4 pt-4 border-t dark:border-dark-700">
                                                <button type="button"
                                                    wire:click.stop="showDetail({{ $report->id }})"
                                                    class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition">
                                                    Detail
                                                </button>
                                                @if ($report->is_editable)
                                                    <button type="button"
                                                        wire:click.stop="openModal({{ $report->id }})"
                                                        class="px-3 py-1.5 text-xs font-medium text-gold-600 hover:bg-gold-50 dark:hover:bg-gold-900/20 rounded-lg transition">
                                                        Edit
                                                    </button>
                                                    <button type="button"
                                                        wire:click.stop="confirmDelete({{ $report->id }})"
                                                        class="px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                                                        Hapus
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- COMPACT TABLE VIEW -->
                        @if ($viewMode === 'table')
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-700">
                                    <thead class="bg-gray-50 dark:bg-dark-700">
                                        <tr>
                                            <th
                                                class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Status</th>
                                            <th
                                                class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Tanggal</th>
                                            <th
                                                class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Pekerjaan</th>
                                            <th
                                                class="px-3 py-1.5 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Progress</th>
                                            <th
                                                class="px-3 py-1.5 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Cuaca</th>
                                            <th
                                                class="px-3 py-1.5 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Pekerja</th>
                                            <th
                                                class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white dark:bg-dark-800 divide-y divide-gray-200 dark:divide-dark-700">
                                        @foreach ($reports as $report)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-dark-700/50 transition cursor-pointer"
                                                wire:click="showDetail({{ $report->id }})">
                                                <td class="px-3 py-1.5 whitespace-nowrap">
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-tighter
                                                        {{ $report->status === 'draft'
                                                            ? 'bg-gray-100 text-gray-700 dark:bg-gray-900/50 dark:text-gray-300'
                                                            : ($report->status === 'submitted'
                                                                ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300'
                                                                : ($report->status === 'reviewed'
                                                                    ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300'
                                                                    : ($report->status === 'rejected'
                                                                        ? 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300'
                                                                        : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300'))) }}">
                                                        @switch($report->status)
                                                            @case('draft')
                                                                <x-heroicon-s-document class="w-2.5 h-2.5 mr-0.5" /> Draft
                                                            @break

                                                            @case('submitted')
                                                                <x-heroicon-s-arrow-up-tray class="w-2.5 h-2.5 mr-0.5" />
                                                                Submit
                                                            @break

                                                            @case('reviewed')
                                                                <x-heroicon-s-check-circle class="w-2.5 h-2.5 mr-0.5" /> Verif
                                                            @break

                                                            @case('rejected')
                                                                <x-heroicon-s-x-circle class="w-2.5 h-2.5 mr-0.5" /> Tolak
                                                            @break

                                                            @case('published')
                                                                <x-heroicon-s-star class="w-2.5 h-2.5 mr-0.5" /> Pub
                                                            @break
                                                        @endswitch
                                                    </span>
                                                </td>
                                                <td class="px-3 py-1.5 whitespace-nowrap">
                                                    <span
                                                        class="text-sm font-medium text-gray-900 dark:text-white">{{ $report->report_date->format('d M Y') }}</span>
                                                </td>
                                                <td class="px-3 py-1.5">
                                                    <span
                                                        class="text-sm text-gray-900 dark:text-white truncate block max-w-xs">
                                                        {{ $report->rabItem->work_name ?? '-' }}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-1.5 text-center">
                                                    <div class="flex items-center justify-center space-x-2">
                                                        <div
                                                            class="w-16 bg-gray-200 dark:bg-dark-600 rounded-full h-2">
                                                            <div class="bg-gold-500 h-2 rounded-full"
                                                                style="width: {{ min($report->progress_percentage, 100) }}%">
                                                            </div>
                                                        </div>
                                                        <span
                                                            class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($report->progress_percentage, 0) }}%</span>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-1.5 text-center">
                                                    @php $weatherIcons = ['sunny' => '☀️', 'cloudy' => '☁️', 'rainy' => '🌧️', 'stormy' => '⛈️']; @endphp
                                                    <span>{{ $weatherIcons[$report->weather] ?? '-' }}</span>
                                                </td>
                                                <td
                                                    class="px-3 py-1.5 text-center text-sm text-gray-900 dark:text-white">
                                                    {{ $report->workers_count ?? '-' }}
                                                </td>
                                                <td class="px-3 py-1.5 text-right">
                                                    <div class="flex justify-end space-x-1">
                                                        <button type="button"
                                                            wire:click.stop="showDetail({{ $report->id }})"
                                                            class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition"
                                                            title="Detail">
                                                            <x-heroicon-o-eye class="w-4 h-4" />
                                                        </button>
                                                        @if ($report->is_editable)
                                                            <button type="button"
                                                                wire:click.stop="openModal({{ $report->id }})"
                                                                class="p-1.5 text-gray-500 hover:text-gold-600 hover:bg-gold-50 dark:hover:bg-gold-900/20 rounded transition"
                                                                title="Edit">
                                                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                            </button>
                                                            <button type="button"
                                                                wire:click.stop="confirmDelete({{ $report->id }})"
                                                                class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition"
                                                                title="Hapus">
                                                                <x-heroicon-o-trash class="w-4 h-4" />
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <div class="mt-6">
                            {{ $reports->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <x-heroicon-o-document-chart-bar
                                class="w-16 h-16 mx-auto text-gray-300 dark:text-dark-600 mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Belum ada laporan
                                progress
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">Mulai catat progress pekerjaan proyek
                                Anda.</p>
                            <button wire:click="openModal"
                                class="inline-flex items-center px-4 py-2 bg-gold-500 text-gray-900 rounded-lg font-medium hover:bg-gold-600 transition">
                                <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                                Tambah Laporan Pertama
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-6">
                <a href="{{ route('projects.show', $project) }}"
                    class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gold-600 dark:hover:text-gold-400 transition">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                    Kembali ke Detail Proyek
                </a>
            </div>
        </div>
    </div>

    {{-- Add Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay" aria-modal="true" x-data="{
            modalOpen: false,
            init() {
                setTimeout(() => this.modalOpen = true, 50);
            },
            close() {
                this.modalOpen = false;
                setTimeout(() => $wire.closeModal(), 300);
            }
        }"
            x-init="init()">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity duration-300"
                    x-show="modalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" @click="close()"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border dark:border-dark-700"
                    x-show="modalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <form wire:submit="save" x-data="{ activeTab: 'basic', quickEntry: false }">
                        <!-- Premium Form Header -->
                        <div
                            class="bg-white dark:bg-dark-800 border-b dark:border-dark-700 px-6 py-4 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-gold-500 rounded-xl text-white shadow-lg shadow-gold-500/20">
                                    @if ($editingId)
                                        <x-heroicon-s-pencil-square class="w-5 h-5" />
                                    @else
                                        <x-heroicon-s-plus-circle class="w-5 h-5" />
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-lg font-black text-gray-900 dark:text-white tracking-tight flex items-center">
                                        {{ $editingId ? 'Ubah Laporan Progres' : 'Laporan Progres Baru' }}
                                        <label class="ml-4 inline-flex items-center cursor-pointer group">
                                            <div class="relative inline-flex h-5 w-9 bg-gray-200 rounded-full peer-checked:bg-gold-500 dark:bg-dark-700 transition-colors" @click="quickEntry = !quickEntry">
                                                <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full transition-transform" :class="{'translate-x-4': quickEntry}"></div>
                                            </div>
                                            <span class="ml-2 text-[10px] font-bold uppercase tracking-widest" :class="quickEntry ? 'text-gold-500' : 'text-gray-400'">Quick Entry</span>
                                        </label>
                                    </h3>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Input Data
                                        Harian Lapangan</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if (!$editingId)
                                    <button type="button" wire:click="copyFromYesterday"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center px-3 py-2 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-400 rounded-lg text-xs font-bold uppercase tracking-tight transition-all disabled:opacity-50 disabled:cursor-not-allowed group">
                                        <x-heroicon-o-document-duplicate
                                            class="w-4 h-4 mr-1.5 group-hover:-translate-x-1 transition-transform" />
                                        Copy Kemarin
                                        <span wire:loading wire:target="copyFromYesterday" class="ml-1 animate-spin">
                                            <x-heroicon-o-arrow-path class="w-3 h-3" />
                                        </span>
                                    </button>
                                @endif
                                <button type="button" @click="close()"
                                    class="p-2 text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-dark-700 rounded-full transition-all group">
                                    <x-heroicon-o-x-mark
                                        class="w-6 h-6 transform group-hover:rotate-90 transition-transform duration-300" />
                                </button>
                            </div>
                        </div>

                        
                        <!-- Tabs Navigation (Hidden in Quick Entry) -->
                        <div class="px-6 py-2 border-b dark:border-dark-700 bg-gray-50 dark:bg-dark-900/50 flex space-x-4 overflow-x-auto scrollbar-none" x-show="!quickEntry">
                            <button type="button" @click="activeTab = 'basic'" :class="{'border-gold-500 text-gold-600': activeTab === 'basic', 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300': activeTab !== 'basic'}" class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-colors flex items-center">
                                <x-heroicon-o-information-circle class="w-4 h-4 mr-1.5" /> Utama
                            </button>
                            <button type="button" @click="activeTab = 'field'" :class="{'border-sky-500 text-sky-600': activeTab === 'field', 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300': activeTab !== 'field'}" class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-colors flex items-center">
                                <x-heroicon-o-cloud class="w-4 h-4 mr-1.5" /> Lapangan
                            </button>
                            <button type="button" @click="activeTab = 'notes'" :class="{'border-emerald-500 text-emerald-600': activeTab === 'notes', 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300': activeTab !== 'notes'}" class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-colors flex items-center">
                                <x-heroicon-o-document-text class="w-4 h-4 mr-1.5" /> Catatan
                            </button>
                            <button type="button" @click="activeTab = 'resources'" :class="{'border-blue-500 text-blue-600': activeTab === 'resources', 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300': activeTab !== 'resources'}" class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-colors flex items-center">
                                <x-heroicon-o-truck class="w-4 h-4 mr-1.5" /> Sumber Daya
                            </button>
                            <button type="button" @click="activeTab = 'safety'" :class="{'border-red-500 text-red-600': activeTab === 'safety', 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300': activeTab !== 'safety'}" class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-colors flex items-center">
                                <x-heroicon-o-shield-exclamation class="w-4 h-4 mr-1.5" /> K3/Safety
                            </button>
                            <button type="button" @click="activeTab = 'photos'" :class="{'border-purple-500 text-purple-600': activeTab === 'photos', 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300': activeTab !== 'photos'}" class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-colors flex items-center">
                                <x-heroicon-o-camera class="w-4 h-4 mr-1.5" /> Foto
                            </button>
                        </div>

                        <div
                            class="px-6 py-6 max-h-[60vh] overflow-y-auto scrollbar-thin scrollbar-thumb-gold-500 scrollbar-track-transparent space-y-8">

                            <!-- Section: Core Information -->
                            <div x-show="quickEntry || activeTab === 'basic'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="space-y-6">
                                <h4
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 flex items-center">
                                    <span class="w-4 h-[2px] bg-gold-500 mr-2"></span>
                                    Informasi Utama
                                </h4>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="relative group">
                                        <label
                                            class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Tanggal
                                            Laporan</label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <x-heroicon-o-calendar class="h-4 w-4 text-gold-500" />
                                            </div>
                                            <input wire:model="reportDate" type="date" required
                                                class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-dark-900 border-none rounded-xl text-sm text-gray-900 dark:text-white ring-1 ring-gray-200 dark:ring-dark-700 focus:ring-2 focus:ring-gold-500 transition-all">
                                        </div>
                                    </div>

                                    <div class="relative group">
                                        <label
                                            class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Item
                                            Pekerjaan (RAB)</label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gold-500">
                                                <x-heroicon-o-list-bullet class="h-4 w-4" />
                                            </div>
                                            <select wire:model="rabItemId"
                                                class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-dark-900 border-none rounded-xl text-sm text-gray-900 dark:text-white ring-1 ring-gray-200 dark:ring-dark-700 focus:ring-2 focus:ring-gold-500 transition-all appearance-none">
                                                <option value="">-- Umum / Non-Spesifik --</option>
                                                @foreach ($rabItems as $rabItem)
                                                    <option value="{{ $rabItem->id }}">{{ $rabItem->work_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="relative group">
                                    <div class="flex justify-between items-center mb-1.5 ml-1">
                                        <label
                                            class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">Target
                                            Progres Hari Ini (%)</label>
                                        <span class="text-xs font-black text-gold-600 tracking-tighter"
                                            x-text="$wire.progressPercentage + '%'"></span>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-1">
                                            <input wire:model.live="progressPercentage" type="range" min="0"
                                                max="100" step="0.01"
                                                class="w-full h-2 bg-gray-200 dark:bg-dark-700 rounded-lg appearance-none cursor-pointer accent-gold-500">
                                        </div>
                                        <input wire:model.live="progressPercentage" type="number" step="0.01"
                                            min="0" max="100"
                                            class="w-20 px-3 py-2 bg-gray-50 dark:bg-dark-900 border-none rounded-xl text-xs font-bold text-center ring-1 ring-gray-200 dark:ring-dark-700 focus:ring-2 focus:ring-gold-500">
                                    </div>
                                    <!-- Enhanced Progress Bar Visualization -->
                                    <div class="mt-4 space-y-3">
                                        <div class="space-y-1">
                                            <div
                                                class="flex justify-between items-center text-[10px] font-bold text-gray-500 dark:text-gray-400">
                                                <span>Progres Hari Ini</span>
                                                <span x-text="$wire.progressPercentage.toFixed(1) + '%'"></span>
                                            </div>
                                            <div
                                                class="h-2.5 w-full bg-gray-100 dark:bg-dark-900 rounded-full overflow-hidden">
                                                <div class="h-full bg-gradient-to-r from-gold-400 to-gold-600 transition-all duration-300"
                                                    style="width: {{ $progressPercentage }}%"></div>
                                            </div>
                                        </div>
                                        @if ($rabItemId)
                                            <div class="pt-2 border-t dark:border-dark-700 space-y-1">
                                                <div
                                                    class="flex justify-between items-center text-[10px] font-bold text-gray-500 dark:text-gray-400">
                                                    <span>Perkiraan Kumulatif</span>
                                                    <span
                                                        class="text-gold-600 dark:text-gold-400">{{ number_format($this->projectedCumulative, 1) }}%</span>
                                                </div>
                                                <div
                                                    class="h-2.5 w-full bg-gray-100 dark:bg-dark-900 rounded-full overflow-hidden">
                                                    <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 transition-all duration-300"
                                                        style="width: {{ $this->projectedCumulative }}%"></div>
                                                </div>
                                                <div
                                                    class="flex justify-between text-[9px] text-gray-400 dark:text-gray-500 mt-1">
                                                    <span>{{ $this->selectedRabItem ? number_format($this->selectedRabItem->actual_progress, 1) : '0' }}%
                                                        (kemarin)</span>
                                                    <span>{{ number_format($this->projectedCumulative, 1) }}% (target)</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Field Conditions -->
                            <div x-show="quickEntry || activeTab === 'field'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="space-y-6" :class="{'mt-8': quickEntry && activeTab !== 'field'}">
                                <h4
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 flex items-center">
                                    <span class="w-4 h-[2px] bg-sky-500 mr-2"></span>
                                    Kondisi Lapangan
                                </h4>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between mb-1 ml-1">
                                            <label
                                                class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">Cuaca
                                                Teramati</label>
                                            <button type="button" wire:click="fetchWeather"
                                                class="inline-flex items-center text-[9px] font-black uppercase tracking-tighter text-sky-600 hover:text-sky-700 transition group/fetch"
                                                wire:loading.attr="disabled">
                                                <x-heroicon-o-cloud-arrow-down
                                                    class="w-3.5 h-3.5 mr-1 transform group-hover/fetch:-translate-y-0.5 transition-transform" />
                                                Auto Fetch
                                                <span wire:loading wire:target="fetchWeather"
                                                    class="ml-1 animate-spin">
                                                    <x-heroicon-o-arrow-path class="w-3 h-3" />
                                                </span>
                                            </button>
                                        </div>
                                        <div class="grid grid-cols-4 gap-2">
                                            @foreach ($weatherOptions as $key => $label)
                                                <button type="button"
                                                    wire:click="$set('weather', '{{ $key }}')"
                                                    class="flex flex-col items-center justify-center p-2 rounded-xl border transition-all duration-200
                                                    {{ $weather === $key ? 'bg-sky-500 border-sky-500 text-white shadow-lg shadow-sky-500/20 scale-105' : 'bg-white dark:bg-dark-900 border-gray-100 dark:border-dark-700 text-gray-400 hover:border-sky-300' }}">
                                                    @php $weatherIcons = ['sunny' => '☀️', 'cloudy' => '☁️', 'rainy' => '🌧️', 'stormy' => '⛈️']; @endphp
                                                    <span class="text-xl mb-1">{{ $weatherIcons[$key] }}</span>
                                                    <span
                                                        class="text-[9px] font-bold uppercase">{{ $label }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                        @if (session()->has('weather_success'))
                                            <p
                                                class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold italic mt-2 ml-1">
                                                ✓ {{ session('weather_success') }}</p>
                                        @endif
                                    </div>

                                    <div class="space-y-2">
                                        <label
                                            class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Durasi
                                            Efektif / Dampak</label>
                                        <input wire:model="weatherDuration" type="text"
                                            placeholder="Contoh: 3 jam hujan deras"
                                            class="block w-full px-4 py-2.5 bg-gray-50 dark:bg-dark-900 border-none rounded-xl text-sm text-gray-900 dark:text-white ring-1 ring-gray-200 dark:ring-dark-700 focus:ring-2 focus:ring-sky-500 transition-all">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label
                                            class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Total
                                            Tenaga Kerja</label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <x-heroicon-o-users class="h-4 w-4 text-purple-500" />
                                            </div>
                                            <input wire:model="workerCount" type="number" min="0"
                                                class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-dark-900 border-none rounded-xl text-sm font-bold text-gray-900 dark:text-white ring-1 ring-gray-200 dark:ring-dark-700 focus:ring-2 focus:ring-purple-500 transition-all">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Detailed Notes -->
                            <div x-show="quickEntry || activeTab === 'notes'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="space-y-6" :class="{'mt-8': quickEntry && activeTab !== 'notes'}">
                                <h4
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 flex items-center">
                                    <span class="w-4 h-[2px] bg-emerald-500 mr-2"></span>
                                    Catatan Pekerjaan
                                </h4>

                                <div class="space-y-4">
                                    <div class="relative">
                                        <label
                                            class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Deskripsi
                                            Aktivitas Hari Ini</label>
                                        <textarea wire:model="description" rows="3"
                                            class="block w-full px-4 py-3 bg-gray-50 dark:bg-dark-900 border-none rounded-2xl text-sm text-gray-900 dark:text-white ring-1 ring-gray-200 dark:ring-dark-700 focus:ring-2 focus:ring-emerald-500 transition-all leading-relaxed"
                                            placeholder="Jelaskan apa yang dikerjakan..."></textarea>
                                    </div>

                                    <div class="relative">
                                        <label
                                            class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1 text-red-400">Kendala
                                            / Isu Lapangan</label>
                                        <textarea wire:model="issues" rows="2"
                                            class="block w-full px-4 py-3 bg-red-50/30 dark:bg-red-950/10 border-none rounded-2xl text-sm text-gray-900 dark:text-white ring-1 ring-red-100 dark:ring-red-900/30 focus:ring-2 focus:ring-red-500 transition-all leading-relaxed placeholder-red-300"
                                            placeholder="Tuliskan kendala jika ada..."></textarea>
                                    </div>

                                    <div class="relative">
                                        <label
                                            class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1 text-sky-400">Rencana
                                            Kerja Esok Hari</label>
                                        <textarea wire:model="nextDayPlan" rows="2"
                                            class="block w-full px-4 py-3 bg-sky-50/30 dark:bg-sky-950/10 border-none rounded-2xl text-sm text-gray-900 dark:text-white ring-1 ring-sky-100 dark:ring-sky-900/30 focus:ring-2 focus:ring-sky-500 transition-all leading-relaxed placeholder-sky-300"
                                            placeholder="Apa rencana besok?"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Dynamic Resources -->
                            <div x-show="!quickEntry && activeTab === 'resources'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="space-y-6">
                                <h4
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 flex items-center">
                                    <span class="w-4 h-[2px] bg-blue-500 mr-2"></span>
                                    Alat & Material (PUPR)
                                </h4>

                                <!-- Equipment -->
                                <div
                                    class="bg-white dark:bg-dark-900 rounded-2xl border dark:border-dark-700 overflow-hidden shadow-sm">
                                    <div
                                        class="px-4 py-3 bg-gray-50 dark:bg-dark-800 border-b dark:border-dark-700 flex justify-between items-center">
                                        <span
                                            class="text-[10px] font-black text-gray-600 dark:text-gray-400 uppercase tracking-widest">Daftar
                                            Peralatan</span>
                                        <button type="button"
                                            wire:click="$set('equipmentDetails', {{ json_encode(array_merge($equipmentDetails, [['name' => '', 'qty' => 1, 'condition' => 'baik', 'hours' => 0]])) }})"
                                            class="text-[9px] font-black uppercase text-blue-600 hover:text-blue-800 flex items-center">
                                            <x-heroicon-s-plus-circle class="w-3.5 h-3.5 mr-1" /> Tambah
                                        </button>
                                    </div>
                                    <div class="p-4 space-y-3">
                                        @foreach ($equipmentDetails as $idx => $eq)
                                            <div class="flex items-center space-x-3 group">
                                                <div class="flex-1 grid grid-cols-12 gap-2">
                                                    <div class="col-span-5">
                                                        <input wire:model="equipmentDetails.{{ $idx }}.name"
                                                            type="text" placeholder="Nama Alat"
                                                            class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-800 border-none rounded-lg text-xs ring-1 ring-gray-100 dark:ring-dark-700 focus:ring-1 focus:ring-blue-500">
                                                    </div>
                                                    <div class="col-span-2">
                                                        <input wire:model="equipmentDetails.{{ $idx }}.qty"
                                                            type="number" placeholder="Qty"
                                                            class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-800 border-none rounded-lg text-xs font-bold ring-1 ring-gray-100 dark:ring-dark-700 focus:ring-1 focus:ring-blue-500 text-center">
                                                    </div>
                                                    <div class="col-span-3">
                                                        <select
                                                            wire:model="equipmentDetails.{{ $idx }}.condition"
                                                            class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-800 border-none rounded-lg text-[10px] font-bold ring-1 ring-gray-100 dark:ring-dark-700 focus:ring-1 focus:ring-blue-500">
                                                            <option value="baik">Baik</option>
                                                            <option value="idle">Idle</option>
                                                            <option value="rusak_ringan">Rusak (R)</option>
                                                            <option value="rusak_berat">Rusak (B)</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-span-2">
                                                        <input wire:model="equipmentDetails.{{ $idx }}.hours"
                                                            type="number" placeholder="Jam"
                                                            class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-800 border-none rounded-lg text-xs ring-1 ring-gray-100 dark:ring-dark-700 focus:ring-1 focus:ring-blue-500 text-center">
                                                    </div>
                                                </div>
                                                <button type="button"
                                                    wire:click="$set('equipmentDetails', {{ json_encode(collect($equipmentDetails)->forget($idx)->values()->toArray()) }})"
                                                    class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition opacity-0 group-hover:opacity-100">
                                                    <x-heroicon-s-trash class="w-4 h-4" />
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Material -->
                                <div
                                    class="bg-white dark:bg-dark-900 rounded-2xl border dark:border-dark-700 overflow-hidden shadow-sm">
                                    <div
                                        class="px-4 py-3 bg-gray-50 dark:bg-dark-800 border-b dark:border-dark-700 flex justify-between items-center">
                                        <span
                                            class="text-[10px] font-black text-gray-600 dark:text-gray-400 uppercase tracking-widest">Daftar
                                            Material</span>
                                        <button type="button"
                                            wire:click="$set('materialUsageSummary', {{ json_encode(array_merge($materialUsageSummary, [['material' => '', 'qty_used' => 0, 'unit' => '']])) }})"
                                            class="text-[9px] font-black uppercase text-amber-600 hover:text-amber-800 flex items-center">
                                            <x-heroicon-s-plus-circle class="w-3.5 h-3.5 mr-1" /> Tambah
                                        </button>
                                    </div>
                                    <div class="p-4 space-y-3">
                                        @foreach ($materialUsageSummary as $idx => $mat)
                                            <div class="flex items-center space-x-3 group">
                                                <div class="flex-1 grid grid-cols-12 gap-2">
                                                    <div class="col-span-6">
                                                        <input
                                                            wire:model="materialUsageSummary.{{ $idx }}.material"
                                                            type="text" placeholder="Nama Material"
                                                            class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-800 border-none rounded-lg text-xs ring-1 ring-gray-100 dark:ring-dark-700 focus:ring-1 focus:ring-amber-500">
                                                    </div>
                                                    <div class="col-span-3">
                                                        <input
                                                            wire:model="materialUsageSummary.{{ $idx }}.qty_used"
                                                            type="number" step="0.01" placeholder="Qty"
                                                            class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-800 border-none rounded-lg text-xs font-bold ring-1 ring-gray-100 dark:ring-dark-700 focus:ring-1 focus:ring-amber-500 text-center">
                                                    </div>
                                                    <div class="col-span-3">
                                                        <input
                                                            wire:model="materialUsageSummary.{{ $idx }}.unit"
                                                            type="text" placeholder="Unit"
                                                            class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-800 border-none rounded-lg text-xs ring-1 ring-gray-100 dark:ring-dark-700 focus:ring-1 focus:ring-amber-500 text-center">
                                                    </div>
                                                </div>
                                                <button type="button"
                                                    wire:click="$set('materialUsageSummary', {{ json_encode(collect($materialUsageSummary)->forget($idx)->values()->toArray()) }})"
                                                    class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition opacity-0 group-hover:opacity-100">
                                                    <x-heroicon-s-trash class="w-4 h-4" />
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Section: K3 / Safety -->
                            <div x-show="!quickEntry && activeTab === 'safety'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="space-y-6">
                                <h4
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 flex items-center">
                                    <span class="w-4 h-[2px] bg-red-500 mr-2"></span>
                                    🚨 Keselamatan & K3 (WAJIB DILAPORKAN)
                                </h4>
                                <div
                                    class="bg-red-50/50 dark:bg-red-950/10 p-5 rounded-2xl border-2 border-red-200 dark:border-red-900/30 space-y-4">
                                    <!-- Alert Box for Safety Importance -->
                                    <div
                                        class="bg-white dark:bg-dark-900/50 rounded-xl p-3 border-l-4 border-red-500 flex items-start space-x-3">
                                        <x-heroicon-s-exclamation-triangle
                                            class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" />
                                        <div class="flex-1 text-xs text-gray-700 dark:text-gray-300 leading-relaxed">
                                            <strong class="text-red-600 dark:text-red-400">⚠️ Penting:</strong>
                                            Laporkan SEMUA insiden dan near miss, bahkan yang kecil, untuk mencegah
                                            kecelakaan lebih lanjut.
                                        </div>
                                    </div>

                                    <!-- Safety Metrics -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <!-- Incidents -->
                                        <div
                                            class="bg-white dark:bg-dark-900 rounded-xl p-4 border-l-4 border-red-500 space-y-2">
                                            <div class="flex justify-between items-center">
                                                <label
                                                    class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">Insiden
                                                    (Cedera)</label>
                                                <x-heroicon-s-exclamation-triangle class="w-4 h-4 text-red-600" />
                                            </div>
                                            <input wire:model="safetyDetails.incidents" type="number" min="0"
                                                class="block w-full px-4 py-2 bg-red-50 dark:bg-red-900/20 border-none rounded-lg text-xl font-black text-red-600 ring-1 ring-red-200 dark:ring-red-900/30 focus:ring-1 focus:ring-red-500">
                                            <p class="text-[9px] text-gray-500 dark:text-gray-400 italic">Jumlah orang
                                                terluka</p>
                                        </div>

                                        <!-- Near Miss -->
                                        <div
                                            class="bg-white dark:bg-dark-900 rounded-xl p-4 border-l-4 border-amber-500 space-y-2">
                                            <div class="flex justify-between items-center">
                                                <label
                                                    class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">Near
                                                    Miss (Nyaris)</label>
                                                <x-heroicon-s-bolt class="w-4 h-4 text-amber-600" />
                                            </div>
                                            <input wire:model="safetyDetails.near_miss" type="number" min="0"
                                                class="block w-full px-4 py-2 bg-amber-50 dark:bg-amber-900/20 border-none rounded-lg text-xl font-black text-amber-600 ring-1 ring-amber-200 dark:ring-amber-900/30 focus:ring-1 focus:ring-amber-500">
                                            <p class="text-[9px] text-gray-500 dark:text-gray-400 italic">Kejadian
                                                hampir terjadi</p>
                                        </div>

                                        <!-- PPE Compliance -->
                                        <div
                                            class="bg-white dark:bg-dark-900 rounded-xl p-4 border-l-4 border-emerald-500 space-y-2">
                                            <label class="flex items-center justify-between cursor-pointer group">
                                                <span
                                                    class="text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-emerald-600 transition">APD
                                                    Lengkap</span>
                                                <div class="relative inline-flex h-6 w-11 bg-gray-200 rounded-full peer-checked:bg-emerald-600 dark:bg-dark-700 transition-colors"
                                                    x-data="{
                                                        checked: @js($safetyDetails['apd_compliance'] ?? false),
                                                        toggle() { this.checked = !this.checked;
                                                            $wire.set('safetyDetails.apd_compliance', this.checked); }
                                                    }" @click="toggle()">
                                                    <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full transition-transform"
                                                        :style="checked ? 'transform: translateX(24px)' : ''"></div>
                                                </div>
                                            </label>
                                            <p class="text-[9px] text-gray-500 dark:text-gray-400 italic">Semua pekerja
                                                memakai APD standar</p>
                                        </div>
                                    </div>

                                    <!-- Safety Notes -->
                                    <div class="space-y-2">
                                        <label
                                            class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">Catatan
                                            K3 Khusus</label>
                                        <textarea wire:model="safetyDetails.notes" rows="3"
                                            class="block w-full px-4 py-3 bg-white dark:bg-dark-900 border-none rounded-xl text-xs text-gray-700 dark:text-gray-300 ring-1 ring-gray-200 dark:ring-dark-700 focus:ring-2 focus:ring-red-500"
                                            placeholder="Deskripsikan insiden/near miss secara detail, lokasi kejadian, penyebab, dan tindakan pencegahan..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Documentation -->
                            <div x-show="!quickEntry && activeTab === 'photos'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="space-y-6">
                                <h4
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 flex items-center">
                                    <span class="w-4 h-[2px] bg-gold-500 mr-2"></span>
                                    Foto Dokumentasi
                                </h4>

                                <div class="relative group">
                                    <div class="flex items-center justify-center w-full">
                                        <label
                                            class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-200 dark:border-dark-700 rounded-2xl cursor-pointer bg-gray-50 dark:bg-dark-900 hover:bg-gray-100 dark:hover:bg-dark-800 transition-all duration-300">
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <x-heroicon-o-camera class="w-10 h-10 text-gray-400 mb-2" />
                                                <p class="mb-2 text-xs text-gray-500 dark:text-gray-400"><span
                                                        class="font-black text-gold-600">Klik untuk upload</span> atau
                                                    drag and drop</p>
                                                <p class="text-[10px] text-gray-400 uppercase tracking-tighter">PNG,
                                                    JPG up to 5MB (Max 5 foto)</p>
                                            </div>
                                            <input wire:model="photos" type="file" class="hidden" multiple
                                                accept="image/*" />
                                        </label>
                                    </div>

                                    <div wire:loading wire:target="photos"
                                        class="mt-4 p-4 bg-gold-50 dark:bg-gold-900/20 rounded-xl flex items-center justify-center">
                                        <x-heroicon-o-arrow-path class="w-5 h-5 text-gold-600 animate-spin mr-3" />
                                        <span
                                            class="text-xs font-bold text-gold-600 uppercase tracking-widest">Memproses
                                            Foto...</span>
                                    </div>

                                    @if ($photos && count($photos) > 0)
                                        <div class="mt-6 grid grid-cols-5 gap-3">
                                            @foreach ($photos as $photo)
                                                @if (method_exists($photo, 'temporaryUrl'))
                                                    <div
                                                        class="relative aspect-square rounded-xl overflow-hidden border-2 border-gold-500 shadow-md transform hover:scale-105 transition-transform duration-200">
                                                        <img src="{{ $photo->temporaryUrl() }}"
                                                            class="w-full h-full object-cover">
                                                        <div
                                                            class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent flex items-end justify-center pb-1">
                                                            <span
                                                                class="text-[8px] font-black text-white uppercase tracking-tighter">READY</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                        <p
                                            class="mt-2 text-[10px] text-emerald-600 font-black uppercase tracking-widest text-center">
                                            ✓ {{ count($photos) }} FOTO TERPILIH</p>
                                    @endif

                                    <x-input-error :messages="$errors->get('photos')" class="mt-2" />
                                    <x-input-error :messages="$errors->get('photos.*')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Premium Sticky Footer -->
                        <div
                            class="px-6 py-4 bg-gray-50 dark:bg-dark-900 border-t dark:border-dark-700 flex flex-col sm:flex-row-reverse gap-3">
                            <button type="submit" wire:loading.attr="disabled"
                                class="w-full sm:w-auto px-10 py-3 bg-gradient-to-r from-gold-500 to-gold-600 text-white text-xs font-black uppercase tracking-[0.2em] rounded-xl shadow-lg shadow-gold-500/30 hover:shadow-gold-500/50 hover:-translate-y-0.5 transition-all active:scale-95 flex items-center justify-center">
                                <span wire:loading.remove wire:target="save">Simpan Laporan</span>
                                <span wire:loading wire:target="save" class="flex items-center">
                                    <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin mr-2" />
                                    Menyimpan...
                                </span>
                            </button>
                            <button type="button" @click="close()"
                                class="w-full sm:w-auto px-10 py-3 text-xs font-black uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-dark-700 rounded-xl transition-all">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Detail Modal --}}
    @if ($showDetailModal && $selectedReport)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay" aria-modal="true" x-data="{
            modalOpen: false,
            init() {
                setTimeout(() => this.modalOpen = true, 50);
            },
            close() {
                this.modalOpen = false;
                setTimeout(() => $wire.closeDetailModal(), 300);
            }
        }"
            x-init="init()">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity duration-300"
                    x-show="modalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" @click="close()"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border dark:border-dark-700"
                    x-show="modalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <!-- Premium Header -->
                    <div
                        class="relative bg-gradient-to-br from-gold-500 via-gold-600 to-amber-700 px-6 py-8 overflow-hidden">
                        <!-- Decorative background elements -->
                        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-black/10 rounded-full blur-3xl"></div>

                        <div class="relative flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="text-2xl font-extrabold text-white tracking-tight">
                                        {{ $selectedReport->report_date->translatedFormat('d F Y') }}
                                    </h3>
                                    <span
                                        class="px-3 py-1 text-[10px] uppercase tracking-wider rounded-full font-bold border border-white/30 bg-white/20 text-white backdrop-blur-md shadow-sm">
                                        {{ $selectedReport->status_label }}
                                    </span>
                                </div>
                                <div class="flex items-center text-gold-50">
                                    <x-heroicon-s-briefcase class="w-4 h-4 mr-2 opacity-70" />
                                    <span
                                        class="text-sm font-medium">{{ $selectedReport->rabItem->work_name ?? 'Laporan Umum' }}</span>
                                </div>
                            </div>
                            <button @click="close()"
                                class="p-2 text-white/80 hover:text-white hover:bg-white/20 rounded-full transition-all duration-200 shadow-lg group">
                                <x-heroicon-o-x-mark
                                    class="w-6 h-6 transform group-hover:rotate-90 transition-transform duration-300" />
                            </button>
                        </div>
                    </div>

                    <div
                        class="px-6 py-6 max-h-[70vh] overflow-y-auto scrollbar-thin scrollbar-thumb-gold-500 scrollbar-track-transparent">
                        <!-- Modern Stats Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                            <div
                                class="bg-gradient-to-br from-white to-gray-50 dark:from-dark-700 dark:to-dark-800 p-4 rounded-2xl border border-gray-100 dark:border-dark-600 shadow-sm transition-transform hover:scale-[1.02] duration-200">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="p-2 bg-gold-100 dark:bg-gold-900/30 rounded-lg text-gold-600">
                                        <x-heroicon-s-arrow-trending-up class="w-4 h-4" />
                                    </div>
                                    <span
                                        class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Progres</span>
                                </div>
                                <p class="text-2xl font-black text-gray-900 dark:text-white">
                                    {{ number_format($selectedReport->progress_percentage, 1) }}%</p>
                            </div>

                            <div
                                class="bg-gradient-to-br from-white to-gray-50 dark:from-dark-700 dark:to-dark-800 p-4 rounded-2xl border border-gray-100 dark:border-dark-600 shadow-sm transition-transform hover:scale-[1.02] duration-200">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg text-blue-600">
                                        <x-heroicon-s-chart-bar class="w-4 h-4" />
                                    </div>
                                    <span
                                        class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kumulatif</span>
                                </div>
                                <p class="text-2xl font-black text-gray-900 dark:text-white">
                                    {{ number_format($selectedReport->cumulative_progress ?? $selectedReport->progress_percentage, 1) }}%
                                </p>
                            </div>

                            <div
                                class="bg-gradient-to-br from-white to-gray-50 dark:from-dark-700 dark:to-dark-800 p-4 rounded-2xl border border-gray-100 dark:border-dark-600 shadow-sm transition-transform hover:scale-[1.02] duration-200">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="p-2 bg-sky-100 dark:bg-sky-900/30 rounded-lg text-sky-600">
                                        <x-heroicon-s-sun class="w-4 h-4" />
                                    </div>
                                    <span
                                        class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Cuaca</span>
                                </div>
                                <div class="flex items-center">
                                    @php $weatherIcons = ['sunny' => '☀️', 'cloudy' => '☁️', 'rainy' => '🌧️', 'stormy' => '⛈️']; @endphp
                                    <span
                                        class="text-2xl mr-2">{{ $weatherIcons[$selectedReport->weather] ?? '❓' }}</span>
                                    <span
                                        class="text-xs font-bold text-gray-600 dark:text-gray-400 truncate">{{ $weatherOptions[$selectedReport->weather] ?? ucfirst($selectedReport->weather) }}</span>
                                </div>
                            </div>

                            <div
                                class="bg-gradient-to-br from-white to-gray-50 dark:from-dark-700 dark:to-dark-800 p-4 rounded-2xl border border-gray-100 dark:border-dark-600 shadow-sm transition-transform hover:scale-[1.02] duration-200">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg text-purple-600">
                                        <x-heroicon-s-users class="w-4 h-4" />
                                    </div>
                                    <span
                                        class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pekerja</span>
                                </div>
                                <p class="text-2xl font-black text-gray-900 dark:text-white">
                                    {{ $selectedReport->workers_count ?? '0' }}
                                </p>
                            </div>
                        </div>

                        <!-- Main Content Grid -->
                        <div class="space-y-8">
                            <!-- Work Description & Review Notes -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div class="group">
                                        <h4
                                            class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 flex items-center">
                                            <span class="w-4 h-[2px] bg-gold-500 mr-2"></span>
                                            Deskripsi Pekerjaan
                                        </h4>
                                        <div
                                            class="bg-gray-50 dark:bg-dark-900/50 p-4 rounded-xl border border-gray-100 dark:border-dark-700">
                                            <p
                                                class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line italic">
                                                "{{ $selectedReport->description ?: 'Tidak ada deskripsi.' }}"
                                            </p>
                                        </div>
                                    </div>

                                    @if ($selectedReport->issues)
                                        <div
                                            class="p-4 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-100 dark:border-red-900/50">
                                            <h4
                                                class="text-xs font-bold text-red-800 dark:text-red-400 mb-2 flex items-center uppercase tracking-wider">
                                                <x-heroicon-o-exclamation-triangle class="w-4 h-4 mr-2" />
                                                Kendala & Hambatan
                                            </h4>
                                            <p
                                                class="text-red-700 dark:text-red-300 whitespace-pre-line text-sm leading-relaxed">
                                                {{ $selectedReport->issues }}
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                <div class="space-y-4">
                                    @if ($selectedReport->review_notes)
                                        <div
                                            class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-100 dark:border-indigo-900/50">
                                            <h4
                                                class="text-xs font-bold text-indigo-800 dark:text-indigo-400 mb-2 flex items-center uppercase tracking-wider">
                                                <x-heroicon-o-chat-bubble-bottom-center-text class="w-4 h-4 mr-2" />
                                                Catatan Review
                                            </h4>
                                            <p
                                                class="text-indigo-700 dark:text-indigo-300 whitespace-pre-line text-sm leading-relaxed italic">
                                                "{{ $selectedReport->review_notes }}"
                                            </p>
                                            @if ($selectedReport->reviewed_by)
                                                <div
                                                    class="mt-4 pt-3 border-t border-indigo-200/50 dark:border-indigo-800/50 flex items-center">
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-[10px] font-bold mr-2">
                                                        {{ strtoupper(substr($selectedReport->reviewer->name ?? 'A', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <p
                                                            class="text-[10px] font-bold text-indigo-900 dark:text-indigo-200 uppercase">
                                                            {{ $selectedReport->reviewer->name ?? 'Reviewer' }}</p>
                                                        <p class="text-[10px] text-indigo-500">
                                                            {{ $selectedReport->reviewed_at?->translatedFormat('d M Y, H:i') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div
                                            class="h-full flex flex-col items-center justify-center p-8 bg-gray-50 dark:bg-dark-900/30 rounded-xl border border-dashed border-gray-200 dark:border-dark-700">
                                            <x-heroicon-o-shield-check class="w-12 h-12 text-gray-300 mb-3" />
                                            <p class="text-xs text-gray-400 font-medium italic">Belum ada tinjauan
                                                manajemen.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Workflow Timeline -->
                            <div>
                                <h4
                                    class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 flex items-center">
                                    <span class="w-4 h-[2px] bg-violet-500 mr-2"></span>
                                    Timeline Workflow
                                </h4>
                                <div class="bg-white dark:bg-dark-900 rounded-xl border dark:border-dark-700 p-4">
                                    <div class="space-y-4">
                                        <div class="flex items-start gap-3">
                                            <div class="mt-1 w-2.5 h-2.5 rounded-full bg-gray-500"></div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Draft
                                                    Dibuat</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $selectedReport->reporter->name ?? 'Unknown' }} •
                                                    {{ $selectedReport->created_at?->translatedFormat('d M Y, H:i') }}
                                                </p>
                                            </div>
                                        </div>

                                        @if ($selectedReport->status !== 'draft')
                                            <div class="flex items-start gap-3">
                                                <div class="mt-1 w-2.5 h-2.5 rounded-full bg-blue-500"></div>
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                        Diajukan</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ $selectedReport->reporter->name ?? 'Unknown' }} •
                                                        {{ $selectedReport->updated_at?->translatedFormat('d M Y, H:i') }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($selectedReport->status === 'reviewed' || $selectedReport->status === 'published')
                                            <div class="flex items-start gap-3">
                                                <div class="mt-1 w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                        Diverifikasi</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ $selectedReport->reviewer->name ?? 'Reviewer' }} •
                                                        {{ $selectedReport->reviewed_at?->translatedFormat('d M Y, H:i') }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($selectedReport->status === 'rejected')
                                            <div class="flex items-start gap-3">
                                                <div class="mt-1 w-2.5 h-2.5 rounded-full bg-red-500"></div>
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                        Ditolak</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ $selectedReport->rejector->name ?? 'Reviewer' }} •
                                                        {{ $selectedReport->rejected_at?->translatedFormat('d M Y, H:i') }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($selectedReport->status === 'published')
                                            <div class="flex items-start gap-3">
                                                <div class="mt-1 w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                        Dipublikasikan</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ $selectedReport->publisher->name ?? 'Publisher' }} •
                                                        {{ $selectedReport->published_at?->translatedFormat('d M Y, H:i') }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Resources & Safety -->
                            <div class="grid grid-cols-1 gap-8">
                                {{-- Combined Equipment & Material --}}
                                @if ($selectedReport->has_equipment || $selectedReport->has_material_usage)
                                    <div>
                                        <h4
                                            class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 flex items-center">
                                            <span class="w-4 h-[2px] bg-blue-500 mr-2"></span>
                                            Penggunaan Sumber Daya
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            {{-- Equipment --}}
                                            @if ($selectedReport->has_equipment)
                                                <div
                                                    class="bg-white dark:bg-dark-900 rounded-xl border dark:border-dark-700 overflow-hidden shadow-sm">
                                                    <div
                                                        class="bg-gray-50 dark:bg-dark-800 px-4 py-2 border-b dark:border-dark-700">
                                                        <span
                                                            class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest">Peralatan</span>
                                                    </div>
                                                    <div class="p-0">
                                                        <table class="min-w-full text-xs">
                                                            <tbody
                                                                class="divide-y divide-gray-100 dark:divide-dark-800">
                                                                @foreach ($selectedReport->equipment_details as $eq)
                                                                    <tr
                                                                        class="hover:bg-gray-50 dark:hover:bg-dark-800/50 transition-colors">
                                                                        <td
                                                                            class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                                                                            {{ $eq['name'] }}</td>
                                                                        <td
                                                                            class="px-4 py-2.5 text-center text-gray-500">
                                                                            {{ $eq['qty'] }} Unit</td>
                                                                        <td class="px-4 py-2.5 text-right">
                                                                            <span
                                                                                class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-tighter
                                                                                {{ ($eq['condition'] ?? '') === 'baik' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                                                                {{ $eq['condition'] ?? 'Baik' }}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Material --}}
                                            @if ($selectedReport->has_material_usage)
                                                <div
                                                    class="bg-white dark:bg-dark-900 rounded-xl border dark:border-dark-700 overflow-hidden shadow-sm">
                                                    <div
                                                        class="bg-gray-50 dark:bg-dark-800 px-4 py-2 border-b dark:border-dark-700">
                                                        <span
                                                            class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest">Material</span>
                                                    </div>
                                                    <div class="p-0">
                                                        <table class="min-w-full text-xs">
                                                            <tbody
                                                                class="divide-y divide-gray-100 dark:divide-dark-800">
                                                                @foreach ($selectedReport->material_usage_summary as $mat)
                                                                    <tr
                                                                        class="hover:bg-gray-50 dark:hover:bg-dark-800/50 transition-colors">
                                                                        <td
                                                                            class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                                                                            {{ $mat['material'] }}</td>
                                                                        <td
                                                                            class="px-4 py-2.5 text-right font-black text-gold-600">
                                                                            {{ number_format($mat['qty_used'], 1) }}
                                                                            <span
                                                                                class="text-[10px] text-gray-400 ml-0.5">{{ $mat['unit'] }}</span>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Safety and Future Plan --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {{-- K3 / Safety --}}
                                    @if ($selectedReport->has_safety_data)
                                        <div
                                            class="p-5 bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/20 dark:to-teal-950/20 rounded-2xl border border-emerald-100 dark:border-emerald-900/50">
                                            <h4
                                                class="text-xs font-black text-emerald-800 dark:text-emerald-400 mb-4 flex items-center uppercase tracking-[0.2em]">
                                                <x-heroicon-o-shield-check class="w-5 h-5 mr-2 text-emerald-600" />
                                                Keselamatan Kerja (K3)
                                            </h4>
                                            <div class="flex items-center justify-between">
                                                <div class="text-center px-2">
                                                    <p
                                                        class="text-[9px] text-emerald-600/70 uppercase font-black mb-1">
                                                        Insiden</p>
                                                    <p
                                                        class="text-xl font-black {{ ($selectedReport->safety_details['incidents'] ?? 0) > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                                        {{ $selectedReport->safety_details['incidents'] ?? 0 }}
                                                    </p>
                                                </div>
                                                <div class="w-[1px] h-8 bg-emerald-200 dark:bg-emerald-800"></div>
                                                <div class="text-center px-2">
                                                    <p
                                                        class="text-[9px] text-emerald-600/70 uppercase font-black mb-1">
                                                        Near Miss</p>
                                                    <p
                                                        class="text-xl font-black {{ ($selectedReport->safety_details['near_miss'] ?? 0) > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                                                        {{ $selectedReport->safety_details['near_miss'] ?? 0 }}
                                                    </p>
                                                </div>
                                                <div class="w-[1px] h-8 bg-emerald-200 dark:bg-emerald-800"></div>
                                                <div class="text-center px-2">
                                                    <p
                                                        class="text-[9px] text-emerald-600/70 uppercase font-black mb-1">
                                                        Status APD</p>
                                                    <span
                                                        class="px-2 py-0.5 rounded text-[10px] font-black uppercase
                                                        {{ $selectedReport->safety_details['apd_compliance'] ?? false ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white' }}">
                                                        {{ $selectedReport->safety_details['apd_compliance'] ?? false ? 'Lengkap' : 'Pelanggaran' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Next Day Plan --}}
                                    @if ($selectedReport->next_day_plan)
                                        <div
                                            class="p-5 bg-gradient-to-br from-sky-50 to-blue-50 dark:from-sky-950/20 dark:to-blue-950/20 rounded-2xl border border-sky-100 dark:border-sky-900/50">
                                            <h4
                                                class="text-xs font-black text-sky-800 dark:text-sky-400 mb-3 flex items-center uppercase tracking-[0.2em]">
                                                <x-heroicon-o-calendar-days class="w-5 h-5 mr-2 text-sky-600" />
                                                Rencana Kerja Esok
                                            </h4>
                                            <p
                                                class="text-sm text-sky-700 dark:text-sky-300 whitespace-pre-line leading-relaxed italic">
                                                "{{ $selectedReport->next_day_plan }}"
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Photo Documentation Gallery -->
                            @php $photoUrls = $selectedReport->precomputed_photo_urls ?? $selectedReport->photo_urls ?? []; @endphp
                            @if (count($photoUrls) > 0)
                                <div class="group" x-data="{
                                    lightboxOpen: false,
                                    currentIndex: 0,
                                    photos: {{ json_encode($photoUrls) }},
                                    open(index) {
                                        this.currentIndex = index;
                                        this.lightboxOpen = true;
                                    },
                                    close() { this.lightboxOpen = false; },
                                    next() { this.currentIndex = (this.currentIndex + 1) % this.photos.length; },
                                    prev() { this.currentIndex = (this.currentIndex - 1 + this.photos.length) % this.photos.length; }
                                }" @keydown.escape.window="close()"
                                    @keydown.arrow-right.window="if(lightboxOpen) next()"
                                    @keydown.arrow-left.window="if(lightboxOpen) prev()">

                                    <h4
                                        class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 flex items-center">
                                        <span class="w-4 h-[2px] bg-amber-500 mr-2"></span>
                                        Dokumentasi Lapangan
                                    </h4>

                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                        @foreach ($photoUrls as $index => $photoUrl)
                                            <div class="relative aspect-square rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group/item cursor-pointer border dark:border-dark-700"
                                                @click="open({{ $index }})">
                                                <img src="{{ $photoUrl }}"
                                                    class="w-full h-full object-cover transform group-hover/item:scale-110 transition-transform duration-500">
                                                <div
                                                    class="absolute inset-0 bg-black/0 group-hover/item:bg-black/20 transition-all duration-300 flex items-center justify-center">
                                                    <x-heroicon-s-magnifying-glass-plus
                                                        class="w-8 h-8 text-white opacity-0 group-hover/item:opacity-100 transform scale-50 group-hover/item:scale-100 transition-all duration-300" />
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Improved Lightbox -->
                                    <div x-show="lightboxOpen" x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-200"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/95 backdrop-blur-xl p-4"
                                        @click.self="close()" style="display: none;">

                                        <button @click="close()"
                                            class="absolute top-6 right-6 p-2 text-white/50 hover:text-white bg-white/10 hover:bg-white/20 rounded-full transition-all duration-300 z-[110]">
                                            <x-heroicon-o-x-mark class="w-8 h-8" />
                                        </button>

                                        <button @click="prev()" x-show="photos.length > 1"
                                            class="absolute left-6 p-4 text-white/50 hover:text-white bg-white/5 hover:bg-white/10 rounded-full transition-all z-[110]">
                                            <x-heroicon-o-chevron-left class="w-10 h-10" />
                                        </button>

                                        <div
                                            class="relative max-w-5xl w-full flex items-center justify-center group/lb">
                                            <img :src="photos[currentIndex]"
                                                class="max-w-full max-h-[85vh] object-contain rounded-2xl shadow-[0_0_50px_rgba(0,0,0,0.5)] border border-white/10">

                                            <!-- Info Overlay -->
                                            <div
                                                class="absolute bottom-6 left-1/2 -translate-x-1/2 px-6 py-3 bg-black/40 backdrop-blur-md rounded-full border border-white/10 text-white/90 text-sm font-bold flex items-center space-x-4">
                                                <span x-text="(currentIndex + 1) + ' / ' + photos.length"></span>
                                                <div class="w-[1px] h-4 bg-white/20"></div>
                                                <span class="text-xs tracking-wider opacity-70">DOKUMENTASI
                                                    PROYEK</span>
                                            </div>
                                        </div>

                                        <button @click="next()" x-show="photos.length > 1"
                                            class="absolute right-6 p-4 text-white/50 hover:text-white bg-white/5 hover:bg-white/10 rounded-full transition-all z-[110]">
                                            <x-heroicon-o-chevron-right class="w-10 h-10" />
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Modern Footer -->
                    <div
                        class="bg-gray-50 dark:bg-dark-900 px-6 py-4 border-t dark:border-dark-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center space-x-3 text-xs text-gray-500">
                            <div
                                class="w-8 h-8 rounded-full bg-gold-100 dark:bg-gold-900/30 flex items-center justify-center text-gold-600 font-black">
                                {{ strtoupper(substr($selectedReport->reporter->name ?? '?', 0, 1)) }}
                            </div>
                            <p>Oleh <span
                                    class="font-black text-gray-900 dark:text-white tracking-tight">{{ $selectedReport->reporter->name ?? 'Unknown' }}</span>
                                • {{ $selectedReport->created_at->translatedFormat('d M Y, H:i') }}</p>
                        </div>
                        <div class="flex items-center space-x-3 w-full sm:w-auto">
                            @if ($selectedReport->status === 'submitted' && auth()->user()->can('progress.approve'))
                                <button type="button"
                                    wire:click="openReviewModal({{ $selectedReport->id }}, 'approve')"
                                    class="w-full sm:w-auto px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold uppercase tracking-wider">
                                    Approve
                                </button>
                                <button type="button"
                                    wire:click="openReviewModal({{ $selectedReport->id }}, 'reject')"
                                    class="w-full sm:w-auto px-4 py-2 bg-red-600 text-white rounded-lg text-xs font-bold uppercase tracking-wider">
                                    Reject
                                </button>
                            @endif
                            @if ($selectedReport->status === 'draft' && auth()->user()->can('progress.manage'))
                                <button type="button" wire:click="submitReport({{ $selectedReport->id }})"
                                    class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg text-xs font-bold uppercase tracking-wider">
                                    Submit
                                </button>
                            @endif
                            @if ($selectedReport->status === 'reviewed' && auth()->user()->can('progress.publish'))
                                <button type="button" wire:click="publishReport({{ $selectedReport->id }})"
                                    class="w-full sm:w-auto px-4 py-2 bg-amber-600 text-white rounded-lg text-xs font-bold uppercase tracking-wider">
                                    Publish
                                </button>
                            @endif
                            <button @click="close()"
                                class="w-full sm:w-auto px-8 py-2.5 text-xs font-black uppercase tracking-[0.2em] text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-dark-700 rounded-xl transition-all">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showReviewModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeReviewModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div
                    class="relative inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-6 py-4 border-b dark:border-dark-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $reviewAction === 'approve' ? 'Approve Report' : 'Reject Report' }}
                        </h3>
                    </div>
                    <div class="p-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catatan
                            Reviewer</label>
                        <textarea wire:model.defer="reviewNotes" rows="4"
                            class="w-full rounded-lg border-gray-300 dark:border-dark-600 dark:bg-dark-900 dark:text-white"
                            placeholder="Tambahkan catatan (opsional)"></textarea>
                    </div>
                    <div class="bg-gray-50 dark:bg-dark-900 px-6 py-3 flex justify-end gap-3">
                        <button type="button" wire:click="closeReviewModal"
                            class="px-4 py-2 rounded-lg border dark:border-dark-600 text-sm">Batal</button>
                        <button type="button" wire:click="processReview"
                            class="px-4 py-2 rounded-lg text-white text-sm {{ $reviewAction === 'approve' ? 'bg-emerald-600' : 'bg-red-600' }}">
                            {{ $reviewAction === 'approve' ? 'Approve' : 'Reject' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeDeleteModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div
                    class="relative inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" wire:click="closeDeleteModal"
                            class="text-gray-400 hover:text-gray-500">
                            <x-heroicon-o-x-circle class="w-6 h-6" />
                        </button>
                    </div>
                    <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Hapus Laporan Progress
                                </h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Yakin ingin menghapus laporan
                                    ini?
                                    Foto yang terkait juga akan dihapus.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-dark-700 px-3 py-1.5 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="delete"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">Hapus</button>
                        <button wire:click="closeDeleteModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600">Batal</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
