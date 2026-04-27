<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Purchase Order']
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Purchase Orders - {{ $project->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->code }}</p>
            </div>
            <a href="{{ route('projects.po.create', $project) }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                Buat PO Baru
            </a>
        </div>
    </x-slot>

    @include('projects.navigation')

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 text-gray-900 dark:text-gray-100">
                    @if($orders->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-dark-700">
                                    <tr>
                                        <th
                                            class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            No. PO</th>
                                        <th
                                            class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Supplier</th>
                                        <th
                                            class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Tanggal</th>
                                        <th
                                            class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Tgl Kirim</th>
                                        <th
                                            class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Total</th>
                                        <th
                                            class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-dark-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($orders as $po)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                            <td
                                                class="px-3 py-1.5 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $po->po_number }}
                                            </td>
                                            <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $po->supplier->name }}
                                            </td>
                                            <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $po->order_date->format('d M Y') }}
                                            </td>
                                            <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $po->expected_delivery->format('d M Y') }}
                                            </td>
                                            <td
                                                class="px-3 py-1.5 whitespace-nowrap text-right text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $po->formatted_total_amount }}
                                            </td>
                                            <td class="px-3 py-1.5 whitespace-nowrap">
                                                @php
                                                    $colors = [
                                                        'draft' => 'bg-gray-100 text-gray-800',
                                                        'sent' => 'bg-blue-100 text-blue-800',
                                                        'partial' => 'bg-yellow-100 text-yellow-800',
                                                        'received' => 'bg-green-100 text-green-800',
                                                        'cancelled' => 'bg-red-100 text-red-800',
                                                    ];
                                                @endphp
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colors[$po->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ $po->status_label }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-1.5 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('projects.po.show', [$project, $po]) }}"
                                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">Detail</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-4">
                                {{ $orders->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <x-heroicon-o-shopping-bag class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Belum ada Purchase Order</h3>
                            <div class="mt-6">
                                <a href="{{ route('projects.po.create', $project) }}"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                                    Buat PO Baru
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


