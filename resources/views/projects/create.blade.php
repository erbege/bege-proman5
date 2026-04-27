<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => 'Buat Proyek Baru']
    ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buat Proyek Baru') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('projects.store') }}" class="p-3">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <!-- Code -->
                        <div>
                            <x-input-label for="code" :value="__('Kode Proyek')" />
                            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full"
                                :value="old('code')" autofocus placeholder="Kosongkan untuk generate otomatis" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kosongkan untuk kode otomatis
                                (PRJ-XXXX)</p>
                            <x-input-error class="mt-2" :messages="$errors->get('code')" />
                        </div>

                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('Nama Proyek')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <!-- Client Name -->
                        <div>
                            <x-input-label for="client_name" :value="__('Nama Klien')" />
                            <x-text-input id="client_name" name="client_name" type="text" class="mt-1 block w-full"
                                :value="old('client_name')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('client_name')" />
                        </div>

                        <!-- Type -->
                        <div>
                            <x-input-label for="type" :value="__('Tipe Proyek')" />
                            <select id="type" name="type"
                                class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">
                                @foreach($types as $value => $label)
                                    <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>{{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('type')" />
                        </div>

                        <!-- Start Date -->
                        <div>
                            <x-input-label for="start_date" :value="__('Tanggal Mulai')" />
                            <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full"
                                :value="old('start_date')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
                        </div>

                        <!-- End Date -->
                        <div>
                            <x-input-label for="end_date" :value="__('Tanggal Selesai')" />
                            <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full"
                                :value="old('end_date')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
                        </div>

                        <!-- Contract Value -->
                        <div>
                            <x-input-label for="contract_value" :value="__('Nilai Kontrak (Rp)')" />
                            <x-text-input id="contract_value" name="contract_value" type="number" step="0.01" min="0"
                                class="mt-1 block w-full" :value="old('contract_value')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('contract_value')" />
                        </div>

                        <!-- Location -->
                        <div>
                            <x-input-label for="location" :value="__('Lokasi')" />
                            <x-text-input id="location" name="location" type="text" class="mt-1 block w-full"
                                :value="old('location')" />
                            <x-input-error class="mt-2" :messages="$errors->get('location')" />
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mt-6">
                        <x-input-label for="description" :value="__('Deskripsi')" />
                        <textarea id="description" name="description" rows="3"
                            class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">{{ old('description') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <!-- Notes -->
                    <div class="mt-6">
                        <x-input-label for="notes" :value="__('Catatan')" />
                        <textarea id="notes" name="notes" rows="2"
                            class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('projects.index') }}"
                            class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 mr-4">
                            Batal
                        </a>
                        <x-primary-button>
                            {{ __('Simpan Proyek') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>


