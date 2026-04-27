<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Master Supplier', 'url' => route('suppliers.index')],
        ['label' => 'Tambah Supplier']
    ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Supplier') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('suppliers.store') }}" class="p-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="code" :value="__('Kode Supplier')" />
                            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full"
                                :value="old('code')" required autofocus placeholder="SUP-XXX" />
                            <x-input-error class="mt-2" :messages="$errors->get('code')" />
                        </div>

                        <div>
                            <x-input-label for="name" :value="__('Nama Supplier')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="contact_person" :value="__('Nama Kontak')" />
                            <x-text-input id="contact_person" name="contact_person" type="text"
                                class="mt-1 block w-full" :value="old('contact_person')" />
                            <x-input-error class="mt-2" :messages="$errors->get('contact_person')" />
                        </div>

                        <div>
                            <x-input-label for="phone" :value="__('Telepon')" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                                :value="old('phone')" />
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                :value="old('email')" />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div>
                            <x-input-label for="city" :value="__('Kota')" />
                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full"
                                :value="old('city')" />
                            <x-input-error class="mt-2" :messages="$errors->get('city')" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-input-label for="address" :value="__('Alamat')" />
                        <textarea id="address" name="address" rows="2"
                            class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">{{ old('address') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('address')" />
                    </div>

                    <div class="mt-6">
                        <x-input-label for="notes" :value="__('Catatan')" />
                        <textarea id="notes" name="notes" rows="2"
                            class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('suppliers.index') }}"
                            class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 mr-4">Batal</a>
                        <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>


