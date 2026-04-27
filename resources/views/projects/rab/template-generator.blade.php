<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.rab.index', $project)],
        ['label' => 'RAB', 'url' => route('projects.rab.index', $project)],
        ['label' => 'Generate dari Template AHSP']
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Generate RAB dari Template AHSP
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Pilih kategori pekerjaan untuk generate struktur RAB otomatis
                </p>
            </div>
            <a href="{{ route('projects.rab.index', $project) }}"
                class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md text-sm transition">
                <x-heroicon-o-arrow-left class="w-4 h-4 inline mr-1" />
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-4" x-data="templateGenerator()">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('projects.rab.template-generator.generate', $project) }}" method="POST"
                @submit="return selectedCategories.length > 0">
                @csrf

                {{-- Settings Panel --}}
                <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg mb-6 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Region Selector --}}
                        <div>
                            <x-input-label for="region_code" value="Wilayah Harga" />
                            <select name="region_code" id="region_code"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 focus:ring-gold-500">
                                @foreach($regions as $code => $name)
                                    <option value="{{ $code }}" {{ $defaultRegion == $code ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @if(empty($regions))
                                <p class="mt-2 text-sm text-yellow-600">
                                    <a href="{{ route('ahsp.prices.import') }}" class="underline">Import harga satuan
                                        dasar</a> terlebih dahulu.
                                </p>
                            @endif
                        </div>

                        {{-- Clear Existing --}}
                        <div class="flex items-end">
                            <label class="flex items-center">
                                <input type="checkbox" name="clear_existing" value="1"
                                    class="rounded border-gray-300 dark:border-dark-700 text-gold-600 shadow-sm focus:ring-gold-500">
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                    Hapus RAB yang ada sebelum generate
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {{-- Categories Tree --}}
                    <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg">
                        <div class="p-4 border-b border-gray-200 dark:border-dark-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Pilih Kategori Pekerjaan
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Centang kategori yang ingin di-generate ke RAB
                            </p>
                        </div>
                        <div class="p-4 max-h-[500px] overflow-y-auto">
                            @forelse($categories as $category)
                                <div class="py-2 border-b border-gray-100 dark:border-dark-700 last:border-0">
                                    <label
                                        class="flex items-center cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-700 px-2 py-1 rounded">
                                        <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                                            x-model="selectedCategories"
                                            class="rounded border-gray-300 dark:border-dark-700 text-gold-600 shadow-sm focus:ring-gold-500">
                                        <div class="ml-3 flex-1">
                                            <span class="font-medium text-gray-900 dark:text-white">
                                                {{ $category->code }}. {{ $category->name }}
                                            </span>
                                            <span
                                                class="ml-2 text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                {{ $category->work_types_count }} item
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            @empty
                                <div class="text-center text-gray-500 py-8">
                                    Belum ada kategori AHSP.
                                    <a href="{{ route('ahsp.create') }}" class="text-blue-600 hover:underline">Tambah
                                        sekarang</a>
                                </div>
                            @endforelse
                        </div>

                        {{-- Selection Summary --}}
                        <div class="p-4 border-t border-gray-200 dark:border-dark-700 bg-gray-50 dark:bg-dark-700">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    <span x-text="selectedCategories.length" class="font-bold text-gold-600"></span>
                                    kategori dipilih
                                </span>
                                <button type="button" @click="selectedCategories = []"
                                    x-show="selectedCategories.length > 0"
                                    class="text-sm text-white hover:text-gray-200 bg-red-600 hover:bg-red-700 px-2 py-1 rounded">
                                    Reset Pilihan
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Preview Panel --}}
                    <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg">
                        <div class="p-4 border-b border-gray-200 dark:border-dark-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Preview Struktur RAB
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Struktur yang akan digenerate
                            </p>
                        </div>
                        <div class="p-4 max-h-[500px] overflow-y-auto">
                            <template x-if="selectedCategories.length === 0">
                                <div class="text-center text-gray-400 py-4">
                                    <x-heroicon-o-folder-open class="w-12 h-12 mx-auto mb-2 opacity-50" />
                                    <p>Pilih kategori di sebelah kiri untuk melihat preview</p>
                                </div>
                            </template>

                            <template x-if="selectedCategories.length > 0 && !previewLoading">
                                <div class="space-y-4">
                                    <template x-for="section in previewData" :key="section.id">
                                        <div
                                            class="border border-gray-200 dark:border-dark-700 rounded-lg overflow-hidden">
                                            <div :class="section.is_parent_only 
                                                    ? 'bg-gray-100 dark:bg-gray-800 border-l-4 border-blue-500' 
                                                    : 'bg-gold-50 dark:bg-gold-900/30'"
                                                class="px-4 py-2 border-b border-gray-200 dark:border-dark-700">
                                                <span :class="section.is_parent_only 
                                                        ? 'text-gray-600 dark:text-gray-400' 
                                                        : 'font-medium text-gold-800 dark:text-gold-200'"
                                                    x-text="section.full_code + '. ' + section.name"></span>
                                                <template x-if="section.is_parent_only">
                                                    <span
                                                        class="ml-2 text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                        auto-created
                                                    </span>
                                                </template>
                                            </div>
                                            <div class="px-4 py-2">
                                                <template x-for="wt in section.work_types" :key="wt.id">
                                                    <div
                                                        class="flex items-center justify-between py-1 text-sm border-b border-gray-50 dark:border-dark-700 last:border-0">
                                                        <span class="text-gray-700 dark:text-gray-300"
                                                            x-text="wt.code + ' - ' + wt.name"></span>
                                                        <span class="text-gray-400 text-xs" x-text="wt.unit"></span>
                                                    </div>
                                                </template>
                                                <template
                                                    x-if="section.work_types.length === 0 && !section.is_parent_only">
                                                    <div class="text-gray-400 text-sm py-2">Tidak ada jenis pekerjaan
                                                    </div>
                                                </template>
                                                <template x-if="section.is_parent_only">
                                                    <div class="text-blue-500 dark:text-blue-400 text-sm py-2 italic">
                                                        Kategori parent (container)
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="previewLoading">
                                <div class="text-center py-4">
                                    <x-heroicon-o-arrow-path class="w-8 h-8 mx-auto animate-spin text-gray-400" />
                                    <p class="text-gray-500 mt-2">Memuat preview...</p>
                                </div>
                            </template>
                        </div>

                        {{-- Generate Button --}}
                        <div class="p-4 border-t border-gray-200 dark:border-dark-700">
                            <button type="submit" :disabled="selectedCategories.length === 0"
                                class="w-full px-3 py-1.5 bg-gold-600 text-white rounded-md hover:bg-gold-700 disabled:bg-gray-400 disabled:cursor-not-allowed font-medium transition flex items-center justify-center gap-2">
                                <x-heroicon-o-bolt class="w-5 h-5" />
                                <span x-show="selectedCategories.length > 0">
                                    Generate RAB (<span x-text="totalWorkTypes"></span> item pekerjaan)
                                </span>
                                <span x-show="selectedCategories.length === 0">
                                    Pilih Kategori Dulu
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function templateGenerator() {
                return {
                    selectedCategories: [],
                    previewData: [],
                    previewLoading: false,
                    totalWorkTypes: 0,

                    init() {
                        this.$watch('selectedCategories', (value) => {
                            if (value.length > 0) {
                                this.loadPreview();
                            } else {
                                this.previewData = [];
                                this.totalWorkTypes = 0;
                            }
                        });
                    },

                    async loadPreview() {
                        this.previewLoading = true;
                        try {
                            const response = await fetch('{{ route("projects.rab.template-generator.preview", $project) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    category_ids: this.selectedCategories.map(id => parseInt(id))
                                })
                            });

                            const data = await response.json();
                            if (data.success) {
                                this.previewData = data.preview;
                                this.totalWorkTypes = data.preview.reduce((sum, s) => sum + s.work_types.length, 0);
                            }
                        } catch (error) {
                            console.error('Preview error:', error);
                        } finally {
                            this.previewLoading = false;
                        }
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>


