<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Master AHSP', 'url' => route('ahsp.index')],
        ['label' => $ahspWorkType->code, 'url' => route('ahsp.show', $ahspWorkType)],
        ['label' => 'Edit']
    ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit') }}: {{ $ahspWorkType->code }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('ahsp.update', $ahspWorkType) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Basic Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                                <select name="ahsp_category_id" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    @foreach($categories as $category)
                                        <option value="{{ $category['id'] }}" {{ $ahspWorkType->ahsp_category_id == $category['id'] ? 'selected' : '' }}>
                                            {{ $category['display_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode
                                    Pekerjaan</label>
                                <input type="text" name="code" value="{{ old('code', $ahspWorkType->code) }}" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama
                                Pekerjaan</label>
                            <input type="text" name="name" value="{{ old('name', $ahspWorkType->name) }}" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Satuan</label>
                                <input type="text" name="unit" value="{{ old('unit', $ahspWorkType->unit) }}" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sumber</label>
                                <select name="source"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    <option value="PUPR" {{ $ahspWorkType->source == 'PUPR' ? 'selected' : '' }}>PUPR
                                    </option>
                                    <option value="SNI" {{ $ahspWorkType->source == 'SNI' ? 'selected' : '' }}>SNI
                                    </option>
                                    <option value="BOW" {{ $ahspWorkType->source == 'BOW' ? 'selected' : '' }}>BOW
                                    </option>
                                    <option value="Custom" {{ $ahspWorkType->source == 'Custom' ? 'selected' : '' }}>
                                        Custom</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Overhead
                                    & Keuntungan (%)</label>
                                <input type="number" name="overhead_percentage"
                                    value="{{ old('overhead_percentage', $ahspWorkType->overhead_percentage) }}"
                                    step="0.01" min="0" max="100" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            </div>
                        </div>

                        <div class="mb-6">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Referensi</label>
                            <input type="text" name="reference" value="{{ old('reference', $ahspWorkType->reference) }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        </div>

                        <div class="mb-6">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
                            <textarea name="description" rows="2"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ old('description', $ahspWorkType->description) }}</textarea>
                        </div>

                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ $ahspWorkType->is_active ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Aktif</span>
                            </label>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('ahsp.show', $ahspWorkType) }}"
                                class="px-4 py-2 bg-gray-200 dark:bg-gray-600 rounded-md text-gray-700 dark:text-gray-300">
                                Batal
                            </a>
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>