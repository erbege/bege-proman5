<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Jadwal', 'url' => route('projects.schedule.index', $project)],
        ['label' => 'Gantt Chart']
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Gantt Chart - {{ $project->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->code }}</p>
            </div>
            <a href="{{ route('projects.schedule.index', $project) }}"
                class="text-gray-600 hover:text-gray-800 dark:text-gray-400">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="overflow-x-auto">
                    @php
                        $startDate = $project->start_date;
                        $endDate = $project->end_date;
                        $totalWeeks = (int) ceil($startDate->diffInDays($endDate) / 7);
                        $weeks = [];
                        for ($i = 0; $i < $totalWeeks; $i++) {
                            $weeks[] = $startDate->copy()->addWeeks($i);
                        }
                    @endphp

                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-dark-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase sticky left-0 bg-gray-50 dark:bg-dark-700 z-10"
                                    style="min-width: 250px;">
                                    Uraian Pekerjaan
                                </th>
                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    style="min-width: 60px;">
                                    Bobot
                                </th>
                                @foreach($weeks as $week)
                                    <th class="px-1 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300"
                                        style="min-width: 40px;">
                                        M{{ $loop->iteration }}
                                        <div class="text-[10px] font-normal">{{ $week->format('d/m') }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($rabSections as $section)
                                @include('projects.schedule.partials.recursive-gantt-row', [
                                    'section' => $section,
                                    'weeks' => $weeks,
                                    'project' => $project
                                ])
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Legend -->
                <div class="mt-6 flex items-center space-x-6">
                    <div class="flex items-center">
                        <div class="w-4 h-3 bg-blue-200 dark:bg-blue-900 rounded-sm mr-2"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Rencana</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-4 h-3 bg-green-500 dark:bg-green-600 rounded-sm mr-2"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Realisasi</span>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-6">
                <a href="{{ route('projects.schedule.index', $project) }}"
                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                    ← Kembali ke Jadwal
                </a>
            </div>
        </div>
    </div>

    <!-- Item Detail Modal -->
    <div id="itemDetailModal" class="hidden fixed inset-0 z-50 overflow-y-auto scrollbar-overlay"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="hideItemDetail()"
                aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="relative inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button type="button" onclick="hideItemDetail()" class="text-gray-400 hover:text-gray-500">
                        <x-heroicon-o-x-circle class="w-6 h-6" />
                    </button>
                </div>
                <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-gold-100 sm:mx-0 sm:h-10 sm:w-10">
                            <x-heroicon-o-clipboard-document-list class="h-6 w-6 text-gold-600" />
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white"
                                id="modal-item-name"></h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400" id="modal-item-section"></p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-6">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600 dark:text-gray-400">Progress</span>
                            <span class="font-semibold text-gray-900 dark:text-white" id="modal-progress-text"></span>
                        </div>
                        <div class="w-full h-4 bg-gray-200 dark:bg-dark-700 rounded-full overflow-hidden">
                            <div id="modal-progress-bar"
                                class="h-full bg-green-500 transition-all duration-300 rounded-full"></div>
                        </div>
                    </div>

                    <!-- Detail Grid -->
                    <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Bobot</p>
                            <p class="font-medium text-gray-900 dark:text-white" id="modal-weight"></p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Volume</p>
                            <p class="font-medium text-gray-900 dark:text-white" id="modal-volume"></p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Mulai Rencana</p>
                            <p class="font-medium text-gray-900 dark:text-white" id="modal-start"></p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Selesai Rencana</p>
                            <p class="font-medium text-gray-900 dark:text-white" id="modal-end"></p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Harga Satuan</p>
                            <p class="font-medium text-gray-900 dark:text-white">Rp <span id="modal-unit-price"></span>
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Total Harga</p>
                            <p class="font-medium text-gold-600 dark:text-gold-400">Rp <span
                                    id="modal-total-price"></span></p>
                        </div>
                    </div>

                    <!-- Status Indicator -->
                    <div class="mt-6 p-3 rounded-lg" id="modal-status-box">
                        <div class="flex items-center">
                            <span id="modal-status-icon"></span>
                            <span class="ml-2 font-medium" id="modal-status-text"></span>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-dark-700 px-3 py-1.5 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="hideItemDetail()"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold-500 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600 dark:hover:bg-gray-700">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showItemDetail(item) {
            document.getElementById('modal-item-name').textContent = item.name;
            document.getElementById('modal-item-section').textContent = item.section;
            document.getElementById('modal-progress-text').textContent = item.progress.toFixed(1) + '%';
            document.getElementById('modal-progress-bar').style.width = item.progress + '%';
            document.getElementById('modal-weight').textContent = item.weight + '%';
            document.getElementById('modal-volume').textContent = item.volume + ' ' + item.unit;
            document.getElementById('modal-start').textContent = item.plannedStart;
            document.getElementById('modal-end').textContent = item.plannedEnd;
            document.getElementById('modal-unit-price').textContent = item.unitPrice;
            document.getElementById('modal-total-price').textContent = item.totalPrice;

            // Status indicator
            const statusBox = document.getElementById('modal-status-box');
            const statusIcon = document.getElementById('modal-status-icon');
            const statusText = document.getElementById('modal-status-text');

            if (item.progress >= 100) {
                statusBox.className = 'mt-6 p-3 rounded-lg bg-green-50 dark:bg-green-900/20';
                statusIcon.innerHTML = '<svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>';
                statusText.textContent = 'Selesai';
                statusText.className = 'ml-2 font-medium text-green-700 dark:text-green-400';
            } else if (item.progress > 0) {
                statusBox.className = 'mt-6 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20';
                statusIcon.innerHTML = '<svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>';
                statusText.textContent = 'Dalam Progres - Sisa ' + item.remaining.toFixed(1) + '%';
                statusText.className = 'ml-2 font-medium text-blue-700 dark:text-blue-400';
            } else {
                statusBox.className = 'mt-6 p-3 rounded-lg bg-gray-50 dark:bg-dark-700';
                statusIcon.innerHTML = '<svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>';
                statusText.textContent = 'Belum Dimulai';
                statusText.className = 'ml-2 font-medium text-gray-600 dark:text-gray-400';
            }

            document.getElementById('itemDetailModal').classList.remove('hidden');
        }

        function hideItemDetail() {
            document.getElementById('itemDetailModal').classList.add('hidden');
        }
    </script>
</x-app-layout>


