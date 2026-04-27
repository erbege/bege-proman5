<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Manajemen Stok']
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manajemen Stok (Inventory)') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div class="mb-4 bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <form method="GET" action="{{ route('inventory.index') }}" class="flex gap-4 items-end">
                    <div class="flex-1">
                        <x-input-label for="search" value="Cari Material" />
                        <x-text-input id="search" name="search" type="text" class="mt-1 block w-full" :value="request('search')" placeholder="Nama atau kode..." />
                    </div>
                    <div>
                        <x-input-label for="project_id" value="Proyek" />
                        <select id="project_id" name="project_id" class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">
                            <option value="">Semua Proyek</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-primary-button type="submit">Filter</x-primary-button>
                    </div>
                </form>
            </div>

            <!-- Inventory Table -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Data Stok Material</h3>
                        <a href="{{ route('inventory.history') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                            Lihat Riwayat Log &rarr;
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-dark-700">
                            <tr>
                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Proyek</th>
                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Material</th>
                                <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stok Saat Ini</th>
                                <th class="px-3 py-1.5 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-dark-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($inventories as $inventory)
                                <tr>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $inventory->project->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $inventory->material?->name ?? 'Material Dihapus' }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $inventory->material?->code ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-right text-sm font-bold text-gray-900 dark:text-white">
                                        {{ number_format($inventory->quantity, 2) }} {{ $inventory->material?->unit ?? '' }}
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-center text-sm font-medium">
                                        <button x-data=""
                                                x-on:click.prevent="$dispatch('open-modal', 'adjust-stock-{{ $inventory->id }}')"
                                                class="text-gold-600 dark:text-gold-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                            Sesuaikan
                                        </button>

                                        <!-- Adjustment Modal -->
                                        <x-modal name="adjust-stock-{{ $inventory->id }}" focusable>
                                            <form method="POST" action="{{ route('inventory.adjust', $inventory) }}" class="p-4 text-left">
                                                @csrf
                                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                    Sesuaikan Stok: {{ $inventory->material?->name ?? 'Material' }}
                                                </h2>
                                                
                                                <div class="mt-4">
                                                    <x-input-label for="type" value="Jenis Penyesuaian" />
                                                    <select name="type" class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">
                                                        <option value="in">Masuk (In)</option>
                                                        <option value="out">Keluar (Out)</option>
                                                        <option value="adjustment">Set Ulang (Opname)</option>
                                                    </select>
                                                </div>

                                                <div class="mt-4">
                                                    <x-input-label for="quantity" value="Jumlah" />
                                                    <x-text-input name="quantity" type="number" step="0.01" class="mt-1 block w-full" required />
                                                </div>

                                                <div class="mt-4">
                                                    <x-input-label for="notes" value="Catatan / Alasan" />
                                                    <textarea name="notes" class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm" rows="3" required></textarea>
                                                </div>

                                                <div class="mt-6 flex justify-end">
                                                    <x-secondary-button x-on:click="$dispatch('close')">
                                                        Batal
                                                    </x-secondary-button>

                                                    <x-primary-button class="ml-3">
                                                        Simpan
                                                    </x-primary-button>
                                                </div>
                                            </form>
                                        </x-modal>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-1.5 text-center text-gray-500 dark:text-gray-400">
                                        Data tidak ditemukan
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $inventories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


