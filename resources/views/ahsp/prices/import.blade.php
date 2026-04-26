<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Master AHSP', 'url' => route('ahsp.index')],
        ['label' => 'Harga Satuan Dasar', 'url' => route('ahsp.prices.index')],
        ['label' => 'Import']
    ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Import Harga Satuan Dasar') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('ahsp.prices.import.process') }}" method="POST" enctype="multipart/form-data"
                        x-data="{ 
                            uploading: false, 
                            progress: 0,
                            simulateProgress() {
                                this.uploading = true;
                                this.progress = 0;
                                let interval = setInterval(() => {
                                    if (this.progress < 90) {
                                        this.progress += Math.floor(Math.random() * 5) + 1;
                                    } else {
                                        clearInterval(interval);
                                    }
                                }, 300);
                            }
                        }" @submit="simulateProgress()">
                        @csrf

                        <!-- Progress Overlay -->
                        <div x-show="uploading" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 backdrop-blur-sm"
                            style="display: none;">
                            <div
                                class="bg-white dark:bg-gray-800 rounded-xl p-8 shadow-2xl flex flex-col items-center max-w-sm w-full mx-4">
                                <!-- Circular Progress -->
                                <div class="relative w-32 h-32 mb-4">
                                    <svg class="w-full h-full transform -rotate-90">
                                        <!-- Background Circle -->
                                        <circle cx="64" cy="64" r="56" class="text-gray-200 dark:text-gray-700"
                                            stroke="currentColor" stroke-width="12" fill="none" />
                                        <!-- Progress Circle -->
                                        <circle cx="64" cy="64" r="56"
                                            class="text-indigo-600 dark:text-indigo-500 transition-all duration-300 ease-out"
                                            stroke="currentColor" stroke-width="12" fill="none"
                                            :stroke-dasharray="2 * Math.PI * 56"
                                            :stroke-dashoffset="2 * Math.PI * 56 * (1 - progress / 100)"
                                            stroke-linecap="round" />
                                    </svg>
                                    <!-- Percentage Text -->
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="text-2xl font-bold text-gray-800 dark:text-white"
                                            x-text="progress + '%'"></span>
                                    </div>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Mengimport Data...
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 text-center">Mohon tunggu, jangan
                                    tutup halaman ini.</p>
                            </div>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">File
                                Excel</label>
                            <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Format: .xlsx, .xls, .csv. Maksimal
                                10MB.</p>
                        </div>

                        <!-- Region -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kode
                                    Wilayah</label>
                                <input type="text" name="region_code" required placeholder="Contoh: ID-JK"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama
                                    Wilayah</label>
                                <input type="text" name="region_name" placeholder="Contoh: DKI Jakarta"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            </div>
                        </div>

                        <!-- Effective Date -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal
                                Berlaku</label>
                            <input type="date" name="effective_date" required value="{{ date('Y-m-d') }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        </div>

                        <!-- Expected Format -->
                        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Format Kolom Excel
                                yang Diharapkan:</h4>
                            <ul class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                                <li>• <strong>no / kode</strong>: Nomor/Kode komponen (L.01, M.01, dll)</li>
                                <li>• <strong>uraian / nama</strong>: Nama bahan/upah/peralatan</li>
                                <li>• <strong>satuan / unit</strong>: Satuan (OH, kg, m3, liter, dll)</li>
                                <li>• <strong>harga / harga_satuan</strong>: Harga dalam Rupiah</li>
                                <li>• <strong>kategori / category</strong> (opsional): Kategori bahan (misal: Semen,
                                    Kayu)</li>
                                <li>• <strong>tipe / jenis</strong> (opsional): labor/material/equipment</li>
                            </ul>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                <em>Sistem akan otomatis mendeteksi tipe komponen dari header baris (TENAGA KERJA,
                                    BAHAN, PERALATAN)</em>
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('ahsp.prices.index') }}"
                                class="px-4 py-2 bg-gray-200 dark:bg-gray-600 rounded-md text-gray-700 dark:text-gray-300">
                                Batal
                            </a>
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500">
                                Import Harga
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>