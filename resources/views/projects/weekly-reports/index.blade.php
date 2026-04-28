<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Weekly Reports']
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Weekly Reports - {{ $project->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->code }}</p>
            </div>
            <div class="flex space-x-2">
                <!-- Auto Generate All -->
                @can('weekly_report.manage')
                <form action="{{ route('projects.weekly-reports.auto-generate-all', $project) }}" method="POST"
                    class="inline">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Auto Generate All
                    </button>
                </form>
                @endcan

                <!-- Create New -->
                @can('weekly_report.manage')
                <a href="{{ route('projects.weekly-reports.create', $project) }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Buat Weekly Report
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div
                    class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('info'))
                <div
                    class="mb-4 p-4 bg-blue-100 dark:bg-blue-900/30 border border-blue-400 dark:border-blue-700 text-blue-700 dark:text-blue-300 rounded-lg">
                    {{ session('info') }}
                </div>
            @endif

            @if ($errors->any())
                <div
                    class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Info Card -->
            <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm text-blue-700 dark:text-blue-300">
                        Minggu saat ini: <strong>Week {{ $currentWeek }}</strong> |
                        Weekly report berikutnya: <strong>Week {{ $nextWeek }}</strong>
                    </span>
                </div>
            </div>

            <!-- Reports Table with Bulk Delete -->
            <div x-data="{ showDeleteModal: false }">
                <form id="bulk-delete-form" action="{{ route('projects.weekly-reports.bulk-destroy', $project) }}"
                    method="POST">
                    @csrf

                    <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <!-- Bulk Actions Bar -->
                        <div id="bulk-actions"
                            class="hidden px-3 py-1.5 bg-gray-50 dark:bg-dark-700 border-b border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-300">
                                    <span id="selected-count">0</span> item dipilih
                                </span>
                                <button type="button" @click="showDeleteModal = true"
                                    class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Hapus Terpilih
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-dark-700">
                                    <tr>
                                        <th class="px-3 py-1.5 text-center w-12">
                                            <input type="checkbox" id="select-all"
                                                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-dark-700">
                                        </th>
                                        <th
                                            class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Week
                                        </th>
                                        <th
                                            class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Periode
                                        </th>
                                        <th
                                            class="px-3 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Title
                                        </th>
                                        <th
                                            class="px-3 py-1.5 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th
                                            class="px-3 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-dark-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($reports as $report)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-dark-700">
                                            <td class="px-3 py-1.5 text-center">
                                                <input type="checkbox" name="ids[]" value="{{ $report->id }}"
                                                    class="report-checkbox rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-dark-700">
                                            </td>
                                            <td class="px-3 py-1.5 whitespace-nowrap">
                                                <span
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-100 font-bold text-xs">
                                                    {{ $report->week_number }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-1.5 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                {{ $report->period_label }}
                                            </td>
                                            <td class="px-3 py-1.5 text-sm text-gray-900 dark:text-white">
                                                {{ $report->cover_title ?? 'Weekly Report Week ' . $report->week_number }}
                                            </td>
                                            <td class="px-3 py-1.5 whitespace-nowrap text-center">
                                                <span
                                                    class="px-2 py-1 text-xs font-medium rounded-full 
                                                                         @if($report->status === 'published') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                                         @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @endif">
                                                    {{ $report->status_label }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-1.5 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end space-x-2">
                                                    <a href="{{ route('projects.weekly-reports.show', [$project, $report]) }}"
                                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                                        title="View">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                    </a>
                                                    <a href="{{ route('projects.weekly-reports.pdf', [$project, $report]) }}"
                                                        class="text-red-600 hover:text-red-900 dark:text-red-400"
                                                        title="Download PDF">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                    </a>
                                                    @can('weekly_report.manage')
                                                    <a href="{{ route('projects.weekly-reports.edit', [$project, $report]) }}"
                                                        class="text-gray-600 hover:text-gray-900 dark:text-gray-400"
                                                        title="Edit">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </a>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-3 py-1.5 text-center text-gray-500 dark:text-gray-400">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="mt-2">Belum ada weekly report.</p>
                                                @can('weekly_report.manage')
                                                <a href="{{ route('projects.weekly-reports.create', $project) }}"
                                                    class="mt-4 inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                                                    Buat Weekly Report Pertama
                                                </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($reports->hasPages())
                            <div class="px-3 py-1.5 border-t border-gray-200 dark:border-gray-700">
                                {{ $reports->links() }}
                            </div>
                        @endif
                    </div>

                    <!-- Bulk Delete Confirmation Modal -->
                    <template x-teleport="body">
                        <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
                            aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div
                                class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                                    @click="showDeleteModal = false"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                <div
                                    class="relative inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                    <div class="absolute top-0 right-0 pt-4 pr-4">
                                        <button type="button" @click="showDeleteModal = false"
                                            class="text-gray-400 hover:text-gray-500">
                                            <x-heroicon-o-x-circle class="w-6 h-6" />
                                        </button>
                                    </div>
                                    <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-4">
                                        <div class="sm:flex sm:items-start">
                                            <div
                                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600" />
                                            </div>
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                                    Hapus Weekly Report</h3>
                                                <div class="mt-2">
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        Apakah Anda yakin ingin menghapus <strong
                                                            id="modal-selected-count">0</strong> weekly report yang
                                                        dipilih?
                                                    </p>
                                                    <p class="text-sm text-orange-600 dark:text-orange-400 mt-2">
                                                        <x-heroicon-o-exclamation-triangle
                                                            class="w-4 h-4 inline mr-1" />
                                                        Tindakan ini tidak dapat dibatalkan.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-gray-50 dark:bg-dark-700 px-3 py-1.5 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button type="submit" form="bulk-delete-form"
                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                            Hapus
                                        </button>
                                        <button type="button" @click="showDeleteModal = false"
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600 dark:hover:bg-gray-700">
                                            Batal
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const selectAll = document.getElementById('select-all');
                const checkboxes = document.querySelectorAll('.report-checkbox');
                const bulkActions = document.getElementById('bulk-actions');
                const selectedCount = document.getElementById('selected-count');
                const modalSelectedCount = document.getElementById('modal-selected-count');

                function updateBulkActions() {
                    const checked = document.querySelectorAll('.report-checkbox:checked');
                    selectedCount.textContent = checked.length;
                    modalSelectedCount.textContent = checked.length;
                    bulkActions.classList.toggle('hidden', checked.length === 0);
                }

                selectAll.addEventListener('change', function () {
                    checkboxes.forEach(cb => cb.checked = this.checked);
                    updateBulkActions();
                });

                checkboxes.forEach(cb => {
                    cb.addEventListener('change', function () {
                        const allChecked = document.querySelectorAll('.report-checkbox:checked').length === checkboxes.length;
                        selectAll.checked = allChecked;
                        selectAll.indeterminate = !allChecked && document.querySelectorAll('.report-checkbox:checked').length > 0;
                        updateBulkActions();
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>


