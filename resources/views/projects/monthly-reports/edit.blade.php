<x-app-layout>
    @php
        $progressPhotos = $progressPhotos ?? [];
    @endphp

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Proyek', 'url' => route('projects.index')],
            ['label' => $project->name, 'url' => route('projects.show', $project)],
            ['label' => 'Monthly Reports', 'url' => route('projects.monthly-reports.index', $project)],
            [
                'label' => \Carbon\Carbon::createFromDate($report->year, $report->month, 1)->translatedFormat('F Y'),
                'url' => route('projects.monthly-reports.show', [$project, $report]),
            ],
            ['label' => 'Edit'],
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Edit Monthly Report -
                    {{ \Carbon\Carbon::createFromDate($report->year, $report->month, 1)->translatedFormat('F Y') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $report->period_label }}</p>
            </div>
            <div class="flex space-x-2">
                <form action="{{ route('projects.monthly-reports.copy-previous', [$project, $report]) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Copy dari Bulan Sebelumnya
                    </button>
                </form>
                <a href="{{ route('projects.monthly-reports.show', [$project, $report]) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Show
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        @php
            // Defensive defaults to avoid fatal errors in tests/environments
            $progressPhotos = $progressPhotos ?? [];
            $projectImages = $projectImages ?? (isset($projectImages) ? $projectImages : collect());
            $report->documentation_uploads = $report->documentation_uploads ?? [];
        @endphp
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div
                    class="p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Section 1: Cover -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4" x-data="coverEditor()"
                x-cloak>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <span
                            class="inline-flex items-center justify-center w-8 h-8 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded-full mr-2 text-sm font-bold">1</span>
                        Cover
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
                        <button @click="save()" :disabled="saving"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors">
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
                            <span x-text="saving ? 'Menyimpan...' : 'Simpan Cover'"></span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Judul
                                Cover</label>
                            <input type="text" x-model="formData.cover_title"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select x-model="formData.status"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white focus:ring-primary-500 focus:border-primary-500">
                                <option value="draft">Draft</option>
                                @can('monthly_report.publish')
                                    <option value="published">Published</option>
                                @endcan
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Bulan:</span>
                                <p class="text-xl font-bold text-primary-600 dark:text-primary-400">
                                    {{ \Carbon\Carbon::createFromDate($report->year, $report->month, 1)->translatedFormat('F Y') }}
                                </p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Periode:</span>
                                <p class="text-gray-900 dark:text-white">{{ $report->period_label }}</p>
                            </div>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Lokasi / Nama Proyek:</span>
                            <p class="text-gray-900 dark:text-white">{{ $project->location ?? '-' }} /
                                {{ $project->name }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cover
                            Image</label>

                        <div
                            class="mb-4 aspect-video rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 relative group">
                            <img :src="currentCoverUrl" x-show="currentCoverUrl" alt="Cover Image"
                                class="w-full h-full object-cover">
                            <div x-show="!currentCoverUrl"
                                class="absolute inset-0 flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p>No cover image</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Upload gambar
                                    baru:</label>
                                <input type="file" @change="handleFileUpload" accept="image/*"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900 dark:file:text-primary-300">
                            </div>

                            @if ($projectImages->count() > 0)
                                <div>
                                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-2">Atau pilih dari
                                        Project Files:</label>
                                    <div
                                        class="grid grid-cols-4 gap-2 max-h-48 overflow-y-auto border rounded-lg p-2 dark:border-gray-600">
                                        @foreach ($projectImages as $image)
                                            <label class="relative cursor-pointer">
                                                <input type="radio" value="{{ $image->id }}"
                                                    x-model="formData.cover_image_id" class="hidden peer">
                                                <div
                                                    class="aspect-square rounded-lg overflow-hidden border-2 border-transparent peer-checked:border-primary-500 transition-all">
                                                    @if ($image->latestVersion)
                                                        <img src="{{ \App\Models\SystemSetting::getFileUrl($image->latestVersion->file_path) }}"
                                                            alt="{{ $image->name }}"
                                                            class="w-full h-full object-cover">
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Kumulatif Progress -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4" x-data="cumulativeEditor()"
                x-cloak>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <span
                            class="inline-flex items-center justify-center w-8 h-8 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded-full mr-2 text-sm font-bold">2</span>
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
                            <svg x-show="saving" class="w-4 h-4 mr-2 animate-spin" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
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
                                    :class="row.type === 'header' ? 'bg-gray-50 dark:bg-dark-700 font-semibold' :
                                        'hover:bg-gray-50 dark:hover:bg-dark-700'">
                                    <template x-if="row.type === 'header'">
                                        <td colspan="11" class="px-3 py-2 border text-gray-900 dark:text-white"
                                            x-html="row.indent + row.label"></td>
                                    </template>
                                    <template x-if="row.type === 'item'">
                                        <td class="px-3 py-1 border text-xs text-gray-900 dark:text-gray-200"
                                            x-html="row.indent + '&nbsp;&nbsp;&nbsp;&nbsp;' + row.item.code + ' ' + (row.item.work_name || row.item.name || '')">
                                        </td>
                                    </template>
                                    <template x-if="row.type === 'item'">
                                        <td class="px-2 py-1 text-center border text-xs text-gray-900 dark:text-gray-200"
                                            x-text="fmt(row.item.weight)"></td>
                                    </template>
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
                                    <template x-if="row.type === 'item'">
                                        <td class="px-2 py-1 text-center border dark:border-gray-600 text-xs text-green-700 dark:text-green-200 bg-green-50 dark:bg-green-900/50"
                                            x-text="fmt(row.item.actual.up_to_prev)"></td>
                                    </template>
                                    <template x-if="row.type === 'item'">
                                        <td
                                            class="px-2 py-1 text-center border dark:border-gray-600 text-xs text-green-700 dark:text-green-200 bg-green-50 dark:bg-green-900/50 p-0">
                                            <input type="number" step="0.0001" min="0"
                                                class="w-full text-center text-xs bg-green-100 dark:bg-green-900 border-0 focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 py-1 px-1 text-green-800 dark:text-green-100 font-medium"
                                                :value="row.item.actual.current"
                                                @input="updateActualCurrent(row.item, parseFloat($event.target.value) || 0)">
                                        </td>
                                    </template>
                                    <template x-if="row.type === 'item'">
                                        <td class="px-2 py-1 text-center border dark:border-gray-600 text-xs text-green-700 dark:text-green-200 bg-green-50 dark:bg-green-900/50"
                                            x-text="fmt(row.item.actual.cumulative)"></td>
                                    </template>
                                    <template x-if="row.type === 'item'">
                                        <td class="px-2 py-1 text-center border dark:border-gray-600 text-xs bg-yellow-50 dark:bg-yellow-900/50"
                                            :class="row.item.deviation.up_to_prev >= 0 ? 'text-green-600 dark:text-green-400' :
                                                'text-red-600 dark:text-red-400'"
                                            x-text="fmt(row.item.deviation.up_to_prev)"></td>
                                    </template>
                                    <template x-if="row.type === 'item'">
                                        <td class="px-2 py-1 text-center border dark:border-gray-600 text-xs bg-yellow-50 dark:bg-yellow-900/50"
                                            :class="row.item.deviation.current >= 0 ? 'text-green-600 dark:text-green-400' :
                                                'text-red-600 dark:text-red-400'"
                                            x-text="fmt(row.item.deviation.current)"></td>
                                    </template>
                                    <template x-if="row.type === 'item'">
                                        <td class="px-2 py-1 text-center border dark:border-gray-600 text-xs bg-yellow-50 dark:bg-yellow-900/50"
                                            :class="row.item.deviation.cumulative >= 0 ? 'text-green-600 dark:text-green-400' :
                                                'text-red-600 dark:text-red-400'"
                                            x-text="fmt(row.item.deviation.cumulative)"></td>
                                    </template>
                                </tr>
                            </template>

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
                                    <td colspan="11"
                                        class="px-3 py-1.5 text-center text-gray-500 dark:text-gray-400">Data progress
                                        tidak tersedia.</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section 3: Detail Progress -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <span
                            class="inline-flex items-center justify-center w-8 h-8 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded-full mr-2 text-sm font-bold">3</span>
                        Detail Progress
                    </h3>
                    <div class="text-sm text-gray-500 dark:text-gray-400 italic">
                        *Data ditarik otomatis dari Progress Reports
                    </div>
                </div>

                @if ($report->detail_data && count($report->detail_data) > 0)
                    <div class="space-y-4">
                        @foreach ($report->detail_data as $detail)
                            <div class="border dark:border-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <span
                                            class="text-sm text-gray-500 dark:text-gray-400">{{ $detail['date_label'] }}</span>
                                        @if ($detail['rab_item'])
                                            <p class="font-medium text-gray-900 dark:text-white">
                                                {{ $detail['rab_item']['code'] }} - {{ $detail['rab_item']['work_name'] ?? $detail['rab_item']['name'] ?? '' }}
                                            </p>
                                        @endif
                                    </div>
                                    <span
                                        class="text-lg font-bold text-primary-600">{{ $detail['progress_percentage'] }}%</span>
                                </div>
                                @if ($detail['description'])
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                                        {{ $detail['description'] }}</p>
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
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">Tidak ada laporan progress dalam
                        periode ini.</p>
                @endif
            </div>

            <!-- Section 4: Project Documentations -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4" x-data="documentationsEditor()"
                x-cloak>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <span
                            class="inline-flex items-center justify-center w-8 h-8 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded-full mr-2 text-sm font-bold">4</span>
                        Project Documentations
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
                            Gagal memproses!
                        </span>
                    </div>
                </div>

                <div class="space-y-6">
                    <!-- Current Selected Docs -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dokumentasi Terpilih</h4>
                        <template x-if="currentDocs.length > 0">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <template x-for="doc in currentDocs" :key="doc.id">
                                    <div
                                        class="relative group aspect-square rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                                        <img :src="doc.url" :alt="doc.name"
                                            class="w-full h-full object-cover">
                                        <button @click="removeDoc(doc)"
                                            class="absolute top-2 right-2 p-1.5 bg-red-600 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                                            title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                        <div class="absolute bottom-0 inset-x-0 bg-black bg-opacity-50 text-white text-xs truncate px-2 py-1"
                                            x-text="doc.name"></div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="currentDocs.length === 0">
                            <p
                                class="text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-dark-700 p-4 rounded-lg text-center border border-dashed border-gray-300 dark:border-gray-600">
                                Belum ada dokumentasi ditambahkan.</p>
                        </template>
                    </div>

                    <!-- Add New Docs Tabs -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4" x-data="{ activeTab: 'upload' }">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Tambahkan Dokumentasi
                        </h4>

                        <div class="flex space-x-2 mb-4 border-b border-gray-200 dark:border-gray-700">
                            <button @click="activeTab = 'upload'"
                                :class="{ 'border-b-2 border-primary-500 text-primary-600': activeTab === 'upload', 'text-gray-500 hover:text-gray-700': activeTab !== 'upload' }"
                                class="pb-2 px-1 text-sm font-medium transition-colors">Upload Manual</button>
                            <button @click="activeTab = 'progress'"
                                :class="{ 'border-b-2 border-primary-500 text-primary-600': activeTab === 'progress', 'text-gray-500 hover:text-gray-700': activeTab !== 'progress' }"
                                class="pb-2 px-1 text-sm font-medium transition-colors">Dari Progress Report</button>
                        </div>

                        <!-- Upload Tab -->
                        <div x-show="activeTab === 'upload'" class="space-y-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-1">
                                    <input type="file" multiple accept="image/*" @change="handleMultipleUploads"
                                        id="manual_uploads"
                                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900 dark:file:text-primary-300">
                                </div>
                                <button @click="uploadFiles()" :disabled="!hasFilesToUpload || uploading"
                                    class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors">
                                    <svg x-show="!uploading" class="w-4 h-4 mr-2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    <svg x-show="uploading" class="w-4 h-4 mr-2 animate-spin" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    <span x-text="uploading ? 'Mengupload...' : 'Upload & Tambahkan'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Progress Report Tab -->
                        <div x-show="activeTab === 'progress'" class="space-y-4">
                            @if (!empty($progressPhotos) && count($progressPhotos) > 0)
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Pilih foto dari progress
                                        report periode ini:</span>
                                    <button @click="addSelectedProgressPhotos()"
                                        :disabled="selectedProgressPhotos.length === 0 || addingPhotos"
                                        class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white text-xs font-medium rounded transition-colors">
                                        <svg x-show="!addingPhotos" class="w-3 h-3 mr-1" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        <svg x-show="addingPhotos" class="w-3 h-3 mr-1 animate-spin" fill="none"
                                            viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <span
                                            x-text="addingPhotos ? 'Menambahkan...' : 'Tambahkan Terpilih (' + selectedProgressPhotos.length + ')'"></span>
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 max-h-64 overflow-y-auto p-1">
                                    @php
                                        // Filter out photos that are already in the report's documentation_uploads
                                        $existingPaths = $report->documentation_uploads ?? [];
                                    @endphp
                                    @foreach ($progressPhotos as $photo)
                                        @if (!in_array($photo['path'], $existingPaths))
                                            <label class="relative cursor-pointer group">
                                                <input type="checkbox" value="{{ $photo['path'] }}"
                                                    x-model="selectedProgressPhotos" class="hidden peer">
                                                <div
                                                    class="aspect-square rounded-lg overflow-hidden border-2 border-transparent peer-checked:border-green-500 transition-all relative">
                                                    <img src="{{ $photo['url'] }}"
                                                        class="w-full h-full object-cover">
                                                    <div
                                                        class="absolute bottom-0 inset-x-0 bg-black bg-opacity-60 text-white text-[10px] p-1 flex flex-col leading-tight">
                                                        <span>{{ $photo['date'] }}</span>
                                                        <span class="truncate">{{ $photo['rab_item'] }}</span>
                                                    </div>
                                                </div>
                                                <div class="absolute top-1 right-1 hidden peer-checked:block">
                                                    <span
                                                        class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-green-500 text-white shadow-sm">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="3" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </span>
                                                </div>
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-dark-700 p-4 rounded-lg text-center border border-dashed border-gray-300 dark:border-gray-600">
                                    Tidak ada foto progress report pada periode ini.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 5: Activity and Problems -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4" x-data="activitiesEditor()"
                x-cloak>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <span
                            class="inline-flex items-center justify-center w-8 h-8 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded-full mr-2 text-sm font-bold">5</span>
                        Activity and Problems
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
                        <button @click="save()" :disabled="saving || !hasChanges"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg x-show="!saving" class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <svg x-show="saving" class="w-4 h-4 mr-2 animate-spin" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="saving ? 'Menyimpan...' : 'Simpan Naskah'"></span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Aktivitas Bulan
                            Ini</label>
                        <textarea x-model="formData.activities" @input="hasChanges = true" rows="6"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white focus:ring-primary-500 focus:border-primary-500"
                            placeholder="Deskripsi aktivitas yang dilakukan pada bulan ini..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kendala /
                            Masalah</label>
                        <textarea x-model="formData.problems" @input="hasChanges = true" rows="6"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white focus:ring-primary-500 focus:border-primary-500"
                            placeholder="Deskripsi kendala atau masalah yang dihadapi..."></textarea>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            // CSRF Token utility
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            // Reusable fetch JSON options
            const fetchOptions = (method, data) => ({
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });

            // Reusable flash message
            const showStatus = (component, status, message = '') => {
                component.saveStatus = status;
                component.saveMessage = message;
                if (status === 'success') {
                    setTimeout(() => component.saveStatus = '', 4000);
                }
            };

            function coverEditor() {
                return {
                    formData: {
                        cover_title: @json($report->cover_title),
                        status: @json($report->status),
                        cover_image_id: @json($report->cover_image_id),
                    },
                    currentCoverUrl: @json($report->cover_image_url),
                    fileToUpload: null,
                    saving: false,
                    saveStatus: '',
                    saveMessage: '',

                    handleFileUpload(e) {
                        if (e.target.files.length > 0) {
                            this.fileToUpload = e.target.files[0];
                            this.formData.cover_image_id = null; // reset project file selection

                            // local preview
                            const reader = new FileReader();
                            reader.onload = (e) => this.currentCoverUrl = e.target.result;
                            reader.readAsDataURL(this.fileToUpload);
                        }
                    },

                    async save() {
                        if (this.saving) return;
                        this.saving = true;

                        const uploadData = new FormData();
                        uploadData.append('_token', csrfToken);
                        if (this.formData.cover_title) uploadData.append('cover_title', this.formData.cover_title);
                        if (this.formData.status) uploadData.append('status', this.formData.status);
                        if (this.formData.cover_image_id) uploadData.append('cover_image_id', this.formData.cover_image_id);
                        if (this.fileToUpload) uploadData.append('cover_image_upload', this.fileToUpload);

                        try {
                            const response = await fetch(
                                '{{ route('projects.monthly-reports.update-cover', [$project, $report]) }}', {
                                    method: 'POST',
                                    body: uploadData,
                                });

                            const result = await response.json();
                            if (response.ok && result.success) {
                                this.currentCoverUrl = result.cover_image_url;
                                this.fileToUpload = null;
                                document.querySelector('input[type="file"]').value = '';
                                showStatus(this, 'success', result.message);
                            } else {
                                showStatus(this, 'error');
                            }
                        } catch (e) {
                            showStatus(this, 'error');
                        } finally {
                            this.saving = false;
                        }
                    }
                }
            }

            function cumulativeEditor() {
                return {
                    data: @json($report->cumulative_data ?? null),
                    originalData: JSON.stringify(@json($report->cumulative_data ?? null)),
                    hasChanges: false,
                    saving: false,
                    saveStatus: '',
                    saveMessage: '',
                    saveUrl: '{{ route('projects.monthly-reports.update-cumulative', [$project, $report]) }}',

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

                        rows.push({
                            type: 'header',
                            key: 'h-' + idx++,
                            indent: indent,
                            label: section.code + ' ' + section.name,
                        });

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
                        item.deviation.cumulative = Math.round((item.actual.cumulative - item.planned.cumulative) * 10000) /
                            10000;

                        this.recalculateTotals();
                        this.hasChanges = true;
                        this.saveStatus = '';
                    },

                    recalculateTotals() {
                        if (!this.data || !this.data.sections) return;

                        let totals = {
                            weight: 0,
                            planned_prev: 0,
                            planned_current: 0,
                            planned_cumulative: 0,
                            actual_prev: 0,
                            actual_current: 0,
                            actual_cumulative: 0,
                            deviation_prev: 0,
                            deviation_current: 0,
                            deviation_cumulative: 0,
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
                            const response = await fetch(this.saveUrl, fetchOptions('PATCH', {
                                items
                            }));
                            const result = await response.json();

                            if (response.ok && result.success) {
                                this.data = result.cumulative_data;
                                this.originalData = JSON.stringify(result.cumulative_data);
                                this.hasChanges = false;
                                showStatus(this, 'success', result.message);
                            } else {
                                showStatus(this, 'error');
                            }
                        } catch (e) {
                            showStatus(this, 'error');
                        } finally {
                            this.saving = false;
                        }
                    },
                };
            }

            function documentationsEditor() {
                return {
                    currentDocs: @json($report->documentation_files),
                    filesToUpload: [],
                    selectedProgressPhotos: [],
                    hasFilesToUpload: false,
                    uploading: false,
                    addingPhotos: false,
                    saveStatus: '',
                    saveMessage: '',

                    handleMultipleUploads(e) {
                        this.filesToUpload = e.target.files;
                        this.hasFilesToUpload = this.filesToUpload.length > 0;
                    },

                    async uploadFiles() {
                        if (this.uploading || this.filesToUpload.length === 0) return;
                        this.uploading = true;

                        const uploadData = new FormData();
                        uploadData.append('_token', csrfToken);
                        for (let i = 0; i < this.filesToUpload.length; i++) {
                            uploadData.append('photos[]', this.filesToUpload[i]);
                        }

                        try {
                            const response = await fetch(
                                '{{ route('projects.monthly-reports.upload-documentation', [$project, $report]) }}', {
                                    method: 'POST',
                                    body: uploadData,
                                });

                            const result = await response.json();
                            if (response.ok && result.success) {
                                this.currentDocs = [...this.currentDocs, ...result.photos];
                                this.filesToUpload = [];
                                this.hasFilesToUpload = false;
                                document.getElementById('manual_uploads').value = '';
                                showStatus(this, 'success', result.message);
                            } else {
                                showStatus(this, 'error');
                            }
                        } catch (e) {
                            showStatus(this, 'error');
                        } finally {
                            this.uploading = false;
                        }
                    },

                    async addSelectedProgressPhotos() {
                        if (this.addingPhotos || this.selectedProgressPhotos.length === 0) return;
                        this.addingPhotos = true;

                        try {
                            const response = await fetch(
                                '{{ route('projects.monthly-reports.add-progress-photos', [$project, $report]) }}',
                                fetchOptions('POST', {
                                    photo_paths: this.selectedProgressPhotos
                                })
                            );

                            const result = await response.json();
                            if (response.ok && result.success) {
                                this.currentDocs = [...this.currentDocs, ...result.photos];
                                // Remove selected from the UI (checkboxes remain unchecked due to x-model resetting but we leave them hidden later if reloading or doing complex ops)
                                this.selectedProgressPhotos = [];
                                // Here we could also dynamically remove them from DOM but Alpine might be tricky with the complex DOM, for now unchecking is fine
                                document.querySelectorAll('input.peer:checked').forEach(el => el.checked = false);

                                showStatus(this, 'success', result.message);
                            } else {
                                showStatus(this, 'error');
                            }
                        } catch (e) {
                            showStatus(this, 'error');
                        } finally {
                            this.addingPhotos = false;
                        }
                    },

                    async removeDoc(doc) {
                        try {
                            const response = await fetch(
                                '{{ route('projects.monthly-reports.remove-documentation', [$project, $report]) }}',
                                fetchOptions('DELETE', {
                                    type: doc.source,
                                    id: doc.id,
                                    path: doc.path
                                })
                            );

                            const result = await response.json();
                            if (response.ok && result.success) {
                                this.currentDocs = this.currentDocs.filter(d => d.id !== doc.id);
                                showStatus(this, 'success', result.message);
                            } else {
                                showStatus(this, 'error');
                            }
                        } catch (e) {
                            showStatus(this, 'error');
                        }
                    }
                }
            }

            function activitiesEditor() {
                return {
                    formData: {
                        activities: @json($report->activities),
                        problems: @json($report->problems),
                    },
                    hasChanges: false,
                    saving: false,
                    saveStatus: '',
                    saveMessage: '',

                    async save() {
                        if (this.saving || !this.hasChanges) return;
                        this.saving = true;

                        try {
                            const response = await fetch(
                                '{{ route('projects.monthly-reports.update-activities', [$project, $report]) }}',
                                fetchOptions('PATCH', this.formData)
                            );

                            const result = await response.json();
                            if (response.ok && result.success) {
                                this.hasChanges = false;
                                showStatus(this, 'success', result.message);
                            } else {
                                showStatus(this, 'error');
                            }
                        } catch (e) {
                            showStatus(this, 'error');
                        } finally {
                            this.saving = false;
                        }
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>
