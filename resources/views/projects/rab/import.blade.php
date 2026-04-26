<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Import RAB - {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('projects.rab.process-import', $project) }}"
                    enctype="multipart/form-data">
                    @csrf

                    <!-- Instructions -->
                    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                        <h3 class="font-semibold text-blue-800 dark:text-blue-300 mb-3">
                            <x-heroicon-o-information-circle class="w-5 h-5 inline mr-1" />
                            Petunjuk Import RAB
                        </h3>

                        <p class="text-sm text-blue-700 dark:text-blue-400 mb-4">
                            File Excel/CSV harus memiliki kolom header di baris pertama. Berikut kolom yang diterima:
                        </p>

                        <!-- Column Requirements Table -->
                        <div class="overflow-x-auto mb-4">
                            <table class="min-w-full text-sm border border-blue-200 dark:border-blue-700 rounded">
                                <thead class="bg-blue-100 dark:bg-blue-900/50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-blue-800 dark:text-blue-300">Kolom</th>
                                        <th class="px-3 py-2 text-left text-blue-800 dark:text-blue-300">Alias</th>
                                        <th class="px-3 py-2 text-center text-blue-800 dark:text-blue-300">Wajib</th>
                                        <th class="px-3 py-2 text-left text-blue-800 dark:text-blue-300">Contoh</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-blue-200 dark:divide-blue-700">
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-blue-800 dark:text-blue-300">section_code
                                        </td>
                                        <td class="px-3 py-2 text-blue-700 dark:text-blue-400">kode_bagian, kode_section
                                        </td>
                                        <td class="px-3 py-2 text-center"><span class="text-yellow-600">○</span></td>
                                        <td class="px-3 py-2 text-blue-700 dark:text-blue-400">A, B, I, II</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-blue-800 dark:text-blue-300">section_name
                                        </td>
                                        <td class="px-3 py-2 text-blue-700 dark:text-blue-400">nama_bagian, section</td>
                                        <td class="px-3 py-2 text-center"><span class="text-yellow-600">○</span></td>
                                        <td class="px-3 py-2 text-blue-700 dark:text-blue-400">Pekerjaan Persiapan</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-blue-800 dark:text-blue-300">work_name
                                        </td>
                                        <td class="px-3 py-2 text-blue-700 dark:text-blue-400">nama_pekerjaan, uraian
                                        </td>
                                        <td class="px-3 py-2 text-center"><span
                                                class="text-green-600 font-bold">✓</span></td>
                                        <td class="px-3 py-2 text-blue-700 dark:text-blue-400">Pasang Bekisting</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-blue-800 dark:text-blue-300">volume</td>
                                        <td class="px-3 py-2 text-blue-700 dark:text-blue-400">vol</td>
                                        <td class="px-3 py-2 text-center"><span
                                                class="text-green-600 font-bold">✓</span></td>
                                        <td class="px-3 py-2 text-blue-700 dark:text-blue-400">100.5</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-blue-800 dark:text-blue-300">unit</td>
                                        <td class="px-3 py-2 text-blue-700 dark:text-blue-400">satuan, sat</td>
                                        <td class="px-3 py-2 text-center"><span
                                                class="text-green-600 font-bold">✓</span></td>
                                        <td class="px-3 py-2 text-blue-700 dark:text-blue-400">m2, m3, kg, ls</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-blue-800 dark:text-blue-300">unit_price
                                        </td>
                                        <td class="px-3 py-2 text-blue-700 dark:text-blue-400">harga_satuan, harga</td>
                                        <td class="px-3 py-2 text-center"><span
                                                class="text-green-600 font-bold">✓</span></td>
                                        <td class="px-3 py-2 text-blue-700 dark:text-blue-400">150000</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-blue-800 dark:text-blue-300">item_code
                                        </td>
                                        <td class="px-3 py-2 text-blue-700 dark:text-blue-400">kode_item, kode</td>
                                        <td class="px-3 py-2 text-center"><span class="text-gray-400">-</span></td>
                                        <td class="px-3 py-2 text-blue-700 dark:text-blue-400">A.1, B.2.1</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="text-xs text-blue-600 dark:text-blue-400 space-y-1">
                            <p><span class="text-green-600 font-bold">✓</span> = Wajib diisi, <span
                                    class="text-yellow-600">○</span> = Disarankan, <span class="text-gray-400">-</span>
                                = Opsional</p>
                            <p>💡 <strong>Tips:</strong> Baris tanpa volume akan dianggap sebagai header bagian
                                (section).</p>
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div class="mb-6">
                        <x-input-label for="file" :value="__('File Excel (.xlsx, .xls, .csv)')" />
                        <input type="file" id="file" name="file" accept=".xlsx,.xls,.csv" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-600 file:text-white
                            hover:file:bg-blue-700
                            cursor-pointer" required />
                        <x-input-error class="mt-2" :messages="$errors->get('file')" />
                    </div>

                    <!-- Clear Existing -->
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="clear_existing" value="1"
                                class="rounded dark:bg-dark-900 border-gray-300 dark:border-dark-700 text-blue-600 shadow-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:focus:ring-offset-gray-800">
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Hapus data RAB yang sudah ada
                                sebelum import</span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Centang ini jika Anda ingin mengganti
                            semua data RAB dengan data baru dari file.</p>
                    </div>

                    <div class="flex items-center justify-end">
                        <a href="{{ route('projects.rab.index', $project) }}"
                            class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 mr-4">
                            Batal
                        </a>
                        <x-primary-button>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            {{ __('Import File') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>