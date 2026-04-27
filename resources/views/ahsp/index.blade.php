<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Master AHSP']
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Analisa Harga Satuan Pekerjaan (AHSP)') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('ahsp.prices.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                    Harga Satuan Dasar
                </a>
                <a href="{{ route('ahsp.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tambah AHSP
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Search & Filter -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-4">
                    <form method="GET" action="{{ route('ahsp.index') }}" class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari kode atau nama pekerjaan..."
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <select name="category_id"
                                class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category['id'] }}" {{ request('category_id') == $category['id'] ? 'selected' : '' }}>
                                        {{ $category['display_name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500">
                                Cari
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Work Types Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Kode</th>
                                    <th
                                        class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Nama Pekerjaan</th>
                                    <th
                                        class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Satuan</th>
                                    <th
                                        class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Kategori</th>
                                    <th
                                        class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Sumber</th>
                                    <th
                                        class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($workTypes as $workType)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td
                                            class="px-3 py-1.5 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $workType->code }}
                                        </td>
                                        <td class="px-3 py-1.5 text-sm text-gray-900 dark:text-gray-100">
                                            <a href="{{ route('ahsp.show', $workType) }}"
                                                class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                                {{ Str::limit($workType->name, 60) }}
                                            </a>
                                        </td>
                                        <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $workType->unit }}
                                        </td>
                                        <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $workType->category?->name ?? '-' }}
                                        </td>
                                        <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <span
                                                class="px-2 py-1 text-xs rounded-full bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-100">
                                                {{ $workType->source }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-1.5 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('ahsp.show', $workType) }}"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 mr-3">
                                                Lihat
                                            </a>
                                            <a href="{{ route('ahsp.edit', $workType) }}"
                                                class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-900 mr-3">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3 py-1.5 text-center text-gray-500 dark:text-gray-400">
                                            Belum ada data AHSP. <a href="{{ route('ahsp.create') }}"
                                                class="text-indigo-600 hover:underline">Tambah sekarang</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $workTypes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


