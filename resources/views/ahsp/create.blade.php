<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Master AHSP', 'url' => route('ahsp.index')],
        ['label' => 'Tambah AHSP']
    ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Jenis Pekerjaan AHSP') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('ahsp.store') }}" method="POST">
                        @csrf

                        <!-- Basic Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                                <select name="ahsp_category_id" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category['id'] }}" {{ old('ahsp_category_id') == $category['id'] ? 'selected' : '' }}>
                                            {{ $category['display_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('ahsp_category_id') <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode
                                    Pekerjaan</label>
                                <input type="text" name="code" value="{{ old('code') }}" required placeholder="1.1.1.1"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama
                                Pekerjaan</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                placeholder="Pembuatan 1 m' pagar sementara dari kayu tinggi 2 meter"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Satuan</label>
                                <input type="text" name="unit" value="{{ old('unit') }}" required
                                    placeholder="m', m2, m3, kg, ls"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                @error('unit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sumber</label>
                                <select name="source"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    <option value="PUPR" {{ old('source') == 'PUPR' ? 'selected' : '' }}>PUPR</option>
                                    <option value="SNI" {{ old('source') == 'SNI' ? 'selected' : '' }}>SNI</option>
                                    <option value="BOW" {{ old('source') == 'BOW' ? 'selected' : '' }}>BOW</option>
                                    <option value="Custom" {{ old('source') == 'Custom' ? 'selected' : '' }}>Custom
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Overhead
                                    & Keuntungan (%)</label>
                                <input type="number" name="overhead_percentage"
                                    value="{{ old('overhead_percentage', 10) }}" step="0.01" min="0" max="100" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                @error('overhead_percentage') <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Referensi
                                (opsional)</label>
                            <input type="text" name="reference" value="{{ old('reference') }}"
                                placeholder="SE Dirjen Binkon No 128/SE/Dk/2025"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi
                                (opsional)</label>
                            <textarea name="description" rows="2"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ old('description') }}</textarea>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('ahsp.index') }}"
                                class="px-4 py-2 bg-gray-200 dark:bg-gray-600 rounded-md text-gray-700 dark:text-gray-300">
                                Batal
                            </a>
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Box -->
            <div class="mt-6 bg-blue-50 dark:bg-blue-900/30 rounded-lg p-4">
                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">Tambah Komponen</h4>
                <p class="text-xs text-blue-600 dark:text-blue-300">
                    Setelah menyimpan jenis pekerjaan, Anda dapat menambahkan komponen (Tenaga Kerja, Bahan, Peralatan)
                    di halaman detail AHSP.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>