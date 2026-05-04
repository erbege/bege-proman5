<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Review Progress Report
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $report->report_code }} • {{ $report->report_date->translatedFormat('d F Y') }}
                </p>
            </div>
            <a href="{{ route('projects.progress.show', [$project, $report]) }}"
                class="text-gray-600 hover:text-gray-800 dark:text-gray-400">
                ← Kembali ke Detail
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if(session('success'))
                <div class="p-3 bg-green-100 border border-green-300 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-3 bg-red-100 border border-red-300 text-red-800 rounded-lg">{{ session('error') }}</div>
            @endif

            <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg p-5 space-y-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Checklist Review</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div class="p-3 rounded border dark:border-dark-700">
                        <p class="font-medium text-gray-700 dark:text-gray-200">Deskripsi Pekerjaan</p>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $report->description ?: '-' }}</p>
                    </div>
                    <div class="p-3 rounded border dark:border-dark-700">
                        <p class="font-medium text-gray-700 dark:text-gray-200">Progress Hari Ini</p>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ number_format($report->progress_percentage, 1) }}%</p>
                    </div>
                    <div class="p-3 rounded border dark:border-dark-700">
                        <p class="font-medium text-gray-700 dark:text-gray-200">K3</p>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">
                            Insiden: {{ data_get($report->safety_details, 'incidents', 0) }},
                            Near Miss: {{ data_get($report->safety_details, 'near_miss', 0) }}
                        </p>
                    </div>
                    <div class="p-3 rounded border dark:border-dark-700">
                        <p class="font-medium text-gray-700 dark:text-gray-200">Rencana Esok Hari</p>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $report->next_day_plan ?: '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-dark-800 shadow-sm sm:rounded-lg p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Keputusan Review</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <form method="POST" action="{{ route('projects.progress.approve', [$project, $report]) }}" class="space-y-2">
                        @csrf
                        <label class="block text-sm text-gray-600 dark:text-gray-300">Catatan Approve (opsional)</label>
                        <textarea name="notes" rows="4" class="w-full rounded-lg border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-white"></textarea>
                        <button type="submit" class="w-full px-4 py-2 bg-emerald-600 text-white rounded-lg font-semibold">
                            Approve Laporan
                        </button>
                    </form>

                    <form method="POST" action="{{ route('projects.progress.reject', [$project, $report]) }}" class="space-y-2">
                        @csrf
                        <label class="block text-sm text-gray-600 dark:text-gray-300">Catatan Reject</label>
                        <textarea name="notes" rows="4" class="w-full rounded-lg border-gray-300 dark:border-dark-700 dark:bg-dark-900 dark:text-white"></textarea>
                        <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg font-semibold">
                            Reject Laporan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

