<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Master AHSP', 'url' => route('ahsp.index')],
        ['label' => $ahspWorkType->code]
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $ahspWorkType->code }} - {{ $ahspWorkType->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Satuan: {{ $ahspWorkType->unit }} | Sumber: {{ $ahspWorkType->source }}
                    @if($ahspWorkType->reference)
                        | Ref: {{ $ahspWorkType->reference }}
                    @endif
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('ahsp.edit', $ahspWorkType) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-400">
                    Edit
                </a>
                <a href="{{ route('ahsp.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="componentManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Region Selector & Price Summary -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex flex-wrap items-center gap-4 mb-6">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Wilayah Harga:</label>
                        <form method="GET" class="flex items-center gap-2">
                            <select name="region" onchange="this.form.submit()"
                                class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                @foreach($regions as $code => $name)
                                    <option value="{{ $code }}" {{ $regionCode == $code ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    <!-- Price Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-4">
                            <div class="text-sm text-blue-600 dark:text-blue-400">Tenaga Kerja (A)</div>
                            <div class="text-xl font-bold text-blue-800 dark:text-blue-200">
                                Rp {{ number_format($calculation['labor_cost'], 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/30 rounded-lg p-4">
                            <div class="text-sm text-green-600 dark:text-green-400">Bahan (B)</div>
                            <div class="text-xl font-bold text-green-800 dark:text-green-200">
                                Rp {{ number_format($calculation['material_cost'], 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="bg-orange-50 dark:bg-orange-900/30 rounded-lg p-4">
                            <div class="text-sm text-orange-600 dark:text-orange-400">Peralatan (C)</div>
                            <div class="text-xl font-bold text-orange-800 dark:text-orange-200">
                                Rp {{ number_format($calculation['equipment_cost'], 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="bg-indigo-50 dark:bg-indigo-900/30 rounded-lg p-4">
                            <div class="text-sm text-indigo-600 dark:text-indigo-400">Harga Satuan (D+E)</div>
                            <div class="text-2xl font-bold text-indigo-800 dark:text-indigo-200">
                                Rp {{ number_format($calculation['unit_price'], 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-indigo-500 mt-1">
                                Overhead {{ $calculation['overhead_percentage'] }}%: Rp
                                {{ number_format($calculation['overhead_cost'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Component Breakdown -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Rincian Komponen</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        No</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Uraian</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Kode</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Sat.</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Koefisien</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Harga Satuan</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Jumlah Harga</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Tenaga Kerja Section -->
                                <tr class="bg-blue-50 dark:bg-blue-900/20">
                                    <td colspan="7" class="px-4 py-2 font-semibold text-blue-800 dark:text-blue-200">A.
                                        TENAGA KERJA</td>
                                    <td class="px-4 py-2 text-center">
                                        <button @click="openModal('labor')"
                                            class="text-xs bg-blue-600 hover:bg-blue-500 text-white px-2 py-1 rounded">
                                            + Tambah
                                        </button>
                                    </td>
                                </tr>
                                @php $laborItems = collect($calculation['breakdown'])->where('type', 'labor'); @endphp
                                @forelse($laborItems as $index => $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 group">
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $item['name'] }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $item['code'] ?? '-' }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $item['unit'] }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100 text-right">
                                            {{ number_format($item['coefficient'], 4) }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100 text-right">
                                            {{ number_format($item['price'], 0, ',', '.') }}
                                        </td>
                                        <td
                                            class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100 text-right font-medium">
                                            {{ number_format($item['amount'], 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-center">
                                            <div
                                                class="flex justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button @click="editComponent(@js($item))"
                                                    class="text-yellow-600 hover:text-yellow-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                    </svg>
                                                </button>
                                                <button @click="deleteComponent(@js($item['id']))"
                                                    class="text-red-600 hover:text-red-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-2 text-sm text-gray-500 text-center italic">Tidak ada
                                            komponen tenaga kerja</td>
                                    </tr>
                                @endforelse
                                <tr class="bg-blue-100 dark:bg-blue-900/40">
                                    <td colspan="6"
                                        class="px-4 py-2 text-right font-semibold text-blue-800 dark:text-blue-200">
                                        Jumlah Harga Tenaga Kerja</td>
                                    <td class="px-4 py-2 text-right font-bold text-blue-800 dark:text-blue-200">
                                        {{ number_format($calculation['labor_cost'], 2, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>

                                <!-- Bahan Section -->
                                <tr class="bg-green-50 dark:bg-green-900/20">
                                    <td colspan="7" class="px-4 py-2 font-semibold text-green-800 dark:text-green-200">
                                        B. BAHAN</td>
                                    <td class="px-4 py-2 text-center">
                                        <button @click="openModal('material')"
                                            class="text-xs bg-green-600 hover:bg-green-500 text-white px-2 py-1 rounded">
                                            + Tambah
                                        </button>
                                    </td>
                                </tr>
                                @php $materialItems = collect($calculation['breakdown'])->where('type', 'material'); @endphp
                                @forelse($materialItems as $index => $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 group">
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $item['name'] }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $item['code'] ?? '-' }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $item['unit'] }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100 text-right">
                                            {{ number_format($item['coefficient'], 4) }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100 text-right">
                                            {{ number_format($item['price'], 0, ',', '.') }}
                                        </td>
                                        <td
                                            class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100 text-right font-medium">
                                            {{ number_format($item['amount'], 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-center">
                                            <div
                                                class="flex justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button @click="editComponent(@js($item))"
                                                    class="text-yellow-600 hover:text-yellow-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                    </svg>
                                                </button>
                                                <button @click="deleteComponent(@js($item['id']))"
                                                    class="text-red-600 hover:text-red-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-2 text-sm text-gray-500 text-center italic">Tidak ada
                                            komponen bahan</td>
                                    </tr>
                                @endforelse
                                <tr class="bg-green-100 dark:bg-green-900/40">
                                    <td colspan="6"
                                        class="px-4 py-2 text-right font-semibold text-green-800 dark:text-green-200">
                                        Jumlah Harga Bahan</td>
                                    <td class="px-4 py-2 text-right font-bold text-green-800 dark:text-green-200">
                                        {{ number_format($calculation['material_cost'], 2, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>

                                <!-- Peralatan Section -->
                                <tr class="bg-orange-50 dark:bg-orange-900/20">
                                    <td colspan="7"
                                        class="px-4 py-2 font-semibold text-orange-800 dark:text-orange-200">C.
                                        PERALATAN</td>
                                    <td class="px-4 py-2 text-center">
                                        <button @click="openModal('equipment')"
                                            class="text-xs bg-orange-600 hover:bg-orange-500 text-white px-2 py-1 rounded">
                                            + Tambah
                                        </button>
                                    </td>
                                </tr>
                                @php $equipmentItems = collect($calculation['breakdown'])->where('type', 'equipment'); @endphp
                                @forelse($equipmentItems as $index => $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 group">
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $item['name'] }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $item['code'] ?? '-' }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $item['unit'] }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100 text-right">
                                            {{ number_format($item['coefficient'], 4) }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100 text-right">
                                            {{ number_format($item['price'], 0, ',', '.') }}
                                        </td>
                                        <td
                                            class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100 text-right font-medium">
                                            {{ number_format($item['amount'], 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-center">
                                            <div
                                                class="flex justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button @click="editComponent(@js($item))"
                                                    class="text-yellow-600 hover:text-yellow-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                    </svg>
                                                </button>
                                                <button @click="deleteComponent(@js($item['id']))"
                                                    class="text-red-600 hover:text-red-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-2 text-sm text-gray-500 text-center italic">Tidak ada
                                            komponen peralatan</td>
                                    </tr>
                                @endforelse
                                <tr class="bg-orange-100 dark:bg-orange-900/40">
                                    <td colspan="6"
                                        class="px-4 py-2 text-right font-semibold text-orange-800 dark:text-orange-200">
                                        Jumlah Harga Peralatan</td>
                                    <td class="px-4 py-2 text-right font-bold text-orange-800 dark:text-orange-200">
                                        {{ number_format($calculation['equipment_cost'], 2, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>

                                <!-- Totals -->
                                <tr class="bg-gray-100 dark:bg-gray-700">
                                    <td colspan="6"
                                        class="px-4 py-2 text-right font-semibold text-gray-800 dark:text-gray-200">D.
                                        Jumlah (A + B + C)
                                    </td>
                                    <td class="px-4 py-2 text-right font-bold text-gray-800 dark:text-gray-200">
                                        {{ number_format($calculation['subtotal'], 2, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>
                                <tr class="bg-gray-100 dark:bg-gray-700">
                                    <td colspan="6"
                                        class="px-4 py-2 text-right font-semibold text-gray-800 dark:text-gray-200">E.
                                        Overhead & Keuntungan
                                        ({{ $calculation['overhead_percentage'] }}%)</td>
                                    <td class="px-4 py-2 text-right font-bold text-gray-800 dark:text-gray-200">
                                        {{ number_format($calculation['overhead_cost'], 2, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>
                                <tr class="bg-indigo-100 dark:bg-indigo-900/40">
                                    <td colspan="6"
                                        class="px-4 py-3 text-right font-bold text-indigo-800 dark:text-indigo-200 text-lg">
                                        F. Harga Satuan Pekerjaan (D + E)</td>
                                    <td
                                        class="px-4 py-3 text-right font-bold text-indigo-800 dark:text-indigo-200 text-lg">
                                        {{ number_format($calculation['unit_price'], 2, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal for Add/Edit -->
            <x-dialog-modal id="componentModal" name="componentModal" :show="false" focusable>
                <x-slot name="title">
                    <span x-text="isEdit ? 'Edit Komponen' : 'Tambah Komponen'"></span>
                    <span x-text="getTypeLabel(form.component_type)"
                        class="ml-1 text-gray-500 text-sm font-normal"></span>
                </x-slot>

                <x-slot name="content">
                    <form id="componentForm" method="POST" :action="actionUrl">
                        @csrf
                        <template x-if="isEdit">
                            @method('PUT')
                        </template>

                        <input type="hidden" name="component_type" x-model="form.component_type">
                        <input type="hidden" name="create_new_price" x-model="form.create_new_price">
                        <input type="hidden" name="region_code" x-model="form.region_code">
                        <input type="hidden" name="region_name" x-model="form.region_name">

                        <div class="space-y-4">
                            <!-- Helper Text -->
                            <div x-show="!isEdit && form.create_new_price"
                                class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                                role="alert">
                                <span class="font-medium">Komponen Baru!</span> Komponen ini belum ada di data harga
                                dasar. Silakan lengkapi harga dan kategori untuk menyimpannya ke master data.
                            </div>

                            <!-- Name / Search -->
                            <div class="relative">
                                <x-label for="name" value="Uraian / Nama Komponen" />
                                <div class="relative mt-1">
                                    <x-input id="name" type="text" name="name" class="block w-full" x-model="form.name"
                                        @input.debounce.300ms="search()" @keydown.escape="showResults = false"
                                        autocomplete="off" required />

                                    <!-- Search Results Dropdown -->
                                    <div x-show="showResults && searchResults.length > 0"
                                        @click.away="showResults = false"
                                        class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-700 rounded-md shadow-lg max-h-60 overflow-y-auto border border-gray-200 dark:border-gray-600">
                                        <template x-for="item in searchResults" :key="item.id">
                                            <div @click="selectItem(item)"
                                                class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer text-sm">
                                                <div class="font-medium text-gray-900 dark:text-gray-100"
                                                    x-text="item.name"></div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    <span x-text="item.code || '-'"></span> |
                                                    <span x-text="item.unit"></span> |
                                                    Rp <span
                                                        x-text="new Intl.NumberFormat('id-ID').format(item.price)"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- No Results / Create New Link -->
                                    <div x-show="showResults && searchResults.length === 0 && form.name.length > 0"
                                        class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-700 rounded-md shadow-lg border border-gray-200 dark:border-gray-600 p-2 text-center">
                                        <button type="button" @click="enableCreateMode()"
                                            class="text-sm text-blue-600 hover:text-blue-500 dark:text-yellow-200 dark:hover:text-blue-500">
                                            + Buat komponen baru: "<span x-text="form.name"></span>"
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Code -->
                            <div>
                                <x-label for="code" value="Kode" />
                                <x-input id="code" type="text" name="code"
                                    class="mt-1 block w-full bg-gray-50 dark:bg-gray-900" x-model="form.code"
                                    x-bind:readonly="!form.create_new_price && !isEdit" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Unit -->
                                <div>
                                    <x-label for="unit" value="Satuan" />
                                    <x-input id="unit" type="text" name="unit"
                                        class="mt-1 block w-full bg-gray-50 dark:bg-gray-900" x-model="form.unit"
                                        x-bind:readonly="!form.create_new_price && !isEdit" required />
                                </div>

                                <!-- Coefficient -->
                                <div>
                                    <x-label for="coefficient" value="Koefisien" />
                                    <x-input id="coefficient" type="number" step="0.0001" name="coefficient"
                                        class="mt-1 block w-full" x-model="form.coefficient" required />
                                </div>
                            </div>

                            <!-- Extra Fields for New Item -->
                            <div x-show="form.create_new_price"
                                class="grid grid-cols-2 gap-4 border-t pt-4 dark:border-gray-600">
                                <div>
                                    <x-label for="price" value="Harga Satuan (Rp)" />
                                    <x-input id="price" type="number" step="0.01" name="price" class="mt-1 block w-full"
                                        x-model="form.price" x-bind:required="form.create_new_price" />
                                </div>
                                <div>
                                    <x-label for="category" value="Kategori" />
                                    <x-input id="category" type="text" name="category" class="mt-1 block w-full"
                                        x-model="form.category" placeholder="Contoh: Semen"
                                        x-bind:required="form.create_new_price" />
                                </div>
                            </div>
                        </div>
                    </form>
                </x-slot>

                <x-slot name="footer">
                    <x-secondary-button x-on:click="$dispatch('close-modal', 'componentModal')">
                        Batal
                    </x-secondary-button>

                    <x-button class="ml-2" @click="document.getElementById('componentForm').submit()">
                        Simpan
                    </x-button>
                </x-slot>
            </x-dialog-modal>

            <!-- Modal for Delete -->
            <x-confirmation-modal id="deleteModal" name="deleteModal" :show="false">
                <x-slot name="title">
                    Hapus Komponen
                </x-slot>

                <x-slot name="content">
                    Apakah Anda yakin ingin menghapus komponen ini? Tindakan ini tidak dapat dibatalkan.
                    <form id="deleteForm" method="POST" :action="deleteUrl">
                        @csrf
                        @method('DELETE')
                    </form>
                </x-slot>

                <x-slot name="footer">
                    <x-secondary-button x-on:click="$dispatch('close-modal', 'deleteModal')">
                        Batal
                    </x-secondary-button>

                    <x-danger-button class="ml-2" @click="document.getElementById('deleteForm').submit()">
                        Hapus
                    </x-danger-button>
                </x-slot>
            </x-confirmation-modal>

        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('componentManager', () => ({
                    isEdit: false,
                    showResults: false,
                    searchResults: [],
                    searchTimeout: null,
                    actionUrl: '',
                    deleteUrl: '',
                    currentRegion: '{{ $regionCode }}',

                    form: {
                        component_type: '',
                        name: '',
                        code: '',
                        unit: '',
                        coefficient: 0,
                        create_new_price: false,
                        price: 0,
                        category: '',
                        region_code: '{{ $regionCode }}',
                        region_name: '{{ $regions[$regionCode] ?? $regionCode }}',
                    },
                    baseUrl: "{{ route('ahsp.show', $ahspWorkType) }}",
                    searchUrl: "{{ route('ahsp.prices.search') }}",

                    openModal(type) {
                        this.resetForm();
                        this.form.component_type = type;
                        this.form.region_code = this.currentRegion;
                        this.isEdit = false;
                        this.actionUrl = `${this.baseUrl}/components`;
                        this.$dispatch('open-modal', 'componentModal');
                    },

                    editComponent(item) {
                        this.resetForm();
                        // Set all fields
                        this.form.component_type = item.type;
                        this.form.name = item.name;
                        this.form.code = item.code;
                        this.form.unit = item.unit;
                        this.form.coefficient = item.coefficient;
                        // existing item, so no new price creation
                        this.form.create_new_price = false;

                        this.isEdit = true;
                        this.actionUrl = `${this.baseUrl}/components/${item.id}`;
                        this.$dispatch('open-modal', 'componentModal');
                    },

                    deleteComponent(id) {
                        this.deleteUrl = `${this.baseUrl}/components/${id}`;
                        this.$dispatch('open-modal', 'deleteModal');
                    },

                    // Search Logic
                    search() {
                        // if (this.isEdit) return; // Allow search on edit for renaming support
                        if (this.form.name.length < 2) {
                            this.searchResults = [];
                            this.showResults = false;
                            return;
                        }

                        clearTimeout(this.searchTimeout);
                        this.searchTimeout = setTimeout(() => {
                            fetch(`${this.searchUrl}?q=${encodeURIComponent(this.form.name)}&type=${this.form.component_type}&region=${this.currentRegion}`)
                                .then(response => response.json())
                                .then(data => {
                                    this.searchResults = data;
                                    this.showResults = true;
                                });
                        }, 300);
                    },

                    selectItem(item) {
                        this.form.name = item.name;
                        this.form.code = item.code;
                        this.form.unit = item.unit;
                        this.form.create_new_price = false;
                        this.showResults = false;
                    },

                    enableCreateMode() {
                        // Pre-fill creation defaults
                        this.form.create_new_price = true;
                        this.form.category = '';
                        this.form.price = 0;
                        this.showResults = false;
                        // Focus on unit as next logical step
                        setTimeout(() => document.getElementById('unit').focus(), 100);
                    },

                    resetForm() {
                        this.form = {
                            component_type: '',
                            name: '',
                            code: '',
                            unit: '',
                            coefficient: 0,
                            create_new_price: false,
                            price: 0,
                            category: '',
                            region_code: this.currentRegion,
                            region_name: '{{ $regions[$regionCode] ?? $regionCode }}',
                        };
                        this.searchResults = [];
                        this.showResults = false;
                    },

                    getTypeLabel(type) {
                        const types = {
                            'labor': 'Tenaga Kerja',
                            'material': 'Bahan',
                            'equipment': 'Peralatan'
                        };
                        return types[type] || type;
                    }
                }));
            });
        </script>
    @endpush
</x-app-layout>