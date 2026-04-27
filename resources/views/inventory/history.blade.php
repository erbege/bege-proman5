<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Riwayat Stok (Inventory Logs)') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div class="mb-6 bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <form method="GET" action="{{ route('inventory.history') }}" class="flex gap-4 items-end">
                    <div class="flex-1">
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
                    <div class="flex-1">
                        <x-input-label for="type" value="Tipe Transaksi" />
                        <select id="type" name="type" class="mt-1 block w-full border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-gray-300 focus:border-gold-500 dark:focus:border-gold-600 focus:ring-gold-500 dark:focus:ring-gold-600 rounded-md shadow-sm">
                            <option value="">Semua Tipe</option>
                            <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Barang Masuk</option>
                            <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Barang Keluar</option>
                            <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                        </select>
                    </div>
                    <div>
                        <x-primary-button type="submit">Filter</x-primary-button>
                    </div>
                    <div>
                        <a href="{{ route('inventory.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Kembali ke Stok
                        </a>
                    </div>
                </form>
            </div>

            <!-- Logs Table -->
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-dark-700">
                            <tr>
                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Proyek</th>
                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Material</th>
                                <th class="px-3 py-1.5 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipe</th>
                                <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jumlah</th>
                                @can('financials.view')
                                <th class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Harga Satuan</th>
                                @endcan
                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Catatan</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-dark-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($logs as $log)
                                <tr>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $log->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $log->inventory->project->name ?? '-' }}
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $log->inventory->material->name ?? '-' }}
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-center">
                                        @if($log->type == 'in')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Masuk</span>
                                        @elseif($log->type == 'out')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Keluar</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Adjustment</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-right text-sm font-bold text-gray-900 dark:text-white">
                                        {{ number_format($log->quantity, 2) }} {{ $log->inventory->material->unit ?? '' }}
                                    </td>
                                    @can('financials.view')
                                    <td class="px-3 py-1.5 whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                                        Rp {{ number_format($log->unit_cost ?? 0, 0, ',', '.') }}
                                    </td>
                                    @endcan
                                    <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $log->createdBy->name ?? 'System' }}
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-normal text-sm text-gray-500 dark:text-gray-400">
                                        {{ $log->notes }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-1.5 text-center text-gray-500 dark:text-gray-400">
                                        Belum ada riwayat transaksi
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


