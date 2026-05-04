<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Proyek', 'url' => route('projects.index')],
            ['label' => $project->name, 'url' => route('projects.show', $project)],
            ['label' => 'Monthly Reports', 'url' => route('projects.monthly-reports.index', $project)],
            ['label' => 'Buat Baru']
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Buat Monthly Report - {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <form action="{{ route('projects.monthly-reports.store', $project) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="space-y-6">
                        <!-- Year, Month & Period -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tahun <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="year" value="{{ old('year', $nextPeriod['year']) }}" min="2000"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white focus:ring-primary-500 focus:border-primary-500"
                                    required>
                                @error('year')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Bulan <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="month" value="{{ old('month', $nextPeriod['month']) }}" min="1" max="12"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white focus:ring-primary-500 focus:border-primary-500"
                                    required>
                                @error('month')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tanggal Awal <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="period_start" value="{{ old('period_start', $period['start']->format('Y-m-d')) }}"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white focus:ring-primary-500 focus:border-primary-500"
                                    required>
                                @error('period_start')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tanggal Akhir <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="period_end" value="{{ old('period_end', $period['end']->format('Y-m-d')) }}"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white focus:ring-primary-500 focus:border-primary-500"
                                    required>
                                @error('period_end')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Cover Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Judul Cover
                            </label>
                            <input type="text" name="cover_title" value="{{ old('cover_title', 'Monthly Progress Report - ' . \Carbon\Carbon::createFromDate($nextPeriod['year'], $nextPeriod['month'], 1)->translatedFormat('F Y')) }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white focus:ring-primary-500 focus:border-primary-500"
                                placeholder="Monthly Progress Report">
                        </div>

                        <!-- Cover Image -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Cover Image
                            </label>
                            
                            <!-- Upload New -->
                            <div class="mb-4">
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Upload gambar baru:</label>
                                <input type="file" name="cover_image_upload" accept="image/*"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900 dark:file:text-primary-300">
                            </div>

                            <!-- Or Select from ProjectFiles -->
                            @if($projectImages->count() > 0)
                                <div>
                                    <label class="block text-xs text-gray-500 dark:text-gray-400 mb-2">Atau pilih dari Project Files:</label>
                                    <div class="grid grid-cols-4 gap-2 max-h-48 overflow-y-auto border rounded-lg p-2 dark:border-gray-600">
                                        @foreach($projectImages as $image)
                                            <label class="relative cursor-pointer">
                                                <input type="radio" name="cover_image_id" value="{{ $image->id }}" class="hidden peer">
                                                <div class="aspect-square rounded-lg overflow-hidden border-2 border-transparent peer-checked:border-primary-500 transition-all">
                                                    @if($image->latestVersion)
                                                        <img src="{{ \App\Models\SystemSetting::getFileUrl($image->latestVersion->file_path) }}" 
                                                            alt="{{ $image->name }}"
                                                            class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Documentation Images -->
                        @if($projectImages->count() > 0)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Documentation Images (pilih beberapa)
                                </label>
                                <div class="grid grid-cols-4 gap-2 max-h-48 overflow-y-auto border rounded-lg p-2 dark:border-gray-600">
                                    @foreach($projectImages as $image)
                                        <label class="relative cursor-pointer">
                                            <input type="checkbox" name="documentation_ids[]" value="{{ $image->id }}" class="hidden peer">
                                            <div class="aspect-square rounded-lg overflow-hidden border-2 border-transparent peer-checked:border-green-500 transition-all">
                                                @if($image->latestVersion)
                                                    <img src="{{ \App\Models\SystemSetting::getFileUrl($image->latestVersion->file_path) }}" 
                                                        alt="{{ $image->name }}"
                                                        class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="absolute top-1 right-1 hidden peer-checked:block">
                                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-green-500 text-white">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Activities -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Aktivitas Bulan Ini
                            </label>
                            <textarea name="activities" rows="4"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white focus:ring-primary-500 focus:border-primary-500"
                                placeholder="Deskripsi aktivitas yang dilakukan pada bulan ini...">{{ old('activities') }}</textarea>
                        </div>

                        <!-- Problems -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Kendala / Masalah
                            </label>
                            <textarea name="problems" rows="4"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white focus:ring-primary-500 focus:border-primary-500"
                                placeholder="Deskripsi kendala atau masalah yang dihadapi...">{{ old('problems') }}</textarea>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3 pt-4 border-t dark:border-gray-700">
                            <a href="{{ route('projects.monthly-reports.index', $project) }}"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-dark-700 transition-colors">
                                Batal
                            </a>
                            <button type="submit"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">
                                Simpan Monthly Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>


