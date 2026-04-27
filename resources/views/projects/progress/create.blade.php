<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Laporan Progress', 'url' => route('projects.progress.index', $project)],
        ['label' => 'Tambah Laporan']
    ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Tambah Laporan Progress - {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('projects.progress.store', $project) }}"
                    enctype="multipart/form-data" class="p-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="report_date" :value="__('Tanggal Laporan')" />
                            <x-text-input id="report_date" name="report_date" type="date" class="mt-1 block w-full"
                                :value="old('report_date', date('Y-m-d'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('report_date')" />
                        </div>

                        <div>
                            <x-input-label for="rab_item_id" :value="__('Item Pekerjaan (Opsional)')" />
                            <select id="rab_item_id" name="rab_item_id"
                                class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">
                                <option value="">-- Pilih Item --</option>
                                @foreach($rabItems as $item)
                                    <option value="{{ $item->id }}" {{ old('rab_item_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->section->code ?? '' }}. {{ $item->work_name }}
                                        ({{ number_format($item->actual_progress, 1) }}%)
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('rab_item_id')" />
                        </div>

                        <div>
                            <x-input-label for="progress_percentage" :value="__('Progress Hari Ini (%)')" />
                            <x-text-input id="progress_percentage" name="progress_percentage" type="number" step="0.1"
                                min="0" max="100" class="mt-1 block w-full" :value="old('progress_percentage', 0)"
                                required />
                            <x-input-error class="mt-2" :messages="$errors->get('progress_percentage')" />
                        </div>

                        <div>
                            <x-input-label for="weather" :value="__('Cuaca')" />
                            <select id="weather" name="weather"
                                class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">
                                @foreach($weatherOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('weather') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('weather')" />
                        </div>

                        <div x-data="{ 
                            labors: [
                                { name: 'Tukang', count: 0 },
                                { name: 'Pekerja', count: 0 },
                                { name: 'Mandor', count: 0 },
                                { name: 'Lainnya', count: 0 }
                            ],
                            get total() {
                                return this.labors.reduce((sum, l) => sum + (parseInt(l.count) || 0), 0);
                            }
                        }">
                            <x-input-label :value="__('Detail Tenaga Kerja')" />
                            <div class="grid grid-cols-2 gap-2 mt-1">
                                <template x-for="(labor, index) in labors" :key="index">
                                    <div class="flex items-center space-x-2">
                                        <input type="hidden" :name="'labor_details[' + labor.name + ']'" :value="labor.count">
                                        <div class="flex-1">
                                            <span class="text-xs text-gray-500" x-text="labor.name"></span>
                                            <input type="number" x-model="labor.count" min="0"
                                                class="block w-full text-sm border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 rounded-md shadow-sm">
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="mt-2 text-xs font-bold text-gray-700 dark:text-gray-400">
                                Total: <span x-text="total"></span> orang
                                <input type="hidden" name="workers_count" :value="total">
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('workers_count')" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-input-label for="description" :value="__('Deskripsi Pekerjaan')" />
                        <textarea id="description" name="description" rows="3"
                            class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">{{ old('description') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div class="mt-6">
                        <x-input-label for="issues" :value="__('Kendala/Masalah')" />
                        <textarea id="issues" name="issues" rows="2"
                            class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">{{ old('issues') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('issues')" />
                    </div>

                    <div class="mt-6">
                        <x-input-label for="photos" :value="__('Foto Dokumentasi (max 5 foto)')" />
                        <input type="file" id="photos" name="photos[]" multiple accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-600 file:text-white
                            hover:file:bg-blue-700
                            cursor-pointer" />
                        <x-input-error class="mt-2" :messages="$errors->get('photos')" />
                        <x-input-error class="mt-2" :messages="$errors->get('photos.*')" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('projects.progress.index', $project) }}"
                            class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 mr-4">Batal</a>
                        <x-primary-button>{{ __('Simpan Laporan') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>


