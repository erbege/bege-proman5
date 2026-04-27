<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Item RAB - {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('projects.rab.items.update', [$project, $item]) }}" class="p-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="rab_section_id" :value="__('Bagian Pekerjaan')" />
                            <select id="rab_section_id" name="rab_section_id"
                                class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm"
                                required>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}" {{ old('rab_section_id', $item->rab_section_id) == $section->id ? 'selected' : '' }}>
                                        {{ $section->code }}. {{ $section->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('rab_section_id')" />
                        </div>

                        <div>
                            <x-input-label for="code" :value="__('Kode Item (Opsional)')" />
                            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full"
                                :value="old('code', $item->code)" />
                            <x-input-error class="mt-2" :messages="$errors->get('code')" />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="work_name" :value="__('Nama Pekerjaan')" />
                            <x-text-input id="work_name" name="work_name" type="text" class="mt-1 block w-full"
                                :value="old('work_name', $item->work_name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('work_name')" />
                        </div>

                        <div>
                            <x-input-label for="volume" :value="__('Volume')" />
                            <x-text-input id="volume" name="volume" type="number" step="0.0001" min="0"
                                class="mt-1 block w-full" :value="old('volume', $item->volume)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('volume')" />
                        </div>

                        <div>
                            <x-input-label for="unit" :value="__('Satuan')" />
                            <x-text-input id="unit" name="unit" type="text" class="mt-1 block w-full"
                                :value="old('unit', $item->unit)" required placeholder="m3, m2, kg, bh" />
                            <x-input-error class="mt-2" :messages="$errors->get('unit')" />
                        </div>

                        <div>
                            <x-input-label for="unit_price" :value="__('Harga Satuan (Rp)')" />
                            <x-text-input id="unit_price" name="unit_price" type="number" step="0.01" min="0.00"
                                class="mt-1 block w-full" :value="old('unit_price', $item->unit_price)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('unit_price')" />
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Harga</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">
                                {{ $item->formatted_total_price }}
                            </p>
                        </div>

                        <div>
                            <x-input-label for="planned_start" :value="__('Tanggal Mulai Rencana')" />
                            <x-text-input id="planned_start" name="planned_start" type="date" class="mt-1 block w-full"
                                :value="old('planned_start', $item->planned_start?->format('Y-m-d'))" />
                            <x-input-error class="mt-2" :messages="$errors->get('planned_start')" />
                        </div>

                        <div>
                            <x-input-label for="planned_end" :value="__('Tanggal Selesai Rencana')" />
                            <x-text-input id="planned_end" name="planned_end" type="date" class="mt-1 block w-full"
                                :value="old('planned_end', $item->planned_end?->format('Y-m-d'))" />
                            <x-input-error class="mt-2" :messages="$errors->get('planned_end')" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-input-label for="description" :value="__('Deskripsi')" />
                        <textarea id="description" name="description" rows="2"
                            class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">{{ old('description', $item->description) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('projects.rab.index', $project) }}"
                            class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 mr-4">Batal</a>
                        <x-primary-button>{{ __('Simpan Perubahan') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>


