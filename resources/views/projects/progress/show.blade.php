<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Laporan Progress
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $report->report_date->format('d M Y') }}</p>
            </div>
            <a href="{{ route('projects.progress.index', $project) }}"
                class="text-gray-600 hover:text-gray-800 dark:text-gray-400">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <!-- Header Info -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 pb-6 border-b dark:border-dark-700">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ $report->report_date->format('d M Y') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Progress</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ number_format($report->progress_percentage, 1) }}%
                        </p>
                    </div>
                    @if($report->weather)
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Cuaca</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $report->weather_label }}</p>
                        </div>
                    @endif
                    @if($report->workers_count)
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tenaga Kerja</p>
                            <div class="flex items-center">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $report->workers_count }} orang</p>
                                @if($report->labor_details)
                                    <div x-data="{ open: false }" class="relative ml-1 inline-block">
                                        <button @mouseenter="open = true" @mouseleave="open = false" type="button" class="text-gray-400 hover:text-gray-600">
                                            <x-heroicon-s-information-circle class="w-4 h-4" />
                                        </button>
                                        <div x-show="open" x-cloak class="absolute z-20 w-32 p-2 bg-gray-900 text-white text-[10px] rounded shadow-lg -top-2 left-6">
                                            <p class="font-bold border-b border-gray-700 mb-1 pb-1">Rincian:</p>
                                            @foreach($report->labor_details as $type => $count)
                                                @if($count > 0)
                                                    <div class="flex justify-between">
                                                        <span>{{ $type }}:</span>
                                                        <span>{{ $count }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Work Item -->
                @if($report->rabItem)
                    <div class="mb-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Item Pekerjaan</p>
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ $report->rabItem->section->code ?? '' }}. {{ $report->rabItem->work_name }}
                        </p>
                        @if($report->cumulative_progress !== null)
                            <div class="mt-2">
                                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                    <span>Progress Kumulatif</span>
                                    <span>{{ number_format($report->cumulative_progress, 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full"
                                        style="width: {{ $report->cumulative_progress }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Description -->
                @if($report->description)
                    <div class="mb-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Deskripsi Pekerjaan</p>
                        <p class="text-gray-900 dark:text-white whitespace-pre-line">{{ $report->description }}</p>
                    </div>
                @endif

                <!-- Issues -->
                @if($report->issues)
                    <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg">
                        <p class="text-sm text-yellow-800 dark:text-yellow-300 font-medium mb-1">Kendala/Masalah</p>
                        <p class="text-yellow-700 dark:text-yellow-400 whitespace-pre-line">{{ $report->issues }}</p>
                    </div>
                @endif

                <!-- Photos with Lightbox -->
                @if($report->photo_urls && count($report->photo_urls) > 0)
                    <div class="mb-6" x-data="{ 
                            lightboxOpen: false, 
                            currentIndex: 0,
                            photos: {{ Js::from($report->photo_urls) }},
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
                            }
                        }" @keydown.escape.window="lightboxOpen = false"
                        @keydown.arrow-right.window="if(lightboxOpen) next()"
                        @keydown.arrow-left.window="if(lightboxOpen) prev()">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Foto Dokumentasi</p>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($report->photo_urls as $index => $photoUrl)
                                <button type="button" @click="open({{ $index }})"
                                    class="block focus:outline-none focus:ring-2 focus:ring-gold-500 rounded-lg">
                                    <img src="{{ $photoUrl }}" alt="Progress photo {{ $index + 1 }}"
                                        class="w-full h-48 object-cover rounded-lg hover:opacity-90 hover:ring-2 hover:ring-gold-500 transition">
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
                <div class="pt-4 border-t dark:border-dark-700 text-sm text-gray-500 dark:text-gray-400">
                    Dilaporkan oleh <span
                        class="font-medium text-gray-900 dark:text-white">{{ $report->reporter->name }}</span>
                    pada {{ $report->created_at->format('d M Y H:i') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>