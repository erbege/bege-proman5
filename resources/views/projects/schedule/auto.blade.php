<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Jadwal', 'url' => route('projects.schedule.index', $project)],
        ['label' => 'Auto Schedule']
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Auto Schedule - {{ $project->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Preview jadwal otomatis berdasarkan bobot RAB</p>
            </div>
        </div>
    </x-slot>

    @include('projects.navigation')

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <div x-data="{ isSubmitting: false }">
        {{-- Loading Overlay - Positioned at root level for proper z-index --}}
        <div x-show="isSubmitting" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[9999] flex items-center justify-center">
            <div class="bg-white dark:bg-dark-800 rounded-xl p-8 shadow-2xl text-center max-w-sm mx-4">
                <div
                    class="animate-spin rounded-full h-16 w-16 border-4 border-blue-500 border-t-transparent mx-auto mb-4">
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Menerapkan Jadwal Otomatis</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Memperbarui jadwal item pekerjaan dan regenerasi
                    time schedule...</p>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Summary Card -->
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ringkasan Perhitungan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Item Pekerjaan</p>
                            <p class="font-semibold text-gray-900 dark:text-white text-xl">
                                {{ $autoScheduleData['summary']['total_items'] }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Mulai Proyek</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $project->start_date->format('d M Y') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Selesai Proyek</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $project->end_date->format('d M Y') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Hasil Perhitungan Selesai</p>
                            <p
                                class="font-semibold {{ $autoScheduleData['summary']['has_mismatch'] ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400' }}">
                                {{ \Carbon\Carbon::parse($autoScheduleData['summary']['calculated_end_date'])->format('d M Y') }}
                            </p>
                        </div>
                    </div>

                    @if($autoScheduleData['summary']['has_mismatch'])
                        <div
                            class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                            <div class="flex">
                                <x-heroicon-o-exclamation-triangle
                                    class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2 flex-shrink-0" />
                                <div>
                                    <p class="font-medium text-yellow-800 dark:text-yellow-200">
                                        Perbedaan Durasi Terdeteksi
                                    </p>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                                        Hasil perhitungan menunjukkan jadwal akan selesai
                                        <strong>{{ abs($autoScheduleData['summary']['days_difference']) }} hari</strong>
                                        {{ $autoScheduleData['summary']['days_difference'] > 0 ? 'lebih lambat' : 'lebih cepat' }}
                                        dari tanggal selesai proyek yang direncanakan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div
                            class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                            <div class="flex">
                                <x-heroicon-o-check-circle
                                    class="w-5 h-5 text-green-600 dark:text-green-400 mr-2 flex-shrink-0" />
                                <p class="text-green-800 dark:text-green-200">
                                    Jadwal otomatis sesuai dengan durasi proyek yang direncanakan.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Preview Table -->
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Preview Jadwal</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-dark-700">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        No</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Pekerjaan</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Bobot</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Durasi</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Mulai</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Selesai</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($autoScheduleData['items'] as $index => $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">{{ $index + 1 }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <span>{{ $item['work_name'] }}</span>
                                                @if(!empty($item['can_parallel']))
                                                    <span
                                                        class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 rounded"
                                                        title="Dapat dikerjakan paralel dengan item sebelumnya">
                                                        <x-heroicon-o-arrows-right-left class="w-3 h-3 mr-0.5" />Paralel
                                                    </span>
                                                @endif
                                            </div>
                                            @if($item['section_name'])
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $item['section_name'] }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white text-center">
                                            {{ number_format($item['weight_percentage'], 2) }}%
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white text-center">
                                            {{ $item['duration_days'] }} hari
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white text-center">
                                            {{ \Carbon\Carbon::parse($item['planned_start'])->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-white text-center">
                                            {{ \Carbon\Carbon::parse($item['planned_end'])->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Action Options -->
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Pilih Tindakan</h3>

                    <form action="{{ route('projects.schedule.auto.apply', $project) }}" method="POST"
                        @submit="isSubmitting = true">
                        @csrf

                        <div class="space-y-4">
                            @if($autoScheduleData['summary']['has_mismatch'] && $autoScheduleData['summary']['days_difference'] > 0)
                                <!-- Option 1: Extend end date -->
                                <label
                                    class="flex items-start p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-700">
                                    <input type="radio" name="mode" value="extend"
                                        class="mt-1 text-blue-600 focus:ring-blue-500">
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-900 dark:text-white">Sesuaikan Tanggal Selesai
                                            Proyek
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Perpanjang tanggal selesai proyek menjadi
                                            <strong>{{ \Carbon\Carbon::parse($autoScheduleData['summary']['calculated_end_date'])->format('d M Y') }}</strong>
                                            agar sesuai dengan hasil perhitungan.
                                        </p>
                                    </div>
                                </label>

                                <!-- Option 2: Compress schedule -->
                                <label
                                    class="flex items-start p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-700">
                                    <input type="radio" name="mode" value="compress"
                                        class="mt-1 text-blue-600 focus:ring-blue-500">
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-900 dark:text-white">Kompres Jadwal</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Sesuaikan durasi tiap item agar total jadwal tidak melebihi tanggal selesai
                                            proyek
                                            <strong>{{ $project->end_date->format('d M Y') }}</strong>.
                                            Overlap antar pekerjaan akan lebih agresif.
                                        </p>
                                    </div>
                                </label>

                                <!-- Option 3: Keep as is -->
                                <label
                                    class="flex items-start p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-700">
                                    <input type="radio" name="mode" value="keep" checked
                                        class="mt-1 text-blue-600 focus:ring-blue-500">
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-900 dark:text-white">Biarkan Apa Adanya</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Terapkan jadwal sesuai hasil perhitungan tanpa mengubah tanggal selesai proyek.
                                            Anda dapat menyesuaikan jadwal secara manual nanti.
                                        </p>
                                    </div>
                                </label>
                            @else
                                <!-- Just keep - no mismatch -->
                                <input type="hidden" name="mode" value="keep">
                                <div
                                    class="p-4 border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                    <p class="text-green-800 dark:text-green-200">
                                        Jadwal otomatis sudah sesuai dengan durasi proyek. Klik tombol di bawah untuk
                                        menerapkan.
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div class="mt-6 flex items-center space-x-4">
                            <button type="submit" :disabled="isSubmitting"
                                :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <template x-if="!isSubmitting">
                                    <span class="flex items-center">
                                        <x-heroicon-o-check class="w-4 h-4 mr-2" />
                                        Terapkan Jadwal Otomatis
                                    </span>
                                </template>
                                <template x-if="isSubmitting">
                                    <span class="flex items-center">
                                        <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Memproses...
                                    </span>
                                </template>
                            </button>
                            <a href="{{ route('projects.schedule.index', $project) }}"
                                :class="{ 'pointer-events-none opacity-50': isSubmitting }"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-500">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Back Button -->
                <div class="mt-6">
                    <a href="{{ route('projects.schedule.index', $project) }}"
                        class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                        ← Kembali ke Jadwal
                    </a>
                </div>
            </div>
        </div>
    </div>{{-- Close x-data wrapper --}}
</x-app-layout>