<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Edit Proyek']
    ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Proyek') }} - {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('projects.update', $project) }}" class="p-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="code" :value="__('Kode Proyek')" />
                            <x-text-input id="code" name="code" type="text"
                                class="mt-1 block w-full bg-gray-100 dark:bg-dark-700 cursor-not-allowed"
                                :value="$project->code" readonly />
                        </div>

                        <div>
                            <x-input-label for="name" :value="__('Nama Proyek')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name', $project->name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="client_name" :value="__('Nama Klien')" />
                            <x-text-input id="client_name" name="client_name" type="text" class="mt-1 block w-full"
                                :value="old('client_name', $project->client_name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('client_name')" />
                        </div>

                        <div>
                            <x-input-label for="type" :value="__('Tipe Proyek')" />
                            <select id="type" name="type"
                                class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">
                                @foreach($types as $value => $label)
                                    <option value="{{ $value }}" {{ old('type', $project->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('type')" />
                        </div>

                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status"
                                class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', $project->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('status')" />
                        </div>

                        <div>
                            <x-input-label for="contract_value" :value="__('Nilai Kontrak (Rp)')" />
                            <x-text-input id="contract_value" name="contract_value" type="number" step="0.01" min="0"
                                class="mt-1 block w-full" :value="old('contract_value', $project->contract_value)"
                                required />
                            <x-input-error class="mt-2" :messages="$errors->get('contract_value')" />
                        </div>

                        <div>
                            <x-input-label for="start_date" :value="__('Tanggal Mulai')" />
                            <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full"
                                :value="old('start_date', $project->start_date->format('Y-m-d'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
                        </div>

                        <div>
                            <x-input-label for="end_date" :value="__('Tanggal Selesai')" />
                            <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full"
                                :value="old('end_date', $project->end_date->format('Y-m-d'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="location" :value="__('Lokasi')" />
                            <x-text-input id="location" name="location" type="text" class="mt-1 block w-full"
                                :value="old('location', $project->location)" />
                            <x-input-error class="mt-2" :messages="$errors->get('location')" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-input-label for="description" :value="__('Deskripsi')" />
                        <textarea id="description" name="description" rows="3"
                            class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">{{ old('description', $project->description) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div class="mt-6">
                        <x-input-label for="notes" :value="__('Catatan')" />
                        <textarea id="notes" name="notes" rows="2"
                            class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">{{ old('notes', $project->notes) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        <button type="button"
                            onclick="document.getElementById('deleteProjectModal').classList.remove('hidden')"
                            class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">Hapus Proyek</button>
                        <div>
                            <a href="{{ route('projects.show', $project) }}"
                                class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 mr-4">Batal</a>
                            <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <x-confirm-modal id="deleteProjectModal" title="Hapus Proyek"
        message="Apakah Anda yakin ingin menghapus proyek ini? Semua data terkait (RAB, Jadwal, Pembelian) akan ikut terhapus dan tidak dapat dikembalikan."
        confirmText="Ya, Hapus" confirmColor="red" icon="trash">
        <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                Ya, Hapus
            </button>
        </form>
    </x-confirm-modal>
</x-app-layout>