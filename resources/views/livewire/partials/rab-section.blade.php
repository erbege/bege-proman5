{{-- Recursive partial for rendering RAB sections --}}
@php
    $level = $level ?? $section->level ?? 0;
@endphp

<div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg mb-4 {{ $level > 0 ? 'border-l-4 border-gray-300 dark:border-dark-600' : '' }}"
    style="margin-left: {{ $level * 2 }}rem;" wire:key="section-{{ $section->id }}">
    <div class="p-3 bg-gray-50 dark:bg-dark-700 border-b dark:border-dark-600 flex justify-between items-center">
        <div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white leading-tight">
                {{ $section->code }}. {{ $section->name }}
            </h3>
            <p class="text-[10px] uppercase font-bold text-gray-500 dark:text-gray-400">{{ $section->items->count() }} item | Bobot:
                {{ number_format($section->weight_percentage, 2) }}%
            </p>
        </div>
        <div class="flex items-center space-x-3">
            @can('financials.view')
            <span class="text-base font-bold text-gray-900 dark:text-white">
                Rp {{ number_format($section->total_price, 2, ',', '.') }}
            </span>
            @endcan
            
            @can('rab.manage')
            <a href="{{ route('projects.rab.ahsp.selector', [$project, $section]) }}"
                class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400" title="Generate dari AHSP">
                <x-heroicon-o-calculator class="w-6 h-6" />
            </a>
            <button wire:click="openItemModal({{ $section->id }})"
                class="text-blue-600 hover:text-blue-800 dark:text-blue-400" title="Tambah Item">
                <x-heroicon-o-plus-circle class="w-6 h-6" />
            </button>
            <button wire:click="openSectionModal({{ $section->id }})"
                class="text-gold-600 hover:text-gold-800 dark:text-gold-400" title="Edit Bagian">
                <x-heroicon-o-pencil-square class="w-5 h-5" />
            </button>
            <button wire:click="confirmDelete('section', {{ $section->id }}, '{{ $section->name }}')"
                class="text-red-600 hover:text-red-800 dark:text-red-400" title="Hapus Bagian">
                <x-heroicon-o-trash class="w-5 h-5" />
            </button>
            @endcan
        </div>
    </div>

    @if($section->items->count() > 0)
        {{-- Bulk Delete Bar with Alpine.js for instant feedback --}}
        @php
            $sectionItemIds = $section->items->pluck('id')->map(fn($id) => (string) $id)->toArray();
        @endphp
        <div x-data="{ sectionIds: {{ json_encode($sectionItemIds) }} }"
            x-show="sectionIds.filter(id => localSelectedItems.includes(id)).length > 0" x-cloak
            class="bg-red-50 dark:bg-red-900/30 px-4 py-2 flex items-center justify-between border-b border-red-200 dark:border-red-800">
            <span class="text-sm text-red-600 dark:text-red-400">
                <strong x-text="sectionIds.filter(id => localSelectedItems.includes(id)).length"></strong> item dipilih
            </span>
            <button wire:click="confirmBulkDelete"
                class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-sm rounded-md flex items-center gap-1">
                <x-heroicon-o-trash class="w-4 h-4" />
                Hapus Terpilih
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-dark-700">
                    <tr>
                        <th class="px-2 py-1.5 text-center">
                            <input type="checkbox" @click="(() => {
                                            const ids = {{ json_encode($sectionItemIds) }};
                                            const allSelected = ids.length > 0 && ids.every(id => localSelectedItems.includes(id));
                                            if (allSelected) {
                                                localSelectedItems = localSelectedItems.filter(id => !ids.includes(id));
                                            } else {
                                                ids.forEach(id => { if (!localSelectedItems.includes(id)) localSelectedItems.push(id); });
                                            }
                                        })()"
                                :checked="(() => { const ids = {{ json_encode($sectionItemIds) }}; return ids.length > 0 && ids.every(id => localSelectedItems.includes(id)); })()"
                                class="rounded border-gray-300 dark:border-dark-700 text-red-600 shadow-sm focus:ring-red-500">
                        </th>
                        <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            No</th>
                        <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Uraian Pekerjaan</th>
                        <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Volume</th>
                        <th class="px-3 py-1.5 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Satuan</th>
                        @can('financials.view')
                        <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Harga Satuan</th>
                        <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Jumlah</th>
                        @endcan
                        <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Bobot</th>
                        <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($section->items as $index => $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700" wire:key="item-{{ $item->id }}">
                            <td class="px-2 py-1.5 text-center">
                                <input type="checkbox" x-model="localSelectedItems" value="{{ $item->id }}"
                                    class="rounded border-gray-300 dark:border-dark-700 text-red-600 shadow-sm focus:ring-red-500">
                            </td>
                            <td class="px-3 py-1.5 text-sm text-gray-900 dark:text-white">{{ $index + 1 }}</td>
                            <td class="px-3 py-1.5 text-sm text-gray-900 dark:text-white">{{ $item->work_name }}</td>
                            <td class="px-3 py-1.5 text-sm text-gray-900 dark:text-white text-right">
                                {{ number_format($item->volume, 2) }}
                            </td>
                            <td class="px-3 py-1.5 text-sm text-gray-900 dark:text-white text-center">{{ $item->unit }}</td>
                            @can('financials.view')
                            <td class="px-3 py-1.5 text-sm text-gray-900 dark:text-white text-right">
                                {{ number_format($item->unit_price, 2, ',', '.') }}
                            </td>
                            <td class="px-3 py-1.5 text-sm font-medium text-gray-900 dark:text-white text-right">
                                {{ number_format($item->total_price, 2, ',', '.') }}
                            </td>
                            @endcan
                            <td class="px-3 py-1.5 text-sm text-gray-900 dark:text-white text-right">
                                {{ number_format($item->weight_percentage, 2) }}%
                            </td>
                            <td class="px-3 py-1.5 text-sm text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('rab.manage')
                                    <button wire:click="openItemModal({{ $section->id }}, {{ $item->id }})" title="Edit"
                                        class="text-gold-600 hover:text-gold-800 dark:text-gold-400"><x-heroicon-o-pencil-square
                                            class="w-5 h-5" /></button>
                                    <button wire:click="confirmDelete('item', {{ $item->id }}, '{{ $item->work_name }}')"
                                        title="Hapus"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400"><x-heroicon-o-trash
                                            class="w-5 h-5" /></button>
                                    @else
                                    <span class="text-xs text-gray-400">View Only</span>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="p-4 text-center text-gray-500 dark:text-gray-400 text-sm">
            Belum ada item. <button wire:click="openItemModal({{ $section->id }})"
                class="text-blue-600 hover:underline">Tambah item pertama</button>
        </div>
    @endif
</div>

{{-- Recursively render children sections --}}
@if($section->relationLoaded('children') && $section->children->count() > 0)
    @foreach($section->children->sortBy('code', SORT_NATURAL) as $childSection)
        @include('livewire.partials.rab-section', ['section' => $childSection, 'level' => $level + 1])
    @endforeach
@endif


