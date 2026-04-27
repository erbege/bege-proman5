<div x-data="{ localSelectedItems: @entangle('selectedItems') }" class="py-4 max-w-full mx-auto sm:px-6 lg:px-8">
    {{-- Loading Overlay --}}
    <div wire:loading.flex class="fixed inset-0 bg-black/30 z-40 items-center justify-center">
        <div class="bg-white dark:bg-dark-800 rounded-lg px-3 py-1.5 shadow-xl flex items-center gap-3">
            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span class="text-gray-700 dark:text-gray-300">Memproses...</span>
        </div>
    </div>

    {{-- Messages --}}
    @if(session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Summary Card --}}
    <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-3 mb-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400">Total Bagian</p>
                <p class="text-lg font-black text-gray-900 dark:text-white">{{ $sections->count() }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400">Total Item</p>
                <p class="text-lg font-black text-gray-900 dark:text-white">{{ $totalItemsCount }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400">Nilai RAB</p>
                <p class="text-lg font-black text-blue-600 dark:text-blue-400">
                    Rp {{ number_format($totalValue, 0, ',', '.') }}
                </p>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400">Nilai Kontrak</p>
                <p class="text-lg font-black text-gray-900 dark:text-white">{{ $project->formatted_contract_value }}</p>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="mb-4 flex justify-end gap-2">
        <a href="{{ route('projects.rab.export-excel', $project) }}" target="_blank"
            class="inline-flex items-center px-4 py-2 border border-green-700 text-green-700 dark:text-green-500 dark:border-green-500 rounded-md hover:bg-green-50 dark:hover:bg-green-900/20 text-sm font-medium transition-colors">
            <x-heroicon-o-document-text class="w-4 h-4 mr-2" />
            Excel
        </a>
        <a href="{{ route('projects.rab.export-pdf', $project) }}" target="_blank"
            class="inline-flex items-center px-4 py-2 border border-red-600 text-red-600 dark:text-red-500 dark:border-red-500 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 text-sm font-medium transition-colors">
            <x-heroicon-o-document-duplicate class="w-4 h-4 mr-2" />
            PDF
        </a>
        <a href="{{ route('projects.rab.template-generator', $project) }}"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
            <x-heroicon-o-bolt class="w-4 h-4 mr-2" />
            Generate dari AHSP
        </a>
        <button wire:click="openImportModal"
            class="inline-flex items-center px-4 py-2 bg-gold-500 text-white rounded-md hover:bg-gold-600 text-sm font-medium">
            <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-2" />
            Import Excel
        </button>
        <button wire:click="openSectionModal"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">
            <x-heroicon-o-plus class="w-4 h-4 mr-2" />
            Tambah Bagian
        </button>
    </div>

    {{-- RAB Sections --}}
    @forelse($sections as $section)
        <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg mb-4 {{ $section->level > 0 ? 'border-l-4 border-gray-300 dark:border-dark-600' : '' }}"
            style="margin-left: {{ $section->level * 2 }}rem;" wire:key="section-{{ $section->id }}">
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
                    <span class="text-lg font-bold text-gray-900 dark:text-white">
                        Rp {{ number_format($section->total_price, 2, ',', '.') }}
                    </span>
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
                                <th
                                    class="px-3 py-1.5 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Satuan</th>
                                <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Harga Satuan</th>
                                <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                    Jumlah</th>
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
                                    <td class="px-3 py-1.5 text-sm text-gray-900 dark:text-white text-right">
                                        {{ number_format($item->unit_price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-1.5 text-sm font-medium text-gray-900 dark:text-white text-right">
                                        {{ number_format($item->total_price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-1.5 text-sm text-gray-900 dark:text-white text-right">
                                        {{ number_format($item->weight_percentage, 2) }}%
                                    </td>
                                    <td class="px-3 py-1.5 text-sm text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button wire:click="openItemModal({{ $section->id }}, {{ $item->id }})" title="Edit"
                                                class="text-gold-600 hover:text-gold-800 dark:text-gold-400"><x-heroicon-o-pencil-square
                                                    class="w-5 h-5" /></button>
                                            <button wire:click="confirmDelete('item', {{ $item->id }}, '{{ $item->work_name }}')"
                                                title="Hapus"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400"><x-heroicon-o-trash
                                                    class="w-5 h-5" /></button>
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
                @include('livewire.partials.rab-section', ['section' => $childSection, 'level' => ($section->level ?? 0) + 1])
            @endforeach
        @endif
    @empty
        <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-12 text-center">
            <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Belum ada data RAB</h3>
            <p class="mt-1 text-sm text-gray-500">Import dari Excel atau tambahkan bagian manual.</p>
            <div class="mt-4 flex justify-center gap-2">
                <button wire:click="openImportModal" class="px-4 py-2 bg-gold-500 text-white rounded-md text-sm">Import
                    Excel</button>
                <button wire:click="openSectionModal" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm">Tambah
                    Bagian</button>
            </div>
        </div>
    @endforelse

    {{-- Section Modal --}}
    @if($showSectionModal)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay"
            @keydown.escape.window="$wire.closeSectionModal()">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeSectionModal"></div>
                <div class="relative bg-white dark:bg-dark-800 rounded-lg shadow-xl max-w-md w-full p-4">
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" wire:click="closeSectionModal" class="text-gray-400 hover:text-gray-500">
                            <x-heroicon-o-x-circle class="w-6 h-6" />
                        </button>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        {{ $editingSectionId ? 'Edit' : 'Tambah' }} Bagian Pekerjaan
                    </h3>
                    <form wire:submit="saveSection">
                        <div class="space-y-4">
                            {{-- AHSP Category Selection (Hierarchical) --}}
                            {{-- AHSP Category Selection (Hierarchical) --}}
                            {{-- AHSP Category Selection (Unified) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Pilih Kategori AHSP
                                </label>
                                <select wire:model.live="selectedAhspCategoryId"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                    <option value="">-- Manual / Tidak menggunakan AHSP --</option>
                                    @foreach($ahspCategories as $cat)
                                        <option value="{{ $cat['id'] }}">
                                            {!! $cat['display_name'] !!}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Struktur hierarki (Root/Level 1/dst) akan otomatis mengikuti kategori yang dipilih.
                                </p>
                            </div>

                            @if($selectedAhspCategoryId)
                                <div
                                    class="rounded-md bg-blue-50 dark:bg-blue-900/30 p-3 text-sm text-blue-700 dark:text-blue-300">
                                    <p><strong>Info:</strong> Bagian ini akan ditempatkan sesuai hierarki AHSP.</p>
                                </div>
                            @else
                                <div
                                    class="rounded-md bg-yellow-50 dark:bg-yellow-900/30 p-3 text-sm text-yellow-700 dark:text-yellow-300">
                                    <p><strong>Info:</strong> Bagian manual akan disimpan sebagai Root (Level 0).</p>
                                </div>
                            @endif

                            <div class="border-t border-gray-200 dark:border-dark-600 pt-4">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Atau isi manual:</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode</label>
                                <input type="text" wire:model="sectionCode"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white"
                                    placeholder="A, B, C...">
                                @error('sectionCode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama
                                    Bagian</label>
                                <input type="text" wire:model="sectionName"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white"
                                    placeholder="Pekerjaan Persiapan">
                                @error('sectionName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" wire:click="closeSectionModal"
                                class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 rounded-md">Batal</button>
                            <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Item Modal --}}
    @if($showItemModal)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay" @keydown.escape.window="$wire.closeItemModal()">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeItemModal"></div>
                <div
                    class="relative bg-white dark:bg-dark-800 rounded-lg shadow-xl max-w-2xl w-full p-4 max-h-[90vh] overflow-y-auto scrollbar-overlay">
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" wire:click="closeItemModal" class="text-gray-400 hover:text-gray-500">
                            <x-heroicon-o-x-circle class="w-6 h-6" />
                        </button>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        {{ $editingItemId ? 'Edit' : 'Tambah' }} Item Pekerjaan
                    </h3>
                    <form wire:submit="saveItem">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Section Selection --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bagian</label>
                                <select wire:model.live="itemSectionId"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                    @foreach($sections as $s)
                                        <option value="{{ $s->id }}">{{ $s->code }}. {{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Region Selection for AHSP pricing --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Wilayah
                                    Harga</label>
                                <select wire:model="regionCode"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                    @foreach($regions as $code => $name)
                                        <option value="{{ $code }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- AHSP Work Type Suggestion --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Pilih dari AHSP (Opsional)
                                </label>
                                <select wire:model.live="itemAhspWorkTypeId"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                    <option value="">-- Isi Manual --</option>
                                    @if($suggestedWorkTypes->isNotEmpty())
                                        @foreach($suggestedWorkTypes as $wt)
                                            <option value="{{ $wt->id }}">{{ $wt->code }} - {{ $wt->name }} ({{ $wt->unit }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    @if($suggestedWorkTypes->isNotEmpty())
                                        <span class="text-green-600 dark:text-green-400">{{ $suggestedWorkTypes->count() }}
                                            jenis pekerjaan tersedia dari kategori section.</span>
                                    @else
                                        <span class="text-yellow-600 dark:text-yellow-400">Tidak ada jenis pekerjaan AHSP yang
                                            tersedia untuk kategori ini.</span>
                                    @endif
                                </p>
                            </div>

                            <div class="md:col-span-2 border-t border-gray-200 dark:border-dark-600 pt-4">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Detail Pekerjaan:</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode
                                    (opsional)</label>
                                <input type="text" wire:model="itemCode"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                            </div>
                            <div></div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Uraian
                                    Pekerjaan</label>
                                <input type="text" wire:model="itemWorkName"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                @error('itemWorkName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Volume</label>
                                <input type="number" step="0.01" wire:model="itemVolume"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                @error('itemVolume') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Satuan</label>
                                <input type="text" wire:model="itemUnit"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white"
                                    placeholder="m², m³, kg, ls...">
                                @error('itemUnit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga
                                    Satuan</label>
                                <input type="number" wire:model="itemUnitPrice" step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                @error('itemUnitPrice') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah</label>
                                <div
                                    class="mt-1 px-3 py-2 bg-gray-100 dark:bg-dark-700 rounded-md text-gray-900 dark:text-white font-medium">
                                    Rp {{ number_format($itemVolume * $itemUnitPrice, 0, ',', '.') }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mulai
                                    (opsional)</label>
                                <input type="date" wire:model="itemPlannedStart"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selesai
                                    (opsional)</label>
                                <input type="date" wire:model="itemPlannedEnd"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi
                                    (opsional)</label>
                                <textarea wire:model="itemDescription" rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white"></textarea>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" wire:click="closeItemModal"
                                class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 rounded-md">Batal</button>
                            <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Import Modal --}}
    @if($showImportModal)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay" @keydown.escape.window="$wire.closeImportModal()">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeImportModal"></div>
                <div
                    class="relative bg-white dark:bg-dark-800 rounded-lg shadow-xl max-w-2xl w-full p-4 max-h-[90vh] overflow-y-auto scrollbar-overlay">
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" wire:click="closeImportModal" class="text-gray-400 hover:text-gray-500">
                            <x-heroicon-o-x-circle class="w-6 h-6" />
                        </button>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        <x-heroicon-o-arrow-up-tray class="w-5 h-5 inline mr-1" />
                        Import RAB dari Excel
                    </h3>

                    {{-- Column Instructions --}}
                    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-sm">
                        <p class="font-medium text-blue-800 dark:text-blue-300 mb-2">
                            <x-heroicon-o-information-circle class="w-4 h-4 inline" />
                            Kolom yang diterima:
                        </p>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-xs border border-blue-200 dark:border-blue-700 rounded">
                                <thead class="bg-blue-100 dark:bg-blue-900/50">
                                    <tr>
                                        <th class="px-2 py-1 text-left text-blue-800 dark:text-blue-300">Kolom</th>
                                        <th class="px-2 py-1 text-left text-blue-800 dark:text-blue-300">Alias</th>
                                        <th class="px-2 py-1 text-center text-blue-800 dark:text-blue-300">Wajib</th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-blue-200 dark:divide-blue-700 text-blue-700 dark:text-blue-400">
                                    <tr>
                                        <td class="px-2 py-1 font-medium">work_name</td>
                                        <td class="px-2 py-1">nama_pekerjaan, uraian</td>
                                        <td class="px-2 py-1 text-center text-green-600">✓</td>
                                    </tr>
                                    <tr>
                                        <td class="px-2 py-1 font-medium">volume</td>
                                        <td class="px-2 py-1">vol</td>
                                        <td class="px-2 py-1 text-center text-green-600">✓</td>
                                    </tr>
                                    <tr>
                                        <td class="px-2 py-1 font-medium">unit</td>
                                        <td class="px-2 py-1">satuan, sat</td>
                                        <td class="px-2 py-1 text-center text-green-600">✓</td>
                                    </tr>
                                    <tr>
                                        <td class="px-2 py-1 font-medium">unit_price</td>
                                        <td class="px-2 py-1">harga_satuan, harga</td>
                                        <td class="px-2 py-1 text-center text-green-600">✓</td>
                                    </tr>
                                    <tr>
                                        <td class="px-2 py-1 font-medium">section_code</td>
                                        <td class="px-2 py-1">kode_bagian</td>
                                        <td class="px-2 py-1 text-center text-yellow-600">○</td>
                                    </tr>
                                    <tr>
                                        <td class="px-2 py-1 font-medium">section_name</td>
                                        <td class="px-2 py-1">nama_bagian</td>
                                        <td class="px-2 py-1 text-center text-yellow-600">○</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="mt-2 text-xs text-blue-600 dark:text-blue-400">
                            <span class="text-green-600">✓</span> Wajib, <span class="text-yellow-600">○</span> Disarankan
                        </p>
                    </div>

                    <form wire:submit="import">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">File Excel</label>
                                <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-gold-50 file:text-gold-700 hover:file:bg-gold-100">
                                @error('importFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                <div wire:loading wire:target="importFile" class="mt-2 text-sm text-gray-500">Mengupload...
                                </div>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="clearExisting" id="clearExisting"
                                    class="rounded border-gray-300 text-gold-600">
                                <label for="clearExisting" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Hapus data
                                    RAB yang ada</label>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" wire:click="closeImportModal"
                                class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 rounded-md">Batal</button>
                            <button type="submit"
                                class="px-4 py-2 bg-gold-500 text-white text-sm rounded-md hover:bg-gold-600"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="import">Import</span>
                                <span wire:loading wire:target="import">Importing...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeDeleteModal"></div>
                <div class="relative bg-white dark:bg-dark-800 rounded-lg shadow-xl max-w-md w-full p-4">
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" wire:click="closeDeleteModal" class="text-gray-400 hover:text-gray-500">
                            <x-heroicon-o-x-circle class="w-6 h-6" />
                        </button>
                    </div>
                    <h3 class="text-lg font-medium text-red-600 mb-4">Konfirmasi Hapus</h3>
                    <p class="text-gray-600 dark:text-gray-400">Yakin ingin menghapus
                        {{ $deleteType === 'section' ? 'bagian' : 'item' }} <strong>"{{ $deleteName }}"</strong>?
                    </p>
                    @if($deleteType === 'section')
                        <p class="mt-2 text-sm text-red-500">Semua item dalam bagian ini juga akan dihapus.</p>
                    @endif
                    <div class="mt-6 flex justify-end gap-2">
                        <button wire:click="closeDeleteModal"
                            class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 rounded-md">Batal</button>
                        <button wire:click="delete"
                            class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Bulk Delete Modal --}}
    @if($showBulkDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto scrollbar-overlay">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeBulkDeleteModal"></div>
                <div class="relative bg-white dark:bg-dark-800 rounded-lg shadow-xl max-w-md w-full p-4">
                    <div class="absolute top-0 right-0 pt-4 pr-4">
                        <button type="button" wire:click="closeBulkDeleteModal" class="text-gray-400 hover:text-gray-500">
                            <x-heroicon-o-x-circle class="w-6 h-6" />
                        </button>
                    </div>
                    <h3 class="text-lg font-medium text-red-600 mb-4">Konfirmasi Hapus Massal</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Yakin ingin menghapus <strong>{{ count($selectedItems) }}</strong> item pekerjaan yang dipilih?
                    </p>
                    <p class="mt-2 text-sm text-red-500">Tindakan ini tidak dapat dibatalkan.</p>
                    <div class="mt-6 flex justify-end gap-2">
                        <button wire:click="closeBulkDeleteModal"
                            class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 rounded-md">Batal</button>
                        <button wire:click="executeBulkDelete"
                            class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700">
                            Hapus {{ count($selectedItems) }} Item
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>


