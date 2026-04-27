<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Purchase Request']
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Purchase Requests - {{ $project->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->code }}</p>
            </div>
            @can('procurement.manage')
            <div class="flex gap-2">
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <x-heroicon-o-document-arrow-down class="w-4 h-4 mr-2" />
                        Dari MR
                    </button>
                    <div x-show="open" @click.away="open = false"
                        class="absolute right-0 mt-2 w-64 bg-white dark:bg-dark-800 rounded-md shadow-lg z-50 py-1 border border-gray-200 dark:border-dark-700">
                        @forelse($approvedMrs as $mr)
                            <a href="{{ route('projects.pr.create', [$project, 'from_mr' => $mr->id]) }}"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700">
                                {{ $mr->code }} ({{ $mr->items->count() }} items)
                            </a>
                        @empty
                            <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 italic">
                                Tidak ada MR yang disetujui (Approved) untuk proyek ini.
                            </div>
                        @endforelse
                    </div>
                </div>
                <a href="{{ route('projects.pr.create', $project) }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                    Buat PR Baru
                </a>
            </div>
            @endcan
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
                    @if($prs->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-dark-700">
                                    <tr>
                                        <th
                                            class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            No. PR</th>
                                        <th
                                            class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Tanggal</th>
                                        <th
                                            class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Tgl Dibutuhkan</th>
                                        <th
                                            class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Prioritas</th>
                                        <th
                                            class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-dark-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($prs as $pr)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                            <td
                                                class="px-3 py-1.5 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $pr->pr_number }}
                                            </td>
                                            <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $pr->request_date->format('d M Y') }}
                                            </td>
                                            <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $pr->required_date->format('d M Y') }}
                                            </td>
                                            <td class="px-3 py-1.5 whitespace-nowrap">
                                                @php
                                                    $prioColors = [
                                                        'low' => 'text-gray-600',
                                                        'normal' => 'text-blue-600',
                                                        'high' => 'text-orange-600 font-bold',
                                                        'urgent' => 'text-red-600 font-bold',
                                                    ];
                                                @endphp
                                                <span class="text-xs {{ $prioColors[$pr->priority] ?? '' }}">
                                                    {{ strtoupper($pr->priority) }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-1.5 whitespace-nowrap">
                                                @php
                                                    $colors = [
                                                        'draft' => 'bg-gray-100 text-gray-800',
                                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                                        'approved' => 'bg-green-100 text-green-800',
                                                        'rejected' => 'bg-red-100 text-red-800',
                                                        'completed' => 'bg-blue-100 text-blue-800',
                                                    ];
                                                @endphp
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colors[$pr->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ ucfirst($pr->status) }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-1.5 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('projects.pr.show', [$project, $pr]) }}"
                                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">Detail</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-4">
                                {{ $prs->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <x-heroicon-o-shopping-cart class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Belum ada Purchase Request
                            </h3>
                            <div class="mt-6 flex justify-center gap-2">
                                @can('procurement.manage')
                                <div x-data="{ open: false }" class="relative text-left">
                                    <button @click="open = !open"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                        <x-heroicon-o-document-arrow-down class="w-4 h-4 mr-2" />
                                        Dari MR
                                    </button>
                                    <div x-show="open" @click.away="open = false"
                                        class="absolute left-0 mt-2 w-64 bg-white dark:bg-dark-800 rounded-md shadow-lg z-50 py-1 border border-gray-200 dark:border-dark-700">
                                        @forelse($approvedMrs as $mr)
                                            <a href="{{ route('projects.pr.create', [$project, 'from_mr' => $mr->id]) }}"
                                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700">
                                                {{ $mr->code }} ({{ $mr->items->count() }} items)
                                            </a>
                                        @empty
                                            <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 italic">
                                                Tidak ada MR yang disetujui (Approved) untuk proyek ini.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                                <a href="{{ route('projects.pr.create', $project) }}"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                                    Buat PR Baru
                                </a>
                                @else
                                <p class="text-sm text-gray-500">Anda tidak memiliki hak akses untuk membuat Purchase Request.</p>
                                @endcan
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


