<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.rab.index', $project)],
        ['label' => 'RAB', 'url' => route('projects.rab.index', $project)],
        ['label' => 'Generate dari AHSP']
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Generate RAB dari AHSP
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Section: {{ $section->code }} - {{ $section->name }}
                </p>
            </div>
            <a href="{{ route('projects.rab.index', $project) }}"
                class="px-4 py-2 bg-gray-800 dark:bg-gray-600 text-white rounded-md text-sm">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-4" x-data="ahspSelector()">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('projects.rab.ahsp.generate', [$project, $section]) }}" method="POST">
                @csrf

                <!-- Region Selector -->
                <div class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg mb-6 p-4">
                    <div class="flex items-center gap-4">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Wilayah Harga:</label>
                        <select name="region_code" x-model="regionCode"
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            @foreach($regions as $code => $name)
                                <option value="{{ $code }}" {{ $defaultRegion == $code ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @if(empty($regions))
                            <span class="text-sm text-yellow-600">
                                <a href="{{ route('ahsp.prices.import') }}" class="underline">Import harga satuan dasar</a>
                                terlebih dahulu.
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Search & Available AHSP -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Available AHSP -->
                    <div class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg">
                        <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-[10px] font-bold uppercase text-gray-400 dark:text-gray-500">Daftar AHSP</h3>
                            @if($matchingCategory)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Kategori: {{ $matchingCategory->code }} - {{ $matchingCategory->name }}
                                </p>
                            @endif
                            <input type="text" placeholder="Cari pekerjaan..." x-model="searchTerm"
                                @input.debounce.300ms="filterItems()"
                                class="mt-2 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-xs">
                        </div>
                        <div class="p-4 max-h-[500px] overflow-y-auto">
                            @forelse($categoriesWithWorkTypes as $category)
                                <div class="mb-4" x-show="isCategoryVisible('{{ addslashes($category->name) }}')">
                                    <!-- Category Header -->
                                    <div class="flex items-center justify-between p-2 bg-indigo-50 dark:bg-indigo-900/30 rounded-t border-b border-indigo-200 dark:border-indigo-700">
                                        <div class="flex-1">
                                            <span class="text-sm font-semibold text-indigo-800 dark:text-indigo-200">
                                                {{ $category->code }}. {{ strtoupper($category->name) }}
                                            </span>
                                            <span class="text-xs text-indigo-600 dark:text-indigo-400 ml-2">
                                                ({{ $category->workTypes->count() }} item)
                                            </span>
                                        </div>
                                        <button type="button" 
                                            @click="addAllFromCategory({{ json_encode($category->workTypes->map(fn($wt) => ['id' => $wt->id, 'code' => $wt->code, 'name' => $wt->name, 'unit' => $wt->unit])) }})"
                                            class="text-xs px-2 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                            + Tambah Semua
                                        </button>
                                    </div>
                                    
                                    <!-- Work Types in Category -->
                                    <div class="space-y-1 bg-gray-50 dark:bg-gray-700/50 rounded-b p-1">
                                        @forelse($category->workTypes as $wt)
                                            <div class="flex items-center justify-between p-1.5 bg-white dark:bg-gray-700 rounded hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer"
                                                x-show="isItemVisible('{{ addslashes($wt->code) }}', '{{ addslashes($wt->name) }}')"
                                                @click="addItem({{ $wt->id }}, '{{ $wt->code }}', '{{ addslashes($wt->name) }}', '{{ $wt->unit }}')">
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-xs font-black text-gray-900 dark:text-gray-100 truncate">
                                                        {{ $wt->code }}</div>
                                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 truncate leading-tight">
                                                        {{ $wt->name }}</div>
                                                </div>
                                                <div class="ml-2 text-[10px] font-bold text-gray-400">{{ $wt->unit }}</div>
                                                <button type="button" class="ml-2 text-indigo-600 hover:text-indigo-800">
                                                    <x-heroicon-o-plus-circle class="w-4 h-4" />
                                                </button>
                                            </div>
                                        @empty
                                            <div class="text-center text-gray-500 py-2 text-sm">
                                                Tidak ada item pekerjaan
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-gray-500 py-4">
                                    Belum ada data AHSP untuk kategori ini. 
                                    <a href="{{ route('ahsp.create') }}" class="text-indigo-600 hover:underline">Tambah sekarang</a>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Selected Items -->
                    <div class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg">
                        <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-[10px] font-bold uppercase text-gray-400 dark:text-gray-500">
                                Item Terpilih <span class="text-xs font-normal"
                                    x-text="'(' + selectedItems.length + ')'"></span>
                            </h3>
                        </div>
                        <div class="p-4 max-h-96 overflow-y-auto">
                            <template x-if="selectedItems.length === 0">
                                <div class="text-center text-gray-500 py-8">
                                    Klik item AHSP di sebelah kiri untuk menambahkan
                                </div>
                            </template>
                            <div class="space-y-3">
                                <template x-for="(item, index) in selectedItems" :key="index">
                                    <div class="p-2 bg-indigo-50 dark:bg-indigo-900/30 rounded border border-indigo-100 dark:border-indigo-800">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 min-w-0">
                                                <div class="text-xs font-black text-indigo-900 dark:text-indigo-200"
                                                    x-text="item.code"></div>
                                                <div class="text-[10px] text-indigo-600 dark:text-indigo-300 leading-tight"
                                                    x-text="item.name"></div>
                                            </div>
                                            <button type="button" @click="removeItem(index)"
                                                class="text-red-500 hover:text-red-700 ml-2">
                                                <x-heroicon-o-x-circle class="w-4 h-4" />
                                            </button>
                                        </div>
                                        <div class="mt-2 flex items-center gap-2">
                                            <input type="hidden" :name="'items[' + index + '][work_type_id]'"
                                                :value="item.id">
                                            <input type="number" :name="'items[' + index + '][volume]'"
                                                x-model="item.volume" step="0.0001" min="0.0001" required
                                                class="w-20 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-xs px-2 py-1"
                                                placeholder="Volume">
                                            <span class="text-[10px] font-bold text-gray-500" x-text="item.unit"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Generate Button -->
                        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="submit" :disabled="selectedItems.length === 0"
                                class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500 disabled:bg-gray-400 disabled:cursor-not-allowed">
                                <span x-show="selectedItems.length > 0">Generate <span
                                        x-text="selectedItems.length"></span> Item RAB</span>
                                <span x-show="selectedItems.length === 0">Pilih Item AHSP</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function ahspSelector() {
                return {
                    selectedItems: [],
                    searchTerm: '',
                    regionCode: '{{ $defaultRegion }}',

                    addItem(id, code, name, unit) {
                        // Check if already added
                        if (this.selectedItems.find(item => item.id === id)) {
                            return;
                        }
                        this.selectedItems.push({
                            id: id,
                            code: code,
                            name: name,
                            unit: unit,
                            volume: 1
                        });
                    },

                    addAllFromCategory(workTypes) {
                        workTypes.forEach(wt => {
                            if (!this.selectedItems.find(item => item.id === wt.id)) {
                                this.selectedItems.push({
                                    id: wt.id,
                                    code: wt.code,
                                    name: wt.name,
                                    unit: wt.unit,
                                    volume: 1
                                });
                            }
                        });
                    },

                    removeItem(index) {
                        this.selectedItems.splice(index, 1);
                    },

                    filterItems() {
                        // Client-side filtering is handled by x-show directives
                    },

                    isCategoryVisible(categoryName) {
                        if (!this.searchTerm) return true;
                        const term = this.searchTerm.toLowerCase();
                        return categoryName.toLowerCase().includes(term);
                    },

                    isItemVisible(code, name) {
                        if (!this.searchTerm) return true;
                        const term = this.searchTerm.toLowerCase();
                        return code.toLowerCase().includes(term) || name.toLowerCase().includes(term);
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>


