<div>
    {{-- Search Modal --}}
    @if($showModal)
        <div x-data="{ }" x-init="$refs.searchInput.focus()" class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay"
            @keydown.escape.window="$wire.closeModal()" @keydown.arrow-down.prevent="$wire.moveSelection('down')"
            @keydown.arrow-up.prevent="$wire.moveSelection('up')" @keydown.enter.prevent="$wire.selectCurrent()"
            x-on:navigate-to.window="window.location.href = $event.detail.url">

            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeModal"></div>

            {{-- Modal --}}
            <div class="relative min-h-screen flex items-start justify-center pt-16 sm:pt-24 px-4">
                <div class="relative w-full max-w-2xl bg-white dark:bg-dark-800 rounded-xl shadow-2xl ring-1 ring-black/5 dark:ring-white/10 overflow-hidden"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100">

                    {{-- Search Input --}}
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                        </div>
                        <input type="text" x-ref="searchInput" wire:model.live.debounce.300ms="query"
                            class="w-full pl-12 pr-4 py-3 text-base bg-transparent border-0 border-b border-gray-200 dark:border-dark-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-0 focus:outline-none"
                            placeholder="Cari proyek, PO, PR, material, supplier...">

                        {{-- Loading --}}
                        <div wire:loading class="absolute inset-y-0 right-12 pr-4 flex items-center">
                            <svg class="animate-spin h-5 w-5 text-gold-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </div>

                        {{-- Close button --}}
                        <button wire:click="closeModal"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <kbd class="text-xs bg-gray-100 dark:bg-dark-700 px-1.5 py-0.5 rounded">ESC</kbd>
                        </button>
                    </div>

                    {{-- Results --}}
                    <div class="max-h-96 overflow-y-auto scrollbar-overlay">
                        @if(count($results) > 0)
                            @php
                                $groupedResults = collect($results)->groupBy('group');
                            @endphp
                            <div class="py-2">
                                @foreach($groupedResults as $groupName => $groupItems)
                                    {{-- Group Header --}}
                                    <div
                                        class="px-3 py-1 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest bg-gray-50 dark:bg-dark-700/50">
                                        {{ $groupName }}
                                    </div>
                                    @foreach($groupItems as $result)
                                        @php
                                            $globalIndex = array_search($result, $results);
                                            $isSelected = $selectedIndex === $globalIndex;
                                        @endphp
                                        <a href="{{ $result['url'] }}"
                                            class="flex items-center px-3 py-1 transition-colors group {{ $isSelected ? 'bg-gold-50 dark:bg-gold-900/20' : 'hover:bg-gray-50 dark:hover:bg-dark-700' }}"
                                            wire:key="result-{{ $globalIndex }}">
                                            <div
                                                class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center
                                                                                                                                                                                        {{ $result['color'] === 'gold' ? 'bg-gold-100 dark:bg-gold-900/30 text-gold-600 dark:text-gold-400' : '' }}
                                                                                                                                                                                        {{ $result['color'] === 'blue' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : '' }}
                                                                                                                                                                                        {{ $result['color'] === 'green' ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400' : '' }}
                                                                                                                                                                                        {{ $result['color'] === 'purple' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' : '' }}
                                                                                                                                                                                        {{ $result['color'] === 'orange' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : '' }}
                                                                                                                                                                                        {{ $result['color'] === 'indigo' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400' : '' }}
                                                                                                                                                                                        {{ $result['color'] === 'teal' ? 'bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400' : '' }}">
                                                @if($result['icon'] === 'folder')
                                                    <x-heroicon-o-folder class="w-4 h-4" />
                                                @elseif($result['icon'] === 'cube')
                                                    <x-heroicon-o-cube class="w-4 h-4" />
                                                @elseif($result['icon'] === 'truck')
                                                    <x-heroicon-o-truck class="w-4 h-4" />
                                                @elseif($result['icon'] === 'user-group')
                                                    <x-heroicon-o-user-group class="w-4 h-4" />
                                                @elseif($result['icon'] === 'document-text')
                                                    <x-heroicon-o-document-text class="w-4 h-4" />
                                                @elseif($result['icon'] === 'document-check')
                                                    <x-heroicon-o-document-check class="w-4 h-4" />
                                                @elseif($result['icon'] === 'document-currency-dollar')
                                                    <x-heroicon-o-document-currency-dollar class="w-4 h-4" />
                                                @elseif($result['icon'] === 'list-bullet')
                                                    <x-heroicon-o-list-bullet class="w-4 h-4" />
                                                @endif
                                            </div>
                                            <div class="ml-3 flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                    {{ $result['title'] }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $result['subtitle'] }}
                                                </p>
                                            </div>
                                            <div
                                                class="flex-shrink-0 {{ $isSelected ? 'opacity-100' : 'opacity-0 group-hover:opacity-100' }} transition-opacity">
                                                <x-heroicon-o-arrow-right class="w-4 h-4 text-gray-400" />
                                            </div>
                                        </a>
                                    @endforeach
                                @endforeach
                            </div>
                        @elseif(strlen($query) >= 2)
                            <div class="px-4 py-12 text-center">
                                <x-heroicon-o-magnifying-glass class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" />
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada hasil untuk "{{ $query }}"
                                </p>
                            </div>
                        @else
                            <div class="px-4 py-8 text-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Ketik minimal 2 karakter untuk mencari</p>
                                <div class="mt-4 flex flex-wrap justify-center gap-2 text-xs text-gray-400">
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-dark-700 rounded">Proyek</span>
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-dark-700 rounded">Item RAB</span>
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-dark-700 rounded">PO</span>
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-dark-700 rounded">PR</span>
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-dark-700 rounded">Material</span>
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-dark-700 rounded">Supplier</span>
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-dark-700 rounded">Klien</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="px-3 py-1.5 border-t border-gray-200 dark:border-dark-700 bg-gray-50 dark:bg-dark-700/50">
                        <div class="flex items-center justify-between text-xs text-gray-400">
                            <div class="flex items-center gap-4">
                                <span><kbd class="px-1.5 py-0.5 bg-white dark:bg-dark-600 rounded shadow-sm">↑↓</kbd>
                                    navigasi</span>
                                <span><kbd class="px-1.5 py-0.5 bg-white dark:bg-dark-600 rounded shadow-sm">Enter</kbd>
                                    pilih</span>
                            </div>
                            <span><kbd class="px-1.5 py-0.5 bg-white dark:bg-dark-600 rounded shadow-sm">Esc</kbd>
                                tutup</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>


