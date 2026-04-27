<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Master AHSP', 'url' => route('ahsp.index')],
        ['label' => 'Harga Satuan Dasar']
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Harga Satuan Dasar') }}
            </h2>
            <div class="flex gap-2" x-data="{ 
                confirmOpen: false, 
                uploading: false, 
                progress: 0,
                startSync() {
                    this.confirmOpen = false;
                    this.uploading = true;
                    this.progress = 0;
                    let interval = setInterval(() => {
                        if (this.progress < 90) {
                            this.progress += Math.floor(Math.random() * 5) + 1;
                        } else {
                            clearInterval(interval);
                        }
                    }, 500);
                    document.getElementById('syncForm').submit();
                }
            }">
                <!-- Sync Trigger Button -->
                <button type="button" @click="confirmOpen = true"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Sync Material
                </button>

                <!-- Hidden Form -->
                <form id="syncForm" action="{{ route('ahsp.prices.sync') }}" method="POST" class="hidden">
                    @csrf
                </form>

                <!-- Confirmation Modal -->
                <div x-show="confirmOpen" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 backdrop-blur-sm"
                     style="display: none;">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 p-4" @click.away="confirmOpen = false">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-green-100 dark:bg-green-900 rounded-full mb-4">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-center text-gray-900 dark:text-gray-100 mb-2">Konfirmasi Sinkronisasi</h3>
                        <p class="text-sm text-center text-gray-500 dark:text-gray-400 mb-6">
                            Apakah Anda yakin ingin menyinkronkan data material? <br>
                            Proses ini akan memperbarui harga di <strong>Master Material</strong> berdasarkan data AHSP saat ini.
                        </p>
                        <div class="flex justify-center gap-3">
                            <button @click="confirmOpen = false" 
                                class="px-4 py-2 bg-gray-200 dark:bg-gray-600 rounded-md text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-300 dark:hover:bg-gray-500">
                                Batal
                            </button>
                            <button @click="startSync()" 
                                class="px-4 py-2 bg-green-600 text-white rounded-md font-medium hover:bg-green-500 shadow-lg">
                                Ya, Sinkronkan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Progress Modal -->
                <div x-show="uploading" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 backdrop-blur-sm"
                     style="display: none;">
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-2xl flex flex-col items-center max-w-sm w-full mx-4">
                        <!-- Circular Progress -->
                        <div class="relative w-32 h-32 mb-4">
                            <svg class="w-full h-full transform -rotate-90">
                                <!-- Background Circle -->
                                <circle cx="64" cy="64" r="56" 
                                        class="text-gray-200 dark:text-gray-700" 
                                        stroke="currentColor" 
                                        stroke-width="12" 
                                        fill="none" />
                                <!-- Progress Circle -->
                                <circle cx="64" cy="64" r="56" 
                                        class="text-green-600 dark:text-green-500 transition-all duration-300 ease-out" 
                                        stroke="currentColor" 
                                        stroke-width="12" 
                                        fill="none" 
                                        :stroke-dasharray="2 * Math.PI * 56" 
                                        :stroke-dashoffset="2 * Math.PI * 56 * (1 - progress / 100)" 
                                        stroke-linecap="round" />
                            </svg>
                            <!-- Percentage Text -->
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-2xl font-bold text-gray-800 dark:text-white" x-text="progress + '%'"></span>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Menyinkronkan Data...</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center">Sedang mencocokkan harga AHSP dengan Master Material.</p>
                    </div>
                </div>

                <a href="{{ route('ahsp.prices.import') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Import Harga
                </a>
                <a href="{{ route('ahsp.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Kembali ke AHSP
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Search & Filter -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-4">
                    <form method="GET" class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari nama atau kode..."
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        </div>
                        <div>
                            <select name="type"
                                class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                <option value="">Semua Tipe</option>
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="region"
                                class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                <option value="">Semua Wilayah</option>
                                @foreach($regions as $code => $name)
                                    <option value="{{ $code }}" {{ request('region') == $code ? 'selected' : '' }}>{{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500">Cari</button>
                    </form>
                </div>
            </div>

            <!-- Prices Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Nama</th>
                                    <th
                                        class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Kode</th>
                                    <th
                                        class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Tipe</th>
                                    <th
                                        class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Satuan</th>
                                    <th
                                        class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Wilayah</th>
                                    <th
                                        class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Harga (Rp)</th>
                                    <th
                                        class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Berlaku</th>
                                    <th
                                        class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($prices as $price)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-3 py-1.5 text-sm text-gray-900 dark:text-gray-100">
                                            {{ Str::limit($price->name, 40) }}
                                        </td>
                                        <td class="px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $price->code ?? '-' }}
                                        </td>
                                        <td class="px-3 py-1.5 text-sm">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                        {{ $price->component_type == 'labor' ? 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100' : '' }}
                                                        {{ $price->component_type == 'material' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : '' }}
                                                        {{ $price->component_type == 'equipment' ? 'bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-100' : '' }}
                                                    ">
                                                {{ $price->type_label }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400">{{ $price->unit }}
                                        </td>
                                        <td class="px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $price->region_name ?? $price->region_code }}
                                        </td>
                                        <td
                                            class="px-3 py-1.5 text-sm text-gray-900 dark:text-gray-100 text-right font-medium">
                                            {{ number_format($price->price, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $price->effective_date->format('d/m/Y') }}
                                        </td>
                                        <td class="px-3 py-1.5 text-right">
                                            <button
                                                onclick="editPrice({{ $price->id }}, '{{ $price->name }}', {{ $price->price }})"
                                                class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-3 py-2 text-center text-gray-500">
                                            Belum ada data harga. <a href="{{ route('ahsp.prices.import') }}"
                                                class="text-indigo-600 hover:underline">Import sekarang</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $prices->links() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Price Modal -->
    <div id="editPriceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 p-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Edit Harga</h3>
            <form id="editPriceForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama</label>
                    <input type="text" id="editPriceName" disabled
                        class="w-full rounded-md border-gray-300 bg-gray-100 dark:bg-gray-700">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Baru
                        (Rp)</label>
                    <input type="number" name="price" id="editPriceValue" required
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alasan
                        Perubahan</label>
                    <input type="text" name="reason" placeholder="Opsional"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-600 rounded-md">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function editPrice(id, name, price) {
                document.getElementById('editPriceForm').action = '/ahsp/prices/' + id;
                document.getElementById('editPriceName').value = name;
                document.getElementById('editPriceValue').value = price;
                document.getElementById('editPriceModal').classList.remove('hidden');
                document.getElementById('editPriceModal').classList.add('flex');
            }
            function closeEditModal() {
                document.getElementById('editPriceModal').classList.add('hidden');
                document.getElementById('editPriceModal').classList.remove('flex');
            }
        </script>
    @endpush
</x-app-layout>


