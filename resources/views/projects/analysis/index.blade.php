<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Analisis Material']
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Analisis Material - {{ $project->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->code }}</p>
            </div>
            @if($unanalyzedCount > 0)
                <div class="flex items-center space-x-2">
                    {{-- Local Analysis Button --}}
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-local-analysis-modal'))"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        <x-heroicon-o-cpu-chip class="w-4 h-4 mr-2" />
                        Lokal ({{ $unanalyzedCount }})
                    </button>
                    {{-- AI Analysis Button --}}
                    @if(count($providers) > 0)
                        <button type="button" @if($aiDisabled) disabled @else
                        onclick="window.dispatchEvent(new CustomEvent('open-ai-analysis-modal'))" @endif
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 {{ $aiDisabled ? 'opacity-50 cursor-not-allowed' : '' }}"
                            @if($aiDisabled) title="Fitur AI dinonaktifkan di pengaturan sistem" @endif>
                            <x-heroicon-o-sparkles class="w-4 h-4 mr-2" />
                            AI ({{ $unanalyzedCount }})
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('info'))
                <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg">
                    {{ session('info') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg">
                    {{ session('warning') }}
                </div>
            @endif

            <!-- Tab Navigation -->
            <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <a href="{{ route('projects.analysis.index', ['project' => $project, 'tab' => 'summary']) }}"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'summary' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <x-heroicon-o-chart-bar class="w-5 h-5 inline-block mr-1" />
                        Ringkasan Material
                        <span
                            class="ml-2 py-0.5 px-2 rounded-full text-xs {{ $activeTab === 'summary' ? 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                            {{ $summaryCount }}
                        </span>
                    </a>
                    <a href="{{ route('projects.analysis.index', ['project' => $project, 'tab' => 'analyzed']) }}"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'analyzed' ? 'border-green-500 text-green-600 dark:text-green-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <x-heroicon-o-check-circle class="w-5 h-5 inline-block mr-1" />
                        Sudah Dianalisis
                        <span
                            class="ml-2 py-0.5 px-2 rounded-full text-xs {{ $activeTab === 'analyzed' ? 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                            {{ $analyzedCount }}
                        </span>
                    </a>
                    <a href="{{ route('projects.analysis.index', ['project' => $project, 'tab' => 'unanalyzed']) }}"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'unanalyzed' ? 'border-yellow-500 text-yellow-600 dark:text-yellow-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                        <x-heroicon-o-clock class="w-5 h-5 inline-block mr-1" />
                        Belum Dianalisis
                        <span
                            class="ml-2 py-0.5 px-2 rounded-full text-xs {{ $activeTab === 'unanalyzed' ? 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                            {{ $unanalyzedCount }}
                        </span>
                    </a>
                </nav>
            </div>

            <!-- Provider Status (collapsible) -->
            <details class="mb-6">
                <summary
                    class="cursor-pointer text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                    <x-heroicon-o-cog-6-tooth class="w-4 h-4 inline-block mr-1" />
                    Metode Analisis Tersedia
                </summary>
                <div class="mt-2 p-4 bg-white dark:bg-dark-800 rounded-lg shadow-sm">
                    <div class="flex flex-wrap gap-2">
                        @if($aiDisabled)
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                                <x-heroicon-o-no-symbol class="w-3 h-3 mr-1" />
                                AI: Tidak Aktif
                            </span>
                        @endif

                        {{-- Local Matching - Always Available --}}
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                            <x-heroicon-o-cpu-chip class="w-3 h-3 mr-1" />
                            Local Matching (Selalu Tersedia)
                        </span>
                        @if(count($providers) > 0 && !$aiDisabled)
                            @foreach($providers as $key => $name)
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                    <x-heroicon-o-sparkles class="w-3 h-3 mr-1" />
                                    {{ $name }}
                                </span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </details>

            <!-- Material Summary Tab -->
            @if($activeTab === 'summary')
                <div class="mb-8">
                    <livewire:material-control-report :project="$project" />
                </div>
                
                @if($summary && $summary->count() > 0)
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 border border-gray-100 dark:border-dark-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Ringkasan Kebutuhan Material ({{ $summary->total() }} material)
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-dark-700">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Material</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Total Qty</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Satuan</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Dari Item</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($summary as $material)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                            {{ $material->material_name }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right font-medium">
                                            {{ number_format($material->total_qty, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-center">
                                            {{ $material->unit }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">
                                            {{ $material->source_items }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- Summary Pagination --}}
                    <div class="mt-4">
                        {{ $summary->links() }}
                    </div>
                </div>
                @else
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 text-center">
                    <x-heroicon-o-inbox class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500 mb-4" />
                    <p class="text-gray-500 dark:text-gray-400">Belum ada material yang dianalisis.</p>
                    <a href="{{ route('projects.analysis.index', ['project' => $project, 'tab' => 'unanalyzed']) }}"
                        class="mt-4 inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400">
                        Lihat item yang belum dianalisis →
                    </a>
                </div>
                @endif
            @endif

            <!-- Unanalyzed Items Tab -->
            @if($activeTab === 'unanalyzed')
                @if($unanalyzedItems && $unanalyzedItems->count() > 0)
                <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Item Belum Dianalisis ({{ $unanalyzedItems->total() }})
                    </h3>
                    <div class="overflow-visible min-h-[300px]">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-dark-700">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Nama Pekerjaan</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Volume</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Satuan</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($unanalyzedItems as $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $item->work_name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">
                                            {{ number_format($item->volume, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-center">
                                            {{ $item->unit }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div x-data="{ open: false, loading: false, loadingText: '' }"
                                                class="relative inline-block text-left">
                                                <button @click="open = !open" type="button" x-show="!loading"
                                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-sm inline-flex items-center">
                                                    Analisis
                                                    <x-heroicon-o-chevron-down class="w-4 h-4 ml-1" />
                                                </button>
                                                {{-- Loading indicator --}}
                                                <span x-show="loading"
                                                    class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-600"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                            stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                        </path>
                                                    </svg>
                                                    <span x-text="loadingText"></span>
                                                </span>
                                                <div x-show="open" @click.away="open = false"
                                                    class="origin-top-right absolute right-0 mt-1 w-48 rounded-md shadow-lg bg-white dark:bg-dark-700 ring-1 ring-black ring-opacity-5 z-10">
                                                    <div class="py-1">
                                                        {{-- Local Matching - Always Available --}}
                                                        <form
                                                            action="{{ route('projects.analysis.analyze-local', [$project, $item]) }}"
                                                            method="POST" @submit="loading = true; loadingText = 'Local...'">
                                                            @csrf
                                                            <button type="submit"
                                                                class="block w-full text-left px-4 py-2 text-sm text-blue-700 dark:text-blue-300 hover:bg-gray-100 dark:hover:bg-gray-600 font-medium">
                                                                <x-heroicon-o-cpu-chip class="w-4 h-4 inline mr-1" />
                                                                Local Matching
                                                            </button>
                                                        </form>
                                                        @if(count($providers) > 0)
                                                            <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                                                            @foreach($providers as $key => $name)
                                                                <form
                                                                    action="{{ route('projects.analysis.analyze', [$project, $item]) }}"
                                                                    method="POST" @if(!$aiDisabled)
                                                                    @submit="loading = true; loadingText = '{{ $name }}...'" @endif>
                                                                    @csrf
                                                                    <input type="hidden" name="provider" value="{{ $key }}">
                                                                    <button type="submit" @if($aiDisabled) disabled @endif
                                                                        class="block w-full text-left px-4 py-2 text-sm {{ $aiDisabled ? 'text-gray-400 dark:text-gray-500 cursor-not-allowed' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600' }}">
                                                                        <x-heroicon-o-sparkles class="w-4 h-4 inline mr-1" />
                                                                        {{ $name }}
                                                                        @if($aiDisabled) <span class="text-xs ml-1">(Disabled)</span>
                                                                        @endif
                                                                    </button>
                                                                </form>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- Unanalyzed Pagination --}}
                    <div class="mt-6">
                        {{ $unanalyzedItems->withQueryString()->links() }}
                    </div>
                </div>
                @else
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 text-center">
                    <x-heroicon-o-check-badge class="w-12 h-12 mx-auto text-green-500 mb-4" />
                    <p class="text-gray-500 dark:text-gray-400">Semua item sudah dianalisis!</p>
                    <a href="{{ route('projects.analysis.index', ['project' => $project, 'tab' => 'analyzed']) }}"
                        class="mt-4 inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400">
                        Lihat item yang sudah dianalisis →
                    </a>
                </div>
                @endif
            @endif

            <!-- Analyzed Items Tab -->
            @if($activeTab === 'analyzed')
                @if($analyzedItems && $analyzedItems->count() > 0)
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    x-data="{ selectedItems: [] , showResetModal: false }">
                    <!-- Bulk Actions Header -->
                    <div
                        class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <label class="inline-flex items-center">
                                <input type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-5 h-5"
                                    @click="selectedItems = $el.checked ? {{ json_encode($analyzedItems->pluck('id')) }} : []"
                                    :checked="selectedItems.length === {{ $analyzedItems->count() }}">
                                <span class="ml-2 text-gray-700 dark:text-gray-300 font-medium">Pilih Semua
                                    ({{ $analyzedItems->total() }} Item)</span>
                            </label>
                        </div>

                        <!-- Bulk Action Buttons -->
                        <div class="flex items-center gap-3">
                            <!-- Expand/Collapse All -->
                            <div class="flex items-center mr-2 border-r border-gray-200 dark:border-gray-700 pr-4">
                                <button type="button" @click="window.dispatchEvent(new CustomEvent('expand-all'))"
                                    class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 mr-3 font-medium transition-colors">
                                    Expand All
                                </button>
                                <button type="button" @click="window.dispatchEvent(new CustomEvent('collapse-all'))"
                                    class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 font-medium transition-colors">
                                    Collapse All
                                </button>
                            </div>

                            <div x-show="selectedItems.length > 0" class="flex items-center gap-3" style="display: none;"
                                x-show.important="selectedItems.length > 0">
                                <span class="text-sm text-gray-500 font-medium"><span x-text="selectedItems.length"></span>
                                    item
                                    dipilih</span>
                                <button type="button" @click="showResetModal = true"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-sm transition-colors">
                                    <x-heroicon-o-arrow-path class="w-4 h-4 mr-2" />
                                    Reset Analisis
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Bulk Reset Confirmation Modal -->
                    <template x-teleport="body">
                        <div x-show="showResetModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
                            aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div
                                class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                                    @click="showResetModal = false"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                <div
                                    class="relative inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                    <div class="absolute top-0 right-0 pt-4 pr-4">
                                        <button type="button" @click="showResetModal = false"
                                            class="text-gray-400 hover:text-gray-500">
                                            <x-heroicon-o-x-circle class="w-6 h-6" />
                                        </button>
                                    </div>
                                    <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6">
                                        <div class="sm:flex sm:items-start">
                                            <div
                                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600" />
                                            </div>
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                                    Reset Analisis</h3>
                                                <div class="mt-2">
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        Apakah Anda yakin ingin me-reset analisis untuk <strong
                                                            x-text="selectedItems.length"></strong> item yang dipilih?
                                                    </p>
                                                    <p class="text-sm text-orange-600 dark:text-orange-400 mt-2">
                                                        <x-heroicon-o-exclamation-triangle class="w-4 h-4 inline mr-1" />
                                                        Item akan kembali ke status "Belum Dianalisis".
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-dark-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <form action="{{ route('projects.analysis.bulk-delete', $project) }}" method="POST">
                                            @csrf
                                            <template x-for="id in selectedItems" :key="id">
                                                <input type="hidden" name="ids[]" :value="id">
                                            </template>
                                            <button type="submit"
                                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                Reset
                                            </button>
                                        </form>
                                        <button type="button" @click="showResetModal = false"
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600 dark:hover:bg-gray-700">
                                            Batal
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="space-y-6">
                        @foreach($analyzedItems as $item)
                            <div class="group relative bg-white dark:bg-dark-800 border border-gray-200 dark:border-dark-700 rounded-xl p-5 hover:shadow-md transition-shadow duration-200"
                                :class="{ 'ring-2 ring-blue-500 border-transparent': selectedItems.includes({{ $item->id }}) }">

                                <!-- Item Header -->
                                <div class="flex items-start gap-4 mb-4">
                                    <div class="pt-1">
                                        <input type="checkbox" value="{{ $item->id }}" x-model="selectedItems"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-5 h-5 cursor-pointer">
                                    </div>
                                    <div class="flex-grow min-w-0">
                                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-2">
                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white truncate"
                                                title="{{ $item->work_name }}">
                                                {{ $item->work_name }}
                                            </h4>

                                            <!-- Action Badges & Links -->
                                            <div class="flex items-center gap-2 flex-shrink-0">
                                                @if($item->materialForecasts->first()?->analysis_source)
                                                    @php
                                                        $source = $item->materialForecasts->first()->analysis_source;
                                                        $badgeClass = match ($source) {
                                                            'local_matching' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                                            'manual_override' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                                                            default => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                                        };
                                                    @endphp
                                                    <span class="px-2.5 py-0.5 text-xs font-medium rounded-full {{ $badgeClass }}">
                                                        {{ str_replace('_', ' ', ucfirst($source)) }}
                                                    </span>
                                                @endif

                                                <div class="h-4 w-px bg-gray-300 dark:bg-gray-600 mx-1"></div>

                                                <a href="{{ route('projects.analysis.show', [$project, $item]) }}"
                                                    class="text-sm font-medium text-gray-600 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 transition-colors">
                                                    Detail
                                                </a>

                                                <div x-data="{ open: false }" class="relative inline-block text-left">
                                                    <button @click="open = !open" type="button"
                                                        class="text-sm font-medium text-orange-600 hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300 flex items-center transition-colors">
                                                        Re-analisis
                                                        <x-heroicon-s-chevron-down class="w-3 h-3 ml-1" />
                                                    </button>
                                                    <!-- Dropdown Menu -->
                                                    <div x-show="open" @click.away="open = false" style="display: none;"
                                                        class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-dark-700 ring-1 ring-black ring-opacity-5 z-20">
                                                        <div class="py-1">
                                                            <button type="button"
                                                                @click="open = false; window.dispatchEvent(new CustomEvent('open-reanalyze-modal', { detail: { itemId: {{ $item->id }}, method: 'local', methodName: 'Local Matching' } }))"
                                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                                <x-heroicon-o-cpu-chip
                                                                    class="w-4 h-4 inline mr-2 text-blue-500" />
                                                                Local Matching
                                                            </button>
                                                            @foreach($providers as $key => $name)
                                                                <button type="button" @if($aiDisabled) disabled
                                                                    class="w-full text-left px-4 py-2 text-sm text-gray-400 dark:text-gray-500 cursor-not-allowed"
                                                                @else
                                                                        @click="open = false; window.dispatchEvent(new CustomEvent('open-reanalyze-modal', { detail: { itemId: {{ $item->id }}, method: '{{ $key }}', methodName: '{{ $name }}' } }))"
                                                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600"
                                                                    @endif>
                                                                    <x-heroicon-o-sparkles
                                                                        class="w-4 h-4 inline mr-2 {{ $aiDisabled ? 'text-gray-400' : 'text-purple-500' }}" />
                                                                    {{ $name }}
                                                                    @if($aiDisabled) <span class="text-xs ml-1">(Disabled)</span>
                                                                    @endif
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 font-mono">
                                            Volume: {{ $item->volume }} {{ $item->unit }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Materials List (Collapsible Accordion) -->
                                @if($item->materialForecasts->count() > 0)
                                    <div class="mt-4 ml-9 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-100 dark:border-gray-700/50"
                                        x-data="{ 
                                                        expanded: false, 
                                                        selectedMaterials: [], 
                                                        showDeleteModal: false,
                                                        init() {
                                                            window.addEventListener('expand-all', () => this.expanded = true);
                                                            window.addEventListener('collapse-all', () => this.expanded = false);
                                                        }
                                                    }">
                                        <!-- Accordion Header (Always Visible) -->
                                        <button type="button" @click="expanded = !expanded"
                                            class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-100 dark:hover:bg-gray-800/50 rounded-lg transition-colors">
                                            <h5
                                                class="flex items-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                <x-heroicon-o-cube class="w-3 h-3 mr-1.5" />
                                                Material Hasil Analisis ({{ $item->materialForecasts->count() }})
                                            </h5>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs text-gray-400 dark:text-gray-500"
                                                    x-text="expanded ? 'Tutup' : 'Lihat'"></span>
                                                <x-heroicon-o-chevron-down
                                                    class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                                    x-bind:class="{ 'rotate-180': expanded }" />
                                            </div>
                                        </button>

                                        <!-- Accordion Content (Collapsible) -->
                                        <div x-show="expanded" x-collapse x-cloak class="px-4 pb-4">
                                            <div
                                                class="flex flex-wrap items-center justify-end gap-2 mb-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                                                <label
                                                    class="inline-flex items-center text-xs text-gray-500 dark:text-gray-400 cursor-pointer">
                                                    <input type="checkbox"
                                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-4 h-4"
                                                        @click="selectedMaterials = $el.checked ? {{ json_encode($item->materialForecasts->pluck('id')) }} : []"
                                                        :checked="selectedMaterials.length === {{ $item->materialForecasts->count() }}">
                                                    <span class="ml-1.5">Pilih Semua</span>
                                                </label>
                                                <button x-show="selectedMaterials.length > 0" @click="showDeleteModal = true"
                                                    type="button" x-show.important="selectedMaterials.length > 0"
                                                    style="display: none;"
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 hover:text-red-700 dark:text-red-400 border border-red-300 dark:border-red-600 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                                    <x-heroicon-o-trash class="w-3 h-3 mr-1" />
                                                    Hapus (<span x-text="selectedMaterials.length"></span>)
                                                </button>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                @foreach($item->materialForecasts as $forecast)
                                                    <div class="flex items-start gap-2 bg-white dark:bg-dark-800 p-3 rounded-md border border-gray-200 dark:border-gray-700 shadow-sm"
                                                        :class="{ 'ring-2 ring-blue-500 border-transparent': selectedMaterials.includes({{ $forecast->id }}) }">
                                                        <input type="checkbox" value="{{ $forecast->id }}" x-model="selectedMaterials"
                                                            class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-4 h-4 cursor-pointer">
                                                        <div class="min-w-0 flex-1">
                                                            <div class="flex items-center gap-2 flex-wrap">
                                                                <span class="font-medium text-gray-800 dark:text-gray-200">
                                                                    {{ $forecast->raw_material_name }}
                                                                </span>
                                                                @if($forecast->status_color === 'green')
                                                                    <span
                                                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
                                                                        title="Match Score: {{ $forecast->match_score }}%">
                                                                        <x-heroicon-s-check-circle class="w-3 h-3 mr-1" />
                                                                        {{ $forecast->match_score }}%
                                                                    </span>
                                                                @elseif($forecast->status_color === 'yellow')
                                                                    <span
                                                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200"
                                                                        title="Match Score: {{ $forecast->match_score }}%">
                                                                        <x-heroicon-s-exclamation-triangle class="w-3 h-3 mr-1" />
                                                                        {{ $forecast->match_score }}%
                                                                    </span>
                                                                @else
                                                                    <span
                                                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"
                                                                        title="Unmatched">
                                                                        <x-heroicon-s-x-circle class="w-3 h-3 mr-1" /> Unmatched
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                                Est: {{ number_format($forecast->estimated_qty, 2) }}
                                                                {{ $forecast->unit }}
                                                                (Koef: {{ $forecast->coefficient }})
                                                            </div>
                                                            @if($forecast->material)
                                                                <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                                                    Mapped to: <strong>{{ $forecast->material->name }}</strong>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="flex-shrink-0 flex items-center gap-2 ml-2">
                                                            {{-- Edit Button --}}
                                                            @if($forecast->status_color !== 'green' || $forecast->analysis_source === 'manual_override')
                                                                <div x-data="{ open: false, selectedMaterial: {{ $forecast->material_id ?? 'null' }} }"
                                                                    class="relative">
                                                                    <button @click="open = !open"
                                                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                                                        title="Edit Mapping">
                                                                        <x-heroicon-s-pencil class="w-4 h-4" />
                                                                    </button>
                                                                    <div x-show="open" @click.away="open = false" style="display: none;"
                                                                        class="absolute right-0 mt-2 w-72 rounded-md shadow-lg bg-white dark:bg-dark-800 ring-1 ring-black ring-opacity-5 z-20 p-3">
                                                                        <form
                                                                            action="{{ route('projects.analysis.update-mapping', [$project, $forecast]) }}"
                                                                            method="POST">
                                                                            @csrf
                                                                            <label
                                                                                class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                                                Map to Master Data:
                                                                            </label>
                                                                            <select name="material_id" x-model="selectedMaterial"
                                                                                class="block w-full text-xs text-gray-900 dark:text-white dark:bg-dark-700 border-gray-300 dark:border-dark-600 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                                                <option value="">-- Pilih Material --</option>
                                                                                <template
                                                                                    x-for="[id, name] in Object.entries(window.masterMaterialsData || {})"
                                                                                    :key="id">
                                                                                    <option :value="id" x-text="name"
                                                                                        :selected="selectedMaterial == id"></option>
                                                                                </template>
                                                                            </select>
                                                                            <div class="mt-2 flex justify-end">
                                                                                <button type="submit"
                                                                                    class="inline-flex items-center px-2 py-1 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                                                    Save
                                                                                </button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                            {{-- Delete Button --}}
                                                            <div x-data="{ confirmDelete: false, deleting: false }" class="relative">
                                                                <button @click="confirmDelete = true" x-show="!deleting"
                                                                    class="text-red-400 hover:text-red-600 dark:hover:text-red-300"
                                                                    title="Hapus Material">
                                                                    <x-heroicon-s-trash class="w-4 h-4" />
                                                                </button>
                                                                <svg x-show="deleting" class="animate-spin w-4 h-4 text-red-500"
                                                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                                        stroke="currentColor" stroke-width="4"></circle>
                                                                    <path class="opacity-75" fill="currentColor"
                                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                                    </path>
                                                                </svg>
                                                                <div x-show="confirmDelete" @click.away="confirmDelete = false"
                                                                    style="display: none;"
                                                                    class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-dark-800 ring-1 ring-black ring-opacity-5 z-20 p-3">
                                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                                                                        Hapus material ini dari analisis?
                                                                    </p>
                                                                    <div class="flex justify-end gap-2">
                                                                        <button @click="confirmDelete = false"
                                                                            class="px-2 py-1 text-xs text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-dark-700 rounded">
                                                                            Batal
                                                                        </button>
                                                                        <form
                                                                            action="{{ route('projects.analysis.delete-forecast', [$project, $forecast]) }}"
                                                                            method="POST"
                                                                            @submit="deleting = true; confirmDelete = false">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit"
                                                                                class="px-2 py-1 text-xs bg-red-600 text-white hover:bg-red-700 rounded">
                                                                                Hapus
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- Bulk Delete Confirmation Modal -->
                                        <template x-teleport="body">
                                            <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
                                                aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                                <div
                                                    class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                                                        @click="showDeleteModal = false"></div>
                                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                                    <div
                                                        class="relative inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                                        <div class="absolute top-0 right-0 pt-4 pr-4">
                                                            <button type="button" @click="showDeleteModal = false"
                                                                class="text-gray-400 hover:text-gray-500">
                                                                <x-heroicon-o-x-circle class="w-6 h-6" />
                                                            </button>
                                                        </div>
                                                        <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6">
                                                            <div class="sm:flex sm:items-start">
                                                                <div
                                                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                                    <x-heroicon-o-exclamation-triangle
                                                                        class="h-6 w-6 text-red-600" />
                                                                </div>
                                                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                                                        Hapus Material</h3>
                                                                    <div class="mt-2">
                                                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                                                            Apakah Anda yakin ingin menghapus <strong
                                                                                x-text="selectedMaterials.length"></strong> material
                                                                            yang dipilih dari hasil analisis?
                                                                        </p>
                                                                        <p
                                                                            class="text-sm text-orange-600 dark:text-orange-400 mt-2">
                                                                            <x-heroicon-o-exclamation-triangle
                                                                                class="w-4 h-4 inline mr-1" />
                                                                            Jika semua material dihapus, item akan kembali ke status
                                                                            "Belum Dianalisis".
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="bg-gray-50 dark:bg-dark-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                            <form
                                                                action="{{ route('projects.analysis.bulk-delete-materials', $project) }}"
                                                                method="POST">
                                                                @csrf
                                                                <template x-for="id in selectedMaterials" :key="id">
                                                                    <input type="hidden" name="forecast_ids[]" :value="id">
                                                                </template>
                                                                <button type="submit"
                                                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                                    Hapus
                                                                </button>
                                                            </form>
                                                            <button type="button" @click="showDeleteModal = false"
                                                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600 dark:hover:bg-gray-700">
                                                                Batal
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-6">
                        {{ $analyzedItems->withQueryString()->links() }}
                    </div>
                </div>
                @else
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 text-center">
                    <x-heroicon-o-inbox class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500 mb-4" />
                    <p class="text-gray-500 dark:text-gray-400">Belum ada item yang dianalisis.</p>
                    <a href="{{ route('projects.analysis.index', ['project' => $project, 'tab' => 'unanalyzed']) }}"
                        class="mt-4 inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400">
                        Lihat item yang belum dianalisis →
                    </a>
                </div>
                @endif
            @endif

            @if($analyzedCount === 0 && $unanalyzedCount === 0)
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-8 text-center">
                    <x-heroicon-o-folder-open class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Belum ada item RAB</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Silakan import RAB terlebih dahulu.</p>
                    <div class="mt-6">
                        <a href="{{ route('projects.rab.import', $project) }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Import RAB
                        </a>
                    </div>
                </div>
            @endif

            <!-- Back Button -->
            <div class="mt-6">
                <a href="{{ route('projects.show', $project) }}"
                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                    ← Kembali ke Detail Proyek
                </a>
            </div>
        </div>
    </div>

    {{-- Master Materials Data for Alpine.js dropdowns --}}
    <script>
        window.masterMaterialsData = @json($masterMaterials);
    </script>

    {{-- Analyze All Local Modal with Progress --}}
    <div x-data="localAnalysisProgress()" x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="showModal && !isProcessing && closeModal()">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                @click="!isProcessing && closeModal()" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="relative inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6">
                    <div class="text-center">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            <span x-show="!isProcessing && !isComplete">Analisis Semua dengan Local Matching</span>
                            <span x-show="isProcessing">Menganalisis Material...</span>
                            <span x-show="isComplete">Analisis Selesai</span>
                        </h3>

                        {{-- Circular Progress Indicator --}}
                        <div x-show="isProcessing || isComplete" class="flex justify-center mb-4">
                            <div class="relative w-32 h-32">
                                <svg class="w-32 h-32 transform -rotate-90">
                                    <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="none"
                                        class="text-gray-200 dark:text-gray-700" />
                                    <circle cx="64" cy="64" r="56" stroke="currentColor" stroke-width="8" fill="none"
                                        stroke-linecap="round" :stroke-dasharray="351.86"
                                        :stroke-dashoffset="351.86 - (351.86 * progress / 100)"
                                        class="text-blue-600 transition-all duration-300 ease-out" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-2xl font-bold text-gray-900 dark:text-white"
                                        x-text="progress + '%'"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Progress Info --}}
                        <div x-show="isProcessing" class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            <p>Item <span x-text="current"></span> dari <span x-text="total"></span></p>
                            <p class="text-xs mt-1 truncate px-4" x-text="currentItem"></p>
                        </div>

                        {{-- Stats --}}
                        <div x-show="isProcessing || isComplete" class="flex justify-center gap-6 mb-4">
                            <div class="text-center">
                                <span class="text-2xl font-bold text-green-600" x-text="successCount"></span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Berhasil</p>
                            </div>
                            <div class="text-center">
                                <span class="text-2xl font-bold text-yellow-600" x-text="noMatchCount"></span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Tidak Ada Match</p>
                            </div>
                        </div>

                        {{-- Confirmation before start --}}
                        <div x-show="!isProcessing && !isComplete">
                            <div class="flex justify-center mb-4">
                                <div
                                    class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                    <x-heroicon-o-cpu-chip class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Apakah Anda yakin ingin menganalisis <strong>{{ $unanalyzedCount }}</strong>
                                item menggunakan Local Matching?
                            </p>
                        </div>

                        {{-- Complete Message --}}
                        <div x-show="isComplete" class="text-sm text-green-600 dark:text-green-400">
                            <x-heroicon-o-check-circle class="w-6 h-6 mx-auto mb-2" />
                            <p x-text="message"></p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-dark-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button x-show="!isProcessing && !isComplete" @click="startAnalysis()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Ya, Analisis Semua
                    </button>
                    <button x-show="isComplete" @click="finishAndReload()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Selesai
                    </button>
                    <button x-show="!isProcessing" @click="closeModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600">
                        <span x-show="!isComplete">Batal</span>
                        <span x-show="isComplete">Tutup</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Analyze All AI Modal with Progress --}}
    @if(count($providers) > 0)
        <div x-data="aiAnalysisProgress()" x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            @keydown.escape.window="showModal && !isProcessing && closeModal()">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    @click="!isProcessing && closeModal()" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="relative inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6">
                        <div class="text-center">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                <span x-show="!isProcessing">Analisis Semua dengan AI</span>
                                <span x-show="isProcessing">Memproses...</span>
                            </h3>

                            {{-- Processing Spinner --}}
                            <div x-show="isProcessing" class="flex flex-col items-center mb-4">
                                <div class="relative w-24 h-24">
                                    <svg class="animate-spin w-24 h-24 text-green-600" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">
                                    Mengirim {{ $unanalyzedCount }} item ke AI untuk dianalisis...<br>
                                    <span class="text-xs">Proses akan berjalan di background</span>
                                </p>
                            </div>

                            {{-- Selection Form --}}
                            <div x-show="!isProcessing">
                                <div class="flex justify-center mb-4">
                                    <div
                                        class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                        <x-heroicon-o-sparkles class="w-6 h-6 text-green-600 dark:text-green-400" />
                                    </div>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    Pilih provider AI dan analisis <strong>{{ $unanalyzedCount }}</strong> item.
                                    Proses akan berjalan di background.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-dark-700 px-4 py-3 sm:px-6">
                        <form x-ref="aiForm" action="{{ route('projects.analysis.analyze-all', $project) }}" method="POST"
                            class="flex flex-col sm:flex-row items-center justify-center sm:justify-end gap-3">
                            @csrf
                            <div x-show="!isProcessing" class="flex items-center gap-3">
                                <select name="provider"
                                    class="text-sm border-gray-300 dark:border-dark-600 dark:bg-dark-800 dark:text-gray-300 rounded-md shadow-sm">
                                    @foreach($providers as $key => $name)
                                        <option value="{{ $key }}" {{ $defaultProvider === $key ? 'selected' : '' }}>{{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" @click="submitAnalysis()"
                                    class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:text-sm">
                                    Ya, Analisis
                                </button>
                                <button type="button" @click="closeModal()"
                                    class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Re-analyze Modal --}}
    <div x-data="{ 
            open: false, 
            itemId: null, 
            method: null, 
            methodName: '',
            loading: false,
            getUrl() {
                if (this.method === 'local') {
                    return '{{ route('projects.analysis.reanalyze-local', [$project, ':id']) }}'.replace(':id', this.itemId);
                }
                return '{{ route('projects.analysis.reanalyze', [$project, ':id']) }}'.replace(':id', this.itemId);
            },
            init() {
                window.addEventListener('open-reanalyze-modal', (e) => {
                    this.open = true;
                    this.itemId = e.detail.itemId;
                    this.method = e.detail.method;
                    this.methodName = e.detail.methodName;
                    this.loading = false;
                });
            }
        }" x-show="open" class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="!loading && (open = false)"
                aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="relative inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div x-show="!loading" class="absolute top-0 right-0 pt-4 pr-4">
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-500">
                        <x-heroicon-o-x-circle class="w-6 h-6" />
                    </button>
                </div>
                <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    {{-- Loading Spinner --}}
                    <div x-show="loading" class="text-center py-4">
                        <svg class="animate-spin h-12 w-12 text-orange-600 mx-auto mb-4"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Menganalisis dengan <span
                                x-text="methodName"></span>...</p>
                    </div>
                    {{-- Confirmation Content --}}
                    <div x-show="!loading" class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                            <x-heroicon-o-arrow-path class="h-6 w-6 text-orange-600" />
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                Re-analisis Item
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Apakah Anda yakin ingin re-analisis item ini menggunakan <strong
                                        x-text="methodName"></strong>?
                                    Hasil analisis sebelumnya akan dihapus.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div x-show="!loading"
                    class="bg-gray-50 dark:bg-dark-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form :action="getUrl()" method="POST" @submit="loading = true">
                        @csrf
                        <input type="hidden" name="provider" :value="method !== 'local' ? method : ''">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Ya, Re-analisis
                        </button>
                    </form>
                    <button type="button" @click="open = false"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600 dark:hover:bg-gray-700">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function localAnalysisProgress() {
                return {
                    showModal: false,
                    isProcessing: false,
                    isComplete: false,
                    progress: 0,
                    current: 0,
                    total: {{ $unanalyzedCount }},
                    currentItem: '',
                    successCount: 0,
                    noMatchCount: 0,
                    message: '',
                    eventSource: null,

                    init() {
                        window.addEventListener('open-local-analysis-modal', () => {
                            this.showModal = true;
                            this.resetState();
                        });
                    },

                    resetState() {
                        this.isProcessing = false;
                        this.isComplete = false;
                        this.progress = 0;
                        this.current = 0;
                        this.currentItem = '';
                        this.successCount = 0;
                        this.noMatchCount = 0;
                        this.message = '';
                    },

                    closeModal() {
                        if (this.eventSource) {
                            this.eventSource.close();
                        }
                        this.showModal = false;
                    },

                    startAnalysis() {
                        this.isProcessing = true;
                        this.progress = 0;

                        const url = '{{ route('projects.analysis.analyze-all-local', $project) }}';

                        this.eventSource = new EventSource(url + '?_token={{ csrf_token() }}');

                        this.eventSource.onmessage = (event) => {
                            const data = JSON.parse(event.data);

                            if (data.complete) {
                                this.isProcessing = false;
                                this.isComplete = true;
                                this.message = data.message;
                                this.progress = 100;
                                this.eventSource.close();
                            } else {
                                this.progress = data.progress;
                                this.current = data.current;
                                this.total = data.total;
                                this.currentItem = data.item;
                                this.successCount = data.success;
                                this.noMatchCount = data.noMatch;
                            }
                        };

                        this.eventSource.onerror = (error) => {
                            console.error('EventSource error:', error);
                            this.eventSource.close();
                            this.isProcessing = false;
                            this.isComplete = true;
                            this.message = 'Terjadi kesalahan saat memproses analisis.';
                        };
                    },

                    finishAndReload() {
                        window.location.reload();
                    }
                }
            }

            function aiAnalysisProgress() {
                return {
                    showModal: false,
                    isProcessing: false,
                    selectedProvider: '{{ $defaultProvider }}',

                    init() {
                        window.addEventListener('open-ai-analysis-modal', () => {
                            this.showModal = true;
                            this.isProcessing = false;
                        });
                    },

                    closeModal() {
                        this.showModal = false;
                    },

                    submitAnalysis() {
                        this.isProcessing = true;
                        this.$refs.aiForm.submit();
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>