<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Master Material', 'url' => route('materials.index')],
        ['label' => $material->name, 'url' => route('materials.edit', $material)],
        ['label' => 'Edit']
    ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Material') }} - {{ $material->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('materials.update', $material) }}" class="p-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="code" :value="__('Kode Material')" />
                            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full"
                                :value="old('code', $material->code)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('code')" />
                        </div>

                        <div>
                            <x-input-label for="name" :value="__('Nama Material')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name', $material->name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="category" :value="__('Kategori')" />
                            <x-text-input id="category" name="category" type="text" class="mt-1 block w-full"
                                :value="old('category', $material->category)" required list="categories" />
                            <datalist id="categories">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                            <x-input-error class="mt-2" :messages="$errors->get('category')" />
                        </div>

                        <div>
                            <x-input-label for="unit" :value="__('Satuan')" />
                            <select id="unit" name="unit"
                                class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm"
                                required>
                                @foreach($units as $unit)
                                    <option value="{{ $unit }}" {{ old('unit', $material->unit) === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('unit')" />
                        </div>

                        <div>
                            <x-input-label for="unit_price" :value="__('Harga Satuan (Rp)')" />
                            <x-text-input id="unit_price" name="unit_price" type="number" step="0.01" min="0"
                                class="mt-1 block w-full" :value="old('unit_price', $material->unit_price)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('unit_price')" />
                        </div>

                        <div>
                            <x-input-label for="minimum_stock" :value="__('Stok Minimum')" />
                            <x-text-input id="minimum_stock" name="minimum_stock" type="number" step="0.01" min="0"
                                class="mt-1 block w-full" :value="old('minimum_stock', $material->minimum_stock)" />
                            <x-input-error class="mt-2" :messages="$errors->get('minimum_stock')" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-input-label for="description" :value="__('Deskripsi')" />
                        <textarea id="description" name="description" rows="2"
                            class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">{{ old('description', $material->description) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div class="mt-6">
                        <label class="flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1"
                                class="rounded dark:bg-dark-900 border-gray-300 dark:border-dark-700 text-blue-600 shadow-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:focus:ring-offset-gray-800"
                                {{ old('is_active', $material->is_active) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Material Aktif</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        <button type="button"
                            onclick="document.getElementById('deleteMaterialModal').classList.remove('hidden')"
                            class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">Hapus Material</button>
                        <div>
                            <a href="{{ route('materials.index') }}"
                                class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 mr-4">Batal</a>
                            <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirm-modal id="deleteMaterialModal" title="Hapus Material"
        message="Apakah Anda yakin ingin menghapus material ini?" confirmText="Ya, Hapus" confirmColor="red"
        icon="trash">
        <form action="{{ route('materials.destroy', $material) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                Ya, Hapus
            </button>
        </form>
    </x-confirm-modal>
</x-app-layout>


