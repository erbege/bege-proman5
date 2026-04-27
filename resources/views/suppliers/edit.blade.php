<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Master Supplier', 'url' => route('suppliers.index')],
        ['label' => $supplier->name, 'url' => route('suppliers.edit', $supplier)],
        ['label' => 'Edit']
    ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Supplier') }} - {{ $supplier->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="p-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="code" :value="__('Kode Supplier')" />
                            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full"
                                :value="old('code', $supplier->code)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('code')" />
                        </div>

                        <div>
                            <x-input-label for="name" :value="__('Nama Supplier')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name', $supplier->name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="contact_person" :value="__('Nama Kontak')" />
                            <x-text-input id="contact_person" name="contact_person" type="text"
                                class="mt-1 block w-full" :value="old('contact_person', $supplier->contact_person)" />
                            <x-input-error class="mt-2" :messages="$errors->get('contact_person')" />
                        </div>

                        <div>
                            <x-input-label for="phone" :value="__('Telepon')" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                                :value="old('phone', $supplier->phone)" />
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                :value="old('email', $supplier->email)" />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div>
                            <x-input-label for="city" :value="__('Kota')" />
                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full"
                                :value="old('city', $supplier->city)" />
                            <x-input-error class="mt-2" :messages="$errors->get('city')" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-input-label for="address" :value="__('Alamat')" />
                        <textarea id="address" name="address" rows="2"
                            class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">{{ old('address', $supplier->address) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('address')" />
                    </div>

                    <div class="mt-6">
                        <x-input-label for="notes" :value="__('Catatan')" />
                        <textarea id="notes" name="notes" rows="2"
                            class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">{{ old('notes', $supplier->notes) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    <div class="mt-6">
                        <label class="flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1"
                                class="rounded dark:bg-dark-900 border-gray-300 dark:border-dark-700 text-blue-600 shadow-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:focus:ring-offset-gray-800"
                                {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Supplier Aktif</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        <button type="button"
                            onclick="document.getElementById('deleteSupplierModal').classList.remove('hidden')"
                            class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">Hapus Supplier</button>
                        <div>
                            <a href="{{ route('suppliers.index') }}"
                                class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 mr-4">Batal</a>
                            <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirm-modal id="deleteSupplierModal" title="Hapus Supplier"
        message="Apakah Anda yakin ingin menghapus supplier ini?" confirmText="Ya, Hapus" confirmColor="red"
        icon="trash">
        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                Ya, Hapus
            </button>
        </form>
    </x-confirm-modal>
</x-app-layout>


