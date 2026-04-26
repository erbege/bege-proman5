<div>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

                <div class="p-6">
                    @if($reports->count() > 0)
                        <!-- LIST VIEW -->
                        @if($viewMode === 'list')
                            <div class="space-y-4">
                                @foreach($reports as $report)
                                            <div class="border dark:border-dark-700 rounded-lg p-4 hover:shadow-md transition-shadow duration-200 hover:border-gold-300 dark:hover:border-gold-700 cursor-pointer"
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
                                                                <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                                                    {{ $report->report_date->format('F Y') }}
                                                                </span>
                                                                @if($report->rabItem)
                                                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                                                        {{ $report->rabItem->work_name }}
                                                                    </p>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="flex flex-wrap items-center gap-3 mt-3">
                                                            <span
                                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                                                                                                                                                                                                        {{ $report->progress_percentage >= 80 ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' :
                                    ($report->progress_percentage >= 50 ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' :
                                        'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300') }}">
                                                                <x-heroicon-s-arrow-trending-up class="w-4 h-4 mr-1" />
                                                                {{ number_format($report->progress_percentage, 1) }}%
                                                            </span>

                                                            @if($report->weather)
                                                                @php $weatherIcons = ['sunny' => '☀️', 'cloudy' => '☁️', 'rainy' => '🌧️', 'stormy' => '⛈️']; @endphp
                                                                <span
                                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-sky-100 text-sky-800 dark:bg-sky-900/50 dark:text-sky-300">
                                                                    {{ $weatherIcons[$report->weather] ?? '' }}
                                                                    {{ $weatherOptions[$report->weather] ?? ucfirst($report->weather) }}
                                                                </span>
                                                            @endif

                                                            @if($report->workers_count)
                                                                <span
                                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700 dark:bg-dark-700 dark:text-gray-300">
                                                                    <x-heroicon-o-user-group class="w-4 h-4 mr-1" />
                                                                    {{ $report->workers_count }} pekerja
                                                                </span>
                                                            @endif
                                                        </div>

                                                        @if($report->description)
                                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-3 line-clamp-2">
                                                                {{ $report->description }}
                                                            </p>
                                                        @endif
                                                    </div>

                                                    <div class="flex items-center space-x-2 ml-4">
                                                        <button type="button" wire:click="showDetail({{ $report->id }})"
                                                            class="p-2 text-gray-500 hover:text-gold-600 hover:bg-gold-50 dark:hover:bg-gold-900/20 rounded-lg transition">
                                                            <x-heroicon-o-eye class="w-5 h-5" />
                                                        </button>
                                                        <button type="button" wire:click.stop="confirmDelete({{ $report->id }})"
                                                            class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                                                            <x-heroicon-o-trash class="w-5 h-5" />
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- GRID VIEW -->
                        @if($viewMode === 'grid')
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($reports as $report)
                                    <div class="border dark:border-dark-700 rounded-xl overflow-hidden hover:shadow-lg transition-all duration-200 hover:border-gold-300 dark:hover:border-gold-700 cursor-pointer"
                                        wire:click="showDetail({{ $report->id }})">
                                        <div class="bg-gradient-to-r from-gold-500 to-gold-600 px-4 py-3 text-white">
                                            <div class="flex justify-between items-center">
                                                <span class="font-bold">{{ $report->report_date->format('d M Y') }}</span>
                                                @if($report->weather)
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
                                                        <circle cx="40" cy="40" r="36" stroke-width="8" fill="transparent"
                                                            class="stroke-gray-200 dark:stroke-dark-600" />
                                                        <circle cx="40" cy="40" r="36" stroke-width="8" fill="transparent"
                                                            stroke-dasharray="{{ 226 }}"
                                                            stroke-dashoffset="{{ 226 - (226 * min($report->progress_percentage, 100) / 100) }}"
                                                            class="stroke-gold-500 transition-all duration-500" />
                                                    </svg>
                                                    <span
                                                        class="absolute inset-0 flex items-center justify-center text-lg font-bold text-gray-900 dark:text-white">
                                                        {{ number_format($report->progress_percentage, 0) }}%
                                                    </span>
                                                </div>
                                            </div>

                                            @if($report->rabItem)
                                                <h4 class="font-medium text-gray-900 dark:text-white text-center text-sm truncate mb-2">
                                                    {{ $report->rabItem->work_name }}
                                                </h4>
                                            @endif

                                            @if($report->workers_count)
                                                <p class="text-center text-xs text-gray-500 dark:text-gray-400">
                                                    <x-heroicon-o-user-group class="w-4 h-4 inline" /> {{ $report->workers_count }}
                                                    pekerja
                                                </p>
                                            @endif

                                            <div class="flex justify-center space-x-2 mt-4 pt-4 border-t dark:border-dark-700">
                                                <button type="button" wire:click="showDetail({{ $report->id }})"
                                                    class="px-3 py-1.5 text-xs font-medium text-gold-600 hover:bg-gold-50 dark:hover:bg-gold-900/20 rounded-lg transition">
                                                    Detail
                                                </button>
                                                <button type="button" wire:click.stop="confirmDelete({{ $report->id }})"
                                                    class="px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- COMPACT TABLE VIEW -->
                        @if($viewMode === 'table')
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-dark-700">
                                    <thead class="bg-gray-50 dark:bg-dark-700">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Tanggal</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Pekerjaan</th>
                                            <th
                                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Progress</th>
                                            <th
                                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Cuaca</th>
                                            <th
                                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Pekerja</th>
                                            <th
                                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-dark-800 divide-y divide-gray-200 dark:divide-dark-700">
                                        @foreach($reports as $report)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-dark-700/50 transition cursor-pointer"
                                                wire:click="showDetail({{ $report->id }})">
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span
                                                        class="text-sm font-medium text-gray-900 dark:text-white">{{ $report->report_date->format('d M Y') }}</span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="text-sm text-gray-900 dark:text-white truncate block max-w-xs">
                                                        {{ $report->rabItem->work_name ?? '-' }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <div class="flex items-center justify-center space-x-2">
                                                        <div class="w-16 bg-gray-200 dark:bg-dark-600 rounded-full h-2">
                                                            <div class="bg-gold-500 h-2 rounded-full"
                                                                style="width: {{ min($report->progress_percentage, 100) }}%"></div>
                                                        </div>
                                                        <span
                                                            class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($report->progress_percentage, 0) }}%</span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    @php $weatherIcons = ['sunny' => '☀️', 'cloudy' => '☁️', 'rainy' => '🌧️', 'stormy' => '⛈️']; @endphp
                                                    <span>{{ $weatherIcons[$report->weather] ?? '-' }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-center text-sm text-gray-900 dark:text-white">
                                                    {{ $report->workers_count ?? '-' }}
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <div class="flex justify-end space-x-1">
                                                        <button type="button" wire:click="showDetail({{ $report->id }})"
                                                            class="p-1.5 text-gray-500 hover:text-gold-600 hover:bg-gold-50 dark:hover:bg-gold-900/20 rounded transition">
                                                            <x-heroicon-o-eye class="w-4 h-4" />
                                                        </button>
                                                        <button type="button" wire:click.stop="confirmDelete({{ $report->id }})"
                                                            class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition">
                                                            <x-heroicon-o-trash class="w-4 h-4" />
                                                        </button>
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
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Belum ada laporan progress
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">Mulai catat progress pekerjaan proyek Anda.</p>
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
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit="save">
                        <div
                            class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6 max-h-[80vh] overflow-y-auto scrollbar-overlay">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Tambah Laporan Progress</h3>
                                <button type="button" wire:click="closeModal"
                                    class="text-gray-400 hover:text-gray-500"><x-heroicon-o-x-circle
                                        class="w-6 h-6" /></button>
                            </div>

                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="reportDate" value="Tanggal Laporan" />
                                        <x-text-input wire:model="reportDate" id="reportDate" type="date"
                                            class="mt-1 block w-full" required />
                                    </div>
                                    <div>
                                        <x-input-label for="rabItemId" value="Pekerjaan (RAB Item)" />
                                        <select wire:model="rabItemId" id="rabItemId"
                                            class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 rounded-md shadow-sm">
                                            <option value="">-- Umum / Tidak spesifik --</option>
                                            @foreach($rabItems as $rabItem)
                                                <option value="{{ $rabItem->id }}">{{ $rabItem->work_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="progressPercentage" value="Progress (%)" />
                                    <x-text-input wire:model="progressPercentage" id="progressPercentage" type="number"
                                        step="0.01" min="0" max="100" class="mt-1 block w-full" required />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="weather" value="Cuaca" />
                                        <select wire:model="weather" id="weather"
                                            class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 rounded-md shadow-sm">
                                            <option value="">-- Pilih Cuaca --</option>
                                            @foreach($weatherOptions as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label for="workerCount" value="Jumlah Pekerja" />
                                        <x-text-input wire:model="workerCount" id="workerCount" type="number" min="0"
                                            class="mt-1 block w-full" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="description" value="Deskripsi Pekerjaan" />
                                    <textarea wire:model="description" id="description" rows="2"
                                        class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 rounded-md shadow-sm"></textarea>
                                </div>

                                <div>
                                    <x-input-label for="issues" value="Kendala/Masalah" />
                                    <textarea wire:model="issues" id="issues" rows="2"
                                        class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500 rounded-md shadow-sm"></textarea>
                                </div>

                                <div>
                                    <x-input-label for="photos" value="Foto Dokumentasi (Max 5)" />
                                    <input wire:model="photos" id="photos" type="file" multiple accept="image/*"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">

                                    {{-- Loading indicator --}}
                                    <div wire:loading wire:target="photos" class="mt-2 text-sm text-blue-600">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Mengupload foto...
                                    </div>

                                    {{-- Photo preview --}}
                                    @if($photos && count($photos) > 0)
                                        <div class="mt-3 grid grid-cols-5 gap-2">
                                            @foreach($photos as $photo)
                                                @if(method_exists($photo, 'temporaryUrl'))
                                                    <img src="{{ $photo->temporaryUrl() }}"
                                                        class="w-full h-16 object-cover rounded border">
                                                @endif
                                            @endforeach
                                        </div>
                                        <p class="mt-1 text-xs text-green-600">{{ count($photos) }} foto siap diupload</p>
                                    @endif

                                    <x-input-error :messages="$errors->get('photos')" class="mt-2" />
                                    <x-input-error :messages="$errors->get('photos.*')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-dark-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <x-primary-button type="submit" class="sm:ml-3"
                                wire:loading.attr="disabled">Simpan</x-primary-button>
                            <button type="button" wire:click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Detail Modal --}}
    @if($showDetailModal && $selectedReport)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeDetailModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-gold-500 to-gold-600 px-6 py-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold text-white">{{ $selectedReport->report_date->format('d F Y') }}
                                </h3>
                                <p class="text-gold-100 text-sm">{{ $selectedReport->rabItem->work_name ?? 'Umum' }}</p>
                            </div>
                            <button wire:click="closeDetailModal" class="text-white/80 hover:text-white transition">
                                <x-heroicon-o-x-circle class="w-6 h-6" />
                            </button>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Stats Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                            <div class="bg-gray-50 dark:bg-dark-700 rounded-lg p-3 text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Progress</p>
                                <p class="text-2xl font-bold text-gold-600">{{ $selectedReport->progress_percentage }}%</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-dark-700 rounded-lg p-3 text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Kumulatif</p>
                                <p class="text-2xl font-bold text-blue-600">
                                    {{ $selectedReport->cumulative_progress ?? $selectedReport->progress_percentage }}%
                                </p>
                            </div>
                            <div class="bg-gray-50 dark:bg-dark-700 rounded-lg p-3 text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Cuaca</p>
                                @php $weatherIcons = ['sunny' => '☀️', 'cloudy' => '☁️', 'rainy' => '🌧️', 'stormy' => '⛈️']; @endphp
                                <p class="text-2xl">{{ $weatherIcons[$selectedReport->weather] ?? '-' }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-dark-700 rounded-lg p-3 text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Pekerja</p>
                                <p class="text-2xl font-bold text-gray-700 dark:text-gray-300">
                                    {{ $selectedReport->workers_count ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($selectedReport->description)
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Deskripsi Pekerjaan</h4>
                                <p class="text-gray-900 dark:text-white whitespace-pre-line">{{ $selectedReport->description }}
                                </p>
                            </div>
                        @endif

                        <!-- Issues -->
                        @if($selectedReport->issues)
                            <div
                                class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                                <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-300 mb-2 flex items-center">
                                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 mr-2" />
                                    Kendala/Masalah
                                </h4>
                                <p class="text-yellow-700 dark:text-yellow-400 whitespace-pre-line text-sm">
                                    {{ $selectedReport->issues }}
                                </p>
                            </div>
                        @endif

                        <!-- Photos with Lightbox -->
                        @php $photoUrls = $selectedReport->precomputed_photo_urls ?? $selectedReport->photo_urls ?? []; @endphp
                        @if(count($photoUrls) > 0)
                            <div class="mb-6" x-data="{ 
                                        lightboxOpen: false, 
                                        currentIndex: 0,
                                        photos: {{ json_encode($photoUrls) }},
                                        loaded: [],
                                        open(index) {
                                            this.currentIndex = index;
                                            this.lightboxOpen = true;
                                        },
                                        close() {
                                            this.lightboxOpen = false;
                                        },
                                        next() {
                                            this.currentIndex = (this.currentIndex + 1) % this.photos.length;
                                        },
                                        prev() {
                                            this.currentIndex = (this.currentIndex - 1 + this.photos.length) % this.photos.length;
                                        },
                                        markLoaded(index) {
                                            this.loaded[index] = true;
                                        }
                                    }" @keydown.escape.window="lightboxOpen = false"
                                @keydown.arrow-right.window="if(lightboxOpen) next()"
                                @keydown.arrow-left.window="if(lightboxOpen) prev()">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Foto Dokumentasi</h4>
                                <div class="grid grid-cols-3 gap-3">
                                    @foreach($photoUrls as $index => $photoUrl)
                                        <button type="button" @click="open({{ $index }})"
                                            class="relative block aspect-square rounded-lg overflow-hidden hover:opacity-90 hover:ring-2 hover:ring-gold-500 transition focus:outline-none focus:ring-2 focus:ring-gold-500 bg-gray-200 dark:bg-dark-600">
                                            <!-- Skeleton loader -->
                                            <div x-show="!loaded[{{ $index }}]" class="absolute inset-0 animate-pulse bg-gray-300 dark:bg-dark-500"></div>
                                            <img src="{{ $photoUrl }}" 
                                                alt="Progress photo {{ $index + 1 }}"
                                                loading="lazy"
                                                @load="markLoaded({{ $index }})"
                                                :class="loaded[{{ $index }}] ? 'opacity-100' : 'opacity-0'"
                                                class="w-full h-full object-cover transition-opacity duration-300">
                                        </button>
                                    @endforeach
                                </div>

                                <!-- Lightbox Modal -->
                                <div x-show="lightboxOpen" x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4"
                                    @click.self="close()" style="display: none;">

                                    <!-- Close Button -->
                                    <button @click="close()"
                                        class="absolute top-4 right-4 z-10 p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-full transition">
                                        <x-heroicon-o-x-mark class="w-8 h-8" />
                                    </button>

                                    <!-- Previous Button -->
                                    <button @click="prev()" x-show="photos.length > 1"
                                        class="absolute left-4 z-10 p-3 text-white/80 hover:text-white hover:bg-white/10 rounded-full transition">
                                        <x-heroicon-o-chevron-left class="w-8 h-8" />
                                    </button>

                                    <!-- Image Container -->
                                    <div class="max-w-full max-h-full flex items-center justify-center">
                                        <img :src="photos[currentIndex]" alt="Progress photo full size"
                                            class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl">
                                    </div>

                                    <!-- Next Button -->
                                    <button @click="next()" x-show="photos.length > 1"
                                        class="absolute right-4 z-10 p-3 text-white/80 hover:text-white hover:bg-white/10 rounded-full transition">
                                        <x-heroicon-o-chevron-right class="w-8 h-8" />
                                    </button>

                                    <!-- Image Counter -->
                                    <div
                                        class="absolute bottom-4 left-1/2 -translate-x-1/2 px-4 py-2 bg-black/50 text-white text-sm rounded-full">
                                        <span x-text="currentIndex + 1"></span> / <span x-text="photos.length"></span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Reporter -->
                        <div
                            class="pt-4 border-t dark:border-dark-700 text-sm text-gray-500 dark:text-gray-400 flex items-center">
                            <x-heroicon-o-user class="w-4 h-4 mr-2" />
                            Dilaporkan oleh <span
                                class="font-medium text-gray-900 dark:text-white ml-1">{{ $selectedReport->reporter->name ?? 'Unknown' }}</span>
                            <span class="ml-1">pada {{ $selectedReport->created_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-dark-700 px-6 py-4 flex justify-end space-x-3">
                        <button wire:click="closeDetailModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-600 rounded-lg transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeDeleteModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div
                    class="relative inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" wire:click="closeDeleteModal" class="text-gray-400 hover:text-gray-500">
                            <x-heroicon-o-x-circle class="w-6 h-6" />
                        </button>
                    </div>
                    <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Hapus Laporan Progress</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Yakin ingin menghapus laporan ini?
                                    Foto yang terkait juga akan dihapus.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-dark-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
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