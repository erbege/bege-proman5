<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Daftar Proyek']
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Daftar Proyek') }}
            </h2>
            <a href="{{ route('projects.create') }}"
                class="inline-flex items-center px-4 py-2 bg-success-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-success-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                Tambah Proyek
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-dark-700">
                            <tr>
                                <th
                                    class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Kode</th>
                                <th
                                    class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Nama Proyek</th>
                                <th
                                    class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Klien</th>
                                <th
                                    class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Tipe</th>
                                <th
                                    class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status</th>
                                @can('financials.view')
                                <th
                                    class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Nilai Kontrak</th>
                                @endcan
                                <th
                                    class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-dark-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($projects as $project)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td
                                        class="px-3 py-1.5 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $project->code }}
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap">
                                        <a href="{{ route('projects.show', $project) }}"
                                            class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                            {{ $project->name }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $project->client_name }}
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ ucfirst($project->type) }}
                                    </td>
                                    <td class="px-3 py-1.5 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                            @if($project->status === 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                                            @elseif($project->status === 'completed') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                                            @elseif($project->status === 'on_hold') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                                            @elseif($project->status === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                                                            @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-300
                                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                        </span>
                                    </td>
                                    @can('financials.view')
                                    <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $project->formatted_contract_value }}
                                    </td>
                                    @endcan
                                    <td class="px-3 py-1.5 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('projects.show', $project) }}"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 mr-3">Lihat</a>
                                        <a href="{{ route('projects.edit', $project) }}"
                                            class="text-gold-600 hover:text-indigo-900 dark:text-gold-400 mr-3">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->can('financials.view') ? 7 : 6 }}" class="px-3 py-1.5 text-center text-gray-500 dark:text-gray-400">
                                        Belum ada proyek. <a href="{{ route('projects.create') }}"
                                            class="text-blue-600 hover:underline">Buat proyek baru</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $projects->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


