<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Weekly Reports', 'url' => route('projects.weekly-reports.index', $project)],
        ['label' => 'Week ' . $report->week_number]
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $report->cover_title ?? 'Weekly Report Week ' . $report->week_number }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $report->period_label }}</p>
            </div>
            <div x-data="{}" class="flex items-center space-x-2">
                @can('weekly_report.manage')
                <form action="{{ route('projects.weekly-reports.regenerate', [$project, $report]) }}" method="POST"
                    class="inline">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Regenerate Data
                    </button>
                </form>
                @endcan
                
                @if($report->status === 'draft')
                @can('weekly_report.publish')
                <form action="{{ route('projects.weekly-reports.publish', [$project, $report]) }}" method="POST"
                    class="inline">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Publish ke Owner
                    </button>
                </form>
                @endcan
                @endif

                <a href="{{ route('projects.weekly-reports.pdf', [$project, $report]) }}"
                    class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export PDF
                </a>

                @can('weekly_report.manage')
                <a href="{{ route('projects.weekly-reports.edit', [$project, $report]) }}"
                    class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
                @endcan

                <button @click="window.dispatchEvent(new CustomEvent('open-discussion'))"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    Diskusi
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-4" x-data="{}">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div
                    class="p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Section 1: Cover -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-3">
                <h3 class="text-[10px] font-black uppercase text-gray-400 dark:text-gray-500 mb-3 flex items-center">
                    <span
                        class="inline-flex items-center justify-center w-5 h-5 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded-full mr-2 text-[10px] font-bold">1</span>
                    Cover
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Judul:</span>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $report->cover_title }}
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Week Number:</span>
                                <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                                    {{ $report->week_number }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Periode:</span>
                                <p class="text-gray-900 dark:text-white">{{ $report->period_label }}</p>
                            </div>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Lokasi:</span>
                            <p class="text-gray-900 dark:text-white">{{ $project->location ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Nama Proyek:</span>
                            <p class="text-gray-900 dark:text-white">{{ $project->name }}</p>
                        </div>
                    </div>

                    @if($report->cover_image_url)
                        <div class="aspect-video rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                            <img src="{{ $report->cover_image_url }}" alt="Cover Image" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div
                            class="aspect-video rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            <div class="text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-2">No cover image</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Section 2: Cumulative Progress -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-3"
                x-data="cumulativeEditor()" x-cloak>
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-[10px] font-black uppercase text-gray-400 dark:text-gray-500 flex items-center">
                        <span
                            class="inline-flex items-center justify-center w-5 h-5 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded-full mr-2 text-[10px] font-bold">2</span>
                        Kumulatif Progress
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span x-show="saveStatus === 'success'" x-transition
                            class="text-sm text-green-600 dark:text-green-400 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span x-text="saveMessage"></span>
                        </span>
                        <span x-show="saveStatus === 'error'" x-transition
                            class="text-sm text-red-600 dark:text-red-400">
                            Gagal menyimpan!
                        </span>
                        <button x-show="hasChanges" x-transition @click="save()" :disabled="saving"
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg x-show="!saving" class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <svg x-show="saving" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="saving ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-dark-700">
                            <tr>
                                <th rowspan="2"
                                    class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase border dark:border-gray-600">
                                    Item Pekerjaan</th>
                                <th rowspan="2"
                                    class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase border dark:border-gray-600">
                                    Bobot (%)</th>
                                <th colspan="3"
                                    class="px-3 py-2 text-center text-xs font-medium text-blue-700 dark:text-blue-100 uppercase border dark:border-gray-600 bg-blue-100 dark:bg-blue-800">
                                    Rencana (%)</th>
                                <th colspan="3"
                                    class="px-3 py-2 text-center text-xs font-medium text-green-700 dark:text-green-100 uppercase border dark:border-gray-600 bg-green-100 dark:bg-green-800">
                                    Realisasi (%)</th>
                                <th colspan="3"
                                    class="px-3 py-2 text-center text-xs font-medium text-yellow-700 dark:text-yellow-100 uppercase border dark:border-gray-600 bg-yellow-100 dark:bg-yellow-800">
                                    Deviasi (%)</th>
                            </tr>
                            <tr>
                                <th
                                    class="px-2 py-1 text-center text-xs font-medium text-blue-700 dark:text-blue-100 border dark:border-gray-600 bg-blue-100 dark:bg-blue-800">
                                    s.d. Lalu</th>
                                <th
                                    class="px-2 py-1 text-center text-xs font-medium text-blue-700 dark:text-blue-100 border dark:border-gray-600 bg-blue-100 dark:bg-blue-800">
                                    Minggu Ini</th>
                                <th
                                    class="px-2 py-1 text-center text-xs font-medium text-blue-700 dark:text-blue-100 border dark:border-gray-600 bg-blue-100 dark:bg-blue-800">
                                    Kum.</th>
                                <th
                                    class="px-2 py-1 text-center text-xs font-medium text-green-700 dark:text-green-100 border dark:border-gray-600 bg-green-100 dark:bg-green-800">
                                    s.d. Lalu</th>
                                <th
                                    class="px-2 py-1 text-center text-xs font-medium text-green-700 dark:text-green-100 border dark:border-gray-600 bg-green-100 dark:bg-green-800">
                                    Minggu Ini</th>
                                <th
                                    class="px-2 py-1 text-center text-xs font-medium text-green-700 dark:text-green-100 border dark:border-gray-600 bg-green-100 dark:bg-green-800">
                                    Kum.</th>
                                <th
                                    class="px-2 py-1 text-center text-xs font-medium text-yellow-700 dark:text-yellow-100 border dark:border-gray-600 bg-yellow-100 dark:bg-yellow-800">
                                    s.d. Lalu</th>
                                <th
                                    class="px-2 py-1 text-center text-xs font-medium text-yellow-700 dark:text-yellow-100 border dark:border-gray-600 bg-yellow-100 dark:bg-yellow-800">
                                    Minggu Ini</th>
                                <th
                                    class="px-2 py-1 text-center text-xs font-medium text-yellow-700 dark:text-yellow-100 border dark:border-gray-600 bg-yellow-100 dark:bg-yellow-800">
                                    Kum.</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-dark-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="row in allRows" :key="row.key">
                                <tr
                                    :class="row.type === 'header' ? 'bg-gray-50 dark:bg-dark-700 font-semibold' : 'hover:bg-gray-50 dark:hover:bg-dark-700'">
                                    <!-- Header row: colspan 11 -->
                                    <template x-if="row.type === 'header'">
                                        <td colspan="11" class="px-2 py-1.5 border text-gray-900 dark:text-white text-xs font-bold"
                                            x-html="row.indent + row.label"></td>
                                    </template>
                                    <!-- Item row: individual cells -->
                                    <template x-if="row.type === 'item'">
                                        <td class="px-3 py-1 border text-xs text-gray-900 dark:text-gray-200"
                                            x-html="row.indent + '&nbsp;&nbsp;&nbsp;&nbsp;' + row.item.code + ' ' + row.item.work_name">
                                        </td>
                                    </template>
                                    <template x-if="row.type === 'item'">
                                        <td class="px-2 py-1 text-center border text-xs text-gray-900 dark:text-gray-200"
                                            x-text="fmt(row.item.weight)"></td>
                                    </template>
                                    <!-- Planned -->
                                    <template x-if="row.type === 'item'">
                                        <td class="px-2 py-1 text-center border dark:border-gray-600 text-xs text-blue-700 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/50"
                                            x-text="fmt(row.item.planned.up_to_prev)"></td>
                                    </template>
                                    <template x-if="row.type === 'item'">
                                        <td class="px-2 py-1 text-center border dark:border-gray-600 text-xs text-blue-700 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/50"
                                            x-text="fmt(row.item.planned.current)"></td>
                                    </template>
                                    <template x-if="row.type === 'item'">
                                        <td class="px-2 py-1 text-center border dark:border-gray-600 text-xs text-blue-700 dark:text-blue-200 bg-blue-50 dark:bg-blue-900/50"
                                            x-text="fmt(row.item.planned.cumulative)"></td>
                                    </template>
                                    <!-- Actual -->
                                    <template x-if="row.type === 'item'">
                                        <td class="px-2 py-1 text-center border dark:border-gray-600 text-xs text-green-700 dark:text-green-200 bg-green-50 dark:bg-green-900/50"
                                            x-text="fmt(row.item.actual.up_to_prev)"></td>
                                    </template>
                                    <template x-if="row.type === 'item'">
                                        <td
                                            class="px-2 py-1 text-center border dark:border-gray-600 text-xs text-green-700 dark:text-green-200 bg-green-50 dark:bg-green-900/50 p-0">
                                            <input type="number" step="0.0001" min="0"
                                                :disabled="!canManage"
                                                class="w-full text-center text-xs bg-green-100 dark:bg-green-900 border-0 focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 py-1 px-1 text-green-800 dark:text-green-100 font-medium disabled:opacity-75 disabled:cursor-not-allowed"
                                                :value="row.item.actual.current"
                                                @input="updateActualCurrent(row.item, parseFloat($event.target.value) || 0)">
                                        </td>
                                    </template>
                                    <template x-if="row.type === 'item'">
                                        <td class="px-2 py-1 text-center border dark:border-gray-600 text-xs text-green-700 dark:text-green-200 bg-green-50 dark:bg-green-900/50"
                                            x-text="fmt(row.item.actual.cumulative)"></td>
                                    </template>
                                    <!-- Deviation -->
                                    <template x-if="row.type === 'item'">
                                        <td class="px-2 py-1 text-center border dark:border-gray-600 text-xs bg-yellow-50 dark:bg-yellow-900/50"
                                            :class="row.item.deviation.up_to_prev >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                            x-text="fmt(row.item.deviation.up_to_prev)"></td>
                                    </template>
                                    <template x-if="row.type === 'item'">
                                        <td class="px-2 py-1 text-center border dark:border-gray-600 text-xs bg-yellow-50 dark:bg-yellow-900/50"
                                            :class="row.item.deviation.current >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                            x-text="fmt(row.item.deviation.current)"></td>
                                    </template>
                                    <template x-if="row.type === 'item'">
                                        <td class="px-2 py-1 text-center border dark:border-gray-600 text-xs bg-yellow-50 dark:bg-yellow-900/50"
                                            :class="row.item.deviation.cumulative >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                            x-text="fmt(row.item.deviation.cumulative)"></td>
                                    </template>
                                </tr>
                            </template>

                            <!-- Totals Row -->
                            <template x-if="data && data.totals">
                                <tr class="bg-gray-100 dark:bg-dark-600 font-bold">
                                    <td class="px-3 py-2 border">TOTAL</td>
                                    <td class="px-3 py-2 text-center border" x-text="fmt(data.totals.weight)"></td>
                                    <td class="px-3 py-2 text-center border bg-blue-50 dark:bg-blue-900"
                                        x-text="fmt(data.totals.planned_prev)"></td>
                                    <td class="px-3 py-2 text-center border bg-blue-50 dark:bg-blue-900"
                                        x-text="fmt(data.totals.planned_current)"></td>
                                    <td class="px-3 py-2 text-center border bg-blue-50 dark:bg-blue-900"
                                        x-text="fmt(data.totals.planned_cumulative)"></td>
                                    <td class="px-3 py-2 text-center border bg-green-50 dark:bg-green-900"
                                        x-text="fmt(data.totals.actual_prev)"></td>
                                    <td class="px-3 py-2 text-center border bg-green-50 dark:bg-green-900"
                                        x-text="fmt(data.totals.actual_current)"></td>
                                    <td class="px-3 py-2 text-center border bg-green-50 dark:bg-green-900"
                                        x-text="fmt(data.totals.actual_cumulative)"></td>
                                    <td class="px-3 py-2 text-center border bg-yellow-50 dark:bg-yellow-900"
                                        :class="data.totals.deviation_prev >= 0 ? 'text-green-600' : 'text-red-600'"
                                        x-text="fmt(data.totals.deviation_prev)"></td>
                                    <td class="px-3 py-2 text-center border bg-yellow-50 dark:bg-yellow-900"
                                        :class="data.totals.deviation_current >= 0 ? 'text-green-600' : 'text-red-600'"
                                        x-text="fmt(data.totals.deviation_current)"></td>
                                    <td class="px-3 py-2 text-center border bg-yellow-50 dark:bg-yellow-900"
                                        :class="data.totals.deviation_cumulative >= 0 ? 'text-green-600' : 'text-red-600'"
                                        x-text="fmt(data.totals.deviation_cumulative)"></td>
                                </tr>
                            </template>

                            <template x-if="!data || !data.sections">
                                <tr>
                                    <td colspan="11" class="px-3 py-1.5 text-center text-gray-500 dark:text-gray-400">Data
                                        progress tidak tersedia.</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section 3: Detail Progress -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-3">
                <h3 class="text-[10px] font-black uppercase text-gray-400 dark:text-gray-500 mb-3 flex items-center">
                    <span
                        class="inline-flex items-center justify-center w-5 h-5 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded-full mr-2 text-[10px] font-bold">3</span>
                    Detail Progress
                </h3>

                @if($report->detail_data && count($report->detail_data) > 0)
                    <div class="space-y-4">
                        @foreach($report->detail_data as $detail)
                            <div class="border dark:border-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <span
                                            class="text-sm text-gray-500 dark:text-gray-400">{{ $detail['date_label'] }}</span>
                                        @if($detail['rab_item'])
                                            <p class="font-medium text-gray-900 dark:text-white">
                                                {{ $detail['rab_item']['code'] }} - {{ $detail['rab_item']['name'] }}
                                            </p>
                                        @endif
                                    </div>
                                    <span
                                        class="text-lg font-bold text-primary-600">{{ $detail['progress_percentage'] }}%</span>
                                </div>
                                @if($detail['description'])
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">{{ $detail['description'] }}</p>
                                @endif
                                <div class="flex flex-wrap gap-4 text-xs text-gray-500 dark:text-gray-400">
                                    <span>Cuaca: {{ $detail['weather'] }}</span>
                                    <span>Pekerja: {{ $detail['workers_count'] ?? '-' }}</span>
                                    <span>Oleh: {{ $detail['reporter'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Tidak ada laporan progress dalam periode
                        ini.</p>
                @endif
            </div>

            <!-- Section 4: Project Documentations -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-3">
                <h3 class="text-[10px] font-black uppercase text-gray-400 dark:text-gray-500 mb-3 flex items-center">
                    <span
                        class="inline-flex items-center justify-center w-5 h-5 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded-full mr-2 text-[10px] font-bold">4</span>
                    Project Documentations
                </h3>

                @php $docs = $report->documentation_files; @endphp
                @if(count($docs) > 0)
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($docs as $doc)
                            @if($doc['url'])
                                <div class="relative group aspect-square rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                                    <img src="{{ $doc['url'] }}" alt="{{ $doc['name'] }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center space-x-2">
                                        <button @click="window.dispatchEvent(new CustomEvent('open-discussion', { detail: { target: 'photo', id: '{{ $doc['id'] }}', url: '{{ $doc['url'] }}' } }))"
                                            class="p-2 bg-white/20 hover:bg-white/40 rounded-full text-white backdrop-blur-sm transition-all transform hover:scale-110"
                                            title="Komentari foto ini">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Tidak ada dokumentasi yang dipilih.</p>
                @endif
            </div>

            <!-- Section 5: Activity and Problems -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-3">
                <h3 class="text-[10px] font-black uppercase text-gray-400 dark:text-gray-500 mb-3 flex items-center">
                    <span
                        class="inline-flex items-center justify-center w-5 h-5 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded-full mr-2 text-[10px] font-bold">5</span>
                    Activity and Problems
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white mb-2">Aktivitas Minggu Ini</h4>
                        <div
                            class="p-4 bg-gray-50 dark:bg-dark-700 rounded-lg min-h-[100px] text-gray-700 dark:text-gray-300">
                            {!! nl2br(e($report->activities ?? 'Tidak ada aktivitas yang dicatat.')) !!}
                        </div>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white mb-2">Kendala / Masalah</h4>
                        <div
                            class="p-4 bg-gray-50 dark:bg-dark-700 rounded-lg min-h-[100px] text-gray-700 dark:text-gray-300">
                            {!! nl2br(e($report->problems ?? 'Tidak ada kendala yang dilaporkan.')) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Discussion Side Panel -->
    <div x-data="discussionPanel()" 
         @open-discussion.window="open($event.detail)"
         x-show="isOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-hidden" 
         aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 overflow-hidden">
            <!-- Background overlay -->
            <div x-show="isOpen" 
                 x-transition:enter="ease-in-out duration-500" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in-out duration-500" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 @click="isOpen = false"
                 class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div x-show="isOpen" 
                     x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" 
                     x-transition:enter-start="translate-x-full" 
                     x-transition:enter-end="translate-x-0" 
                     x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" 
                     x-transition:leave-start="translate-x-0" 
                     x-transition:leave-end="translate-x-full" 
                     class="pointer-events-auto w-screen max-w-md">
                    <div class="flex h-full flex-col overflow-y-scroll bg-white dark:bg-dark-800 shadow-xl">
                        <div class="px-4 py-6 sm:px-6 bg-indigo-600 text-white">
                            <div class="flex items-start justify-between">
                                <h2 class="text-lg font-bold" id="slide-over-title">Diskusi Laporan</h2>
                                <div class="ml-3 flex h-7 items-center">
                                    <button @click="isOpen = false" type="button" class="rounded-md bg-indigo-600 text-indigo-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-white">
                                        <span class="sr-only">Close panel</span>
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-indigo-100">Diskusikan progres dan dokumentasi minggu ini secara real-time.</p>
                        </div>
                        
                        <div class="relative flex-1 flex flex-col min-h-0">
                            <!-- Target Context (if any) -->
                            <div x-show="currentTarget" x-transition class="p-4 bg-gray-50 dark:bg-dark-700 border-b dark:border-gray-600">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Menanggapi Foto:</span>
                                    <button @click="currentTarget = null" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Hapus Konteks</button>
                                </div>
                                <div class="mt-2 flex items-center">
                                    <a :href="currentTarget?.url" target="_blank" class="block hover:opacity-75 transition-opacity">
                                        <img :src="currentTarget?.url" class="h-16 w-16 object-cover rounded border dark:border-gray-600 shadow-sm">
                                    </a>
                                    <div class="ml-3">
                                        <p class="text-xs text-gray-600 dark:text-gray-300 italic" x-text="currentTarget?.id"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Comments List -->
                            <div class="flex-1 overflow-y-auto p-4 space-y-4" id="comments-container">
                                <template x-for="comment in comments" :key="comment.id">
                                    <div class="flex flex-col" :class="comment.user?.id === {{ auth()->id() }} ? 'items-end' : 'items-start'">
                                        <div class="flex items-center mb-1 space-x-2" :class="comment.user?.id === {{ auth()->id() }} ? 'flex-row-reverse space-x-reverse' : ''">
                                            <span class="text-xs font-bold text-gray-900 dark:text-gray-200" x-text="comment.user?.name"></span>
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400" x-text="comment.created_at_human"></span>
                                        </div>
                                        
                                        <!-- Context Badge & Preview -->
                                        <div x-show="comment.metadata?.target === 'photo'" class="mb-2 w-full">
                                            <a :href="comment.metadata?.url" target="_blank" class="block group relative">
                                                <img :src="comment.metadata?.url" 
                                                     class="h-20 w-20 object-cover rounded-lg border-2 border-indigo-100 dark:border-indigo-900 shadow-sm group-hover:opacity-75 transition-opacity">
                                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <svg class="w-6 h-6 text-white drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                    </svg>
                                                </div>
                                                <span class="mt-1 block text-[8px] text-indigo-600 dark:text-indigo-400 font-bold uppercase">Lihat Foto Konteks</span>
                                            </a>
                                        </div>

                                        <div class="max-w-[85%] px-4 py-2 rounded-2xl text-sm shadow-sm"
                                             :class="comment.user?.id === {{ auth()->id() }} 
                                                ? 'bg-indigo-600 text-white rounded-tr-none' 
                                                : 'bg-gray-100 dark:bg-dark-700 text-gray-800 dark:text-gray-200 rounded-tl-none'">
                                            <p x-text="comment.content"></p>
                                        </div>
                                    </div>
                                </template>
                                
                                <div x-show="comments.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400">
                                    <svg class="w-12 h-12 mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <p class="text-sm italic">Belum ada diskusi.</p>
                                </div>
                            </div>

                            <!-- Input Area -->
                            <div class="p-4 bg-white dark:bg-dark-800 border-t dark:border-gray-700">
                                <form @submit.prevent="postComment" class="relative">
                                    <textarea 
                                        x-model="newComment"
                                        @keydown.enter.prevent="if(!$event.shiftKey) postComment()"
                                        rows="2"
                                        class="block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm resize-none pr-12"
                                        placeholder="Ketik pesan..."
                                    ></textarea>
                                    <button 
                                        type="submit"
                                        :disabled="!newComment.trim() || isPosting"
                                        class="absolute right-2 bottom-2 p-2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 disabled:opacity-50 transition-colors"
                                    >
                                        <svg x-show="!isPosting" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                        </svg>
                                        <svg x-show="isPosting" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function cumulativeEditor() {
                return {
                    data: @json($report->cumulative_data ?? null),
                    originalData: JSON.stringify(@json($report->cumulative_data ?? null)),
                    hasChanges: false,
                    saving: false,
                    saveStatus: '',
                    saveMessage: '',
                    saveUrl: '{{ route("projects.weekly-reports.update-cumulative", [$project, $report]) }}',
                    canManage: @json(auth()->user()->can('weekly_report.manage')),

                    fmt(val) {
                        return parseFloat(val || 0).toFixed(4);
                    },

                    get allRows() {
                        if (!this.data || !this.data.sections) return [];
                        let rows = [];
                        let counter = 0;
                        this.data.sections.forEach(section => {
                            rows = rows.concat(this.flattenSection(section, 0, counter));
                            counter = rows.length;
                        });
                        return rows;
                    },

                    flattenSection(section, level, startIdx) {
                        let rows = [];
                        const indent = '&nbsp;&nbsp;&nbsp;&nbsp;'.repeat(level);
                        let idx = startIdx || 0;

                        // Section header
                        rows.push({
                            type: 'header',
                            key: 'h-' + idx++,
                            indent: indent,
                            label: section.code + ' ' + section.name,
                        });

                        // Items
                        if (section.items) {
                            section.items.forEach((item) => {
                                rows.push({
                                    type: 'item',
                                    key: 'i-' + idx++,
                                    indent: indent,
                                    item: item,
                                });
                            });
                        }

                        // Recursive children
                        if (section.children) {
                            section.children.forEach(child => {
                                const childRows = this.flattenSection(child, level + 1, idx);
                                idx += childRows.length;
                                rows = rows.concat(childRows);
                            });
                        }

                        return rows;
                    },

                    updateActualCurrent(item, newValue) {
                        newValue = Math.round(newValue * 10000) / 10000;
                        item.actual.current = newValue;
                        item.actual.cumulative = Math.round((item.actual.up_to_prev + newValue) * 10000) / 10000;
                        item.deviation.current = Math.round((newValue - item.planned.current) * 10000) / 10000;
                        item.deviation.cumulative = Math.round((item.actual.cumulative - item.planned.cumulative) * 10000) / 10000;

                        this.recalculateTotals();
                        this.hasChanges = true;
                        this.saveStatus = '';
                    },

                    recalculateTotals() {
                        if (!this.data || !this.data.sections) return;

                        let totals = {
                            weight: 0, planned_prev: 0, planned_current: 0, planned_cumulative: 0,
                            actual_prev: 0, actual_current: 0, actual_cumulative: 0,
                            deviation_prev: 0, deviation_current: 0, deviation_cumulative: 0,
                        };

                        const accumulate = (section) => {
                            if (section.items) {
                                section.items.forEach(item => {
                                    totals.weight += parseFloat(item.weight || 0);
                                    totals.planned_prev += parseFloat(item.planned.up_to_prev || 0);
                                    totals.planned_current += parseFloat(item.planned.current || 0);
                                    totals.planned_cumulative += parseFloat(item.planned.cumulative || 0);
                                    totals.actual_prev += parseFloat(item.actual.up_to_prev || 0);
                                    totals.actual_current += parseFloat(item.actual.current || 0);
                                    totals.actual_cumulative += parseFloat(item.actual.cumulative || 0);
                                });
                            }
                            if (section.children) {
                                section.children.forEach(child => accumulate(child));
                            }
                        };

                        this.data.sections.forEach(s => accumulate(s));

                        totals.deviation_prev = totals.actual_prev - totals.planned_prev;
                        totals.deviation_current = totals.actual_current - totals.planned_current;
                        totals.deviation_cumulative = totals.actual_cumulative - totals.planned_cumulative;

                        this.data.totals = totals;
                    },

                    async save() {
                        if (this.saving) return;
                        this.saving = true;
                        this.saveStatus = '';

                        // Collect all item actual_current values
                        let items = {};
                        const collectItems = (section) => {
                            if (section.items) {
                                section.items.forEach(item => {
                                    items[item.code] = item.actual.current;
                                });
                            }
                            if (section.children) {
                                section.children.forEach(child => collectItems(child));
                            }
                        };

                        this.data.sections.forEach(s => collectItems(s));

                        try {
                            const response = await fetch(this.saveUrl, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ items }),
                            });

                            const result = await response.json();

                            if (response.ok && result.success) {
                                this.data = result.cumulative_data;
                                this.originalData = JSON.stringify(result.cumulative_data);
                                this.hasChanges = false;
                                this.saveMessage = result.message || 'Tersimpan!';
                                this.saveStatus = 'success';
                                setTimeout(() => this.saveStatus = '', 5000);
                            } else {
                                this.saveStatus = 'error';
                            }
                        } catch (e) {
                            console.error('Save failed:', e);
                            this.saveStatus = 'error';
                        } finally {
                            this.saving = false;
                        }
                    },
                };
            }

            function discussionPanel() {
                return {
                    isOpen: false,
                    comments: @json(\App\Http\Resources\Api\CommentResource::collection($report->comments)->resolve()),
                    newComment: '',
                    isPosting: false,
                    currentTarget: null,
                    projectId: {{ $project->id }},
                    reportId: {{ $report->id }},

                    init() {
                        console.log('Discussion panel initialized for report:', this.reportId);
                        if (window.Echo) {
                            window.Echo.private(`project.${this.projectId}`)
                                .listen('CommentPosted', (e) => {
                                    if (e.commentable_type === 'App\\Models\\WeeklyReport' && 
                                        e.commentable_id == this.reportId) {
                                        // Avoid duplicates if we already pushed via postComment
                                        if (!this.comments.find(c => c.id == e.comment.id)) {
                                            this.comments.push(e.comment);
                                            this.scrollToBottom();
                                        }
                                    }
                                });
                        }
                    },

                    open(detail = null) {
                        this.isOpen = true;
                        if (detail) {
                            this.currentTarget = detail;
                        }
                        this.scrollToBottom();
                    },

                    async postComment() {
                        if (!this.newComment.trim() || this.isPosting) return;
                        
                        this.isPosting = true;
                        try {
                            const response = await fetch('/api/comments', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    commentable_type: 'App\\Models\\WeeklyReport',
                                    commentable_id: this.reportId,
                                    content: this.newComment,
                                    metadata: this.currentTarget ? { 
                                        target: this.currentTarget.target, 
                                        id: this.currentTarget.id,
                                        url: this.currentTarget.url
                                    } : null
                                })
                            });

                            const result = await response.json();
                            if (response.ok && result.success) {
                                // Only push if not already added by Echo/real-time
                                if (!this.comments.find(c => c.id == result.data.id)) {
                                    this.comments.push(result.data);
                                }
                                this.newComment = '';
                                this.currentTarget = null;
                                this.scrollToBottom();
                            }
                        } catch (e) {
                            console.error('Failed to post comment:', e);
                        } finally {
                            this.isPosting = false;
                        }
                    },

                    scrollToBottom() {
                        this.$nextTick(() => {
                            const container = document.getElementById('comments-container');
                            if (container) {
                                container.scrollTop = container.scrollHeight;
                            }
                        });
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>


