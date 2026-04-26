<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Jadwal']
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Jadwal Proyek - {{ $project->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->code }}</p>
            </div>
            <div class="flex space-x-2">
                @if($canEditSchedule)
                    <a href="{{ route('projects.schedule.auto', $project) }}" id="auto-schedule-link"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                        <x-heroicon-o-sparkles class="w-4 h-4 mr-1" />
                        Auto Schedule
                    </a>
                @endif
                <a href="{{ route('projects.schedule.gantt', $project) }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    Gantt Chart
                </a>
                <a href="{{ route('projects.schedule.scurve', $project) }}"
                    class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700">
                    Kurva S
                </a>
            </div>
        </div>
    </x-slot>

    @include('projects.navigation')

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <div x-data="{ isLoading: false, loadingMessage: '' }">
        {{-- Loading Overlay --}}
        <div x-show="isLoading" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[9999] flex items-center justify-center">
            <div class="bg-white dark:bg-dark-800 rounded-xl p-8 shadow-2xl text-center max-w-sm mx-4">
                <div
                    class="animate-spin rounded-full h-16 w-16 border-4 border-gold-500 border-t-transparent mx-auto mb-4">
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"
                    x-text="loadingMessage || 'Memproses...'"></h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Mohon tunggu sebentar...</p>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Project Timeline -->
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Timeline Proyek</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Mulai</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $project->start_date->format('d M Y') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Selesai</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $project->end_date->format('d M Y') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Durasi</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $project->duration_weeks }} Minggu
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Item dengan Jadwal</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $project->rabItems()->withSchedule()->count() }} /
                                {{ $project->rabItems()->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Mini S-Curve -->
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Kurva S</h3>
                        <form action="{{ route('projects.schedule.regenerate', $project) }}" method="POST"
                            class="inline" @submit="isLoading = true; loadingMessage = 'Regenerating Jadwal'">
                            @csrf
                            <button type="submit" :disabled="isLoading"
                                :class="{ 'opacity-50 cursor-not-allowed': isLoading }"
                                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 inline-flex items-center">
                                <span x-show="!isLoading">Regenerate Jadwal</span>
                                <span x-show="isLoading" class="flex items-center">
                                    <svg class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Memproses...
                                </span>
                            </button>
                        </form>
                    </div>
                    <div id="mini-scurve-chart" style="height: 250px;"></div>
                </div>

                <!-- RAB Items with Schedule -->
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Item Pekerjaan</h3>
                        @if($canEditSchedule)
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Klik tanggal untuk mengedit
                            </span>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-dark-700">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Pekerjaan</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Bobot</th>
                                    @if($canEditSchedule)
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-16"
                                            title="Item dapat dikerjakan paralel dengan item sebelumnya">
                                            <span class="inline-flex items-center">
                                                <x-heroicon-o-arrows-right-left class="w-3 h-3 mr-1" />
                                                Paralel
                                            </span>
                                        </th>
                                    @endif
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Mulai</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Selesai</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Progress</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Status</th>
                                    @if($canEditSchedule)
                                        <th
                                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-20">
                                            Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($rabSections as $section)
                                    @include('projects.schedule.partials.recursive-index-row', ['section' => $section, 'project' => $project, 'canEditSchedule' => $canEditSchedule])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="mt-6">
                    <a href="{{ route('projects.show', $project) }}"
                        class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                        ← Kembali ke Detail Proyek
                    </a>
                </div>
            </div>
        </div>
    </div>{{-- Close x-data wrapper --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var options = {
                    series: [{
                        name: 'Rencana (%)',
                        data: @json($scurveData['planned'])
                    }, {
                        name: 'Realisasi (%)',
                        data: @json($scurveData['actual'])
                    }],
                    chart: {
                        height: 250,
                        type: 'area',
                        toolbar: { show: false },
                        background: 'transparent',
                        sparkline: { enabled: false }
                    },
                    colors: ['#3B82F6', '#10B981'],
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 2 },
                    fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.1 } },
                    xaxis: { categories: @json($scurveData['labels']), labels: { style: { colors: '#9CA3AF' } } },
                    yaxis: { min: 0, max: 100, labels: { style: { colors: '#9CA3AF' }, formatter: (val) => val.toFixed(0) + '%' } },
                    legend: { position: 'top', labels: { colors: '#9CA3AF' } },
                    grid: { borderColor: '#374151' },
                    tooltip: { theme: 'dark', y: { formatter: (val) => val.toFixed(2) + '%' } }
                };
                new ApexCharts(document.querySelector("#mini-scurve-chart"), options).render();

                // Auto Schedule link - show loading overlay before navigation
                const autoScheduleLink = document.getElementById('auto-schedule-link');
                if (autoScheduleLink) {
                    autoScheduleLink.addEventListener('click', function (e) {
                        e.preventDefault();
                        const href = this.getAttribute('href');

                        // Find the Alpine.js component and set loading state
                        const alpineComponent = document.querySelector('[x-data]');
                        if (alpineComponent && alpineComponent._x_dataStack) {
                            Alpine.data = alpineComponent._x_dataStack[0];
                            alpineComponent._x_dataStack[0].isLoading = true;
                            alpineComponent._x_dataStack[0].loadingMessage = 'Memuat Auto Schedule';
                        }

                        // Navigate after a short delay to show the loading overlay
                        setTimeout(() => {
                            window.location.href = href;
                        }, 100);
                    });
                }

                // Inline Schedule Editing
                @if($canEditSchedule)
                    const scheduleInputs = document.querySelectorAll('.schedule-input');

                    scheduleInputs.forEach(input => {
                        input.addEventListener('change', function () {
                            const itemId = this.dataset.itemId;
                            const row = document.querySelector(`tr[data-item-id="${itemId}"]`);
                            const saveBtn = row.querySelector('.save-schedule-btn');

                            // Check if any value changed
                            const startInput = row.querySelector('.planned-start');
                            const endInput = row.querySelector('.planned-end');
                            const startChanged = startInput.value !== startInput.dataset.original;
                            const endChanged = endInput.value !== endInput.dataset.original;

                            if (startChanged || endChanged) {
                                saveBtn.classList.remove('hidden');
                            } else {
                                saveBtn.classList.add('hidden');
                            }
                        });
                    });

                    // Save button click handler
                    document.querySelectorAll('.save-schedule-btn').forEach(btn => {
                        btn.addEventListener('click', function () {
                            const itemId = this.dataset.itemId;
                            const projectId = this.dataset.projectId;
                            const row = document.querySelector(`tr[data-item-id="${itemId}"]`);
                            const startInput = row.querySelector('.planned-start');
                            const endInput = row.querySelector('.planned-end');

                            // Disable button and show loading
                            this.disabled = true;
                            this.innerHTML = '<svg class="animate-spin w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>...';

                            fetch(`/projects/${projectId}/schedule/items/${itemId}`, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    planned_start: startInput.value || null,
                                    planned_end: endInput.value || null,
                                })
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // Update original values
                                        startInput.dataset.original = startInput.value;
                                        endInput.dataset.original = endInput.value;

                                        // Hide save button and show success
                                        this.classList.add('hidden');
                                        this.disabled = false;
                                        this.innerHTML = '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>Save';

                                        // Show success toast
                                        showToast('Jadwal berhasil disimpan', 'success');
                                    } else {
                                        throw new Error(data.error || 'Gagal menyimpan');
                                    }
                                })
                                .catch(error => {
                                    this.disabled = false;
                                    this.innerHTML = '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>Save';
                                    showToast(error.message || 'Terjadi kesalahan', 'error');
                                });
                        });
                    });

                    // Handle parallel toggle
                    document.querySelectorAll('.parallel-toggle').forEach(toggle => {
                        toggle.addEventListener('change', function () {
                            const itemId = this.dataset.itemId;
                            const projectId = this.dataset.projectId;
                            const canParallel = this.checked;

                            // Show loading state
                            this.disabled = true;

                            fetch(`/projects/${projectId}/schedule/items/${itemId}/parallel`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    can_parallel: canParallel
                                })
                            })
                                .then(async response => {
                                    const contentType = response.headers.get("content-type");
                                    if (!response.ok) {
                                        const text = await response.text();
                                        console.error('Server Error:', response.status, text);
                                        throw new Error(`Server returned status ${response.status}: ${text.substring(0, 100)}...`);
                                    }
                                    if (contentType && contentType.includes("application/json")) {
                                        return response.json();
                                    } else {
                                        const text = await response.text();
                                        console.error('Invalid JSON:', text);
                                        throw new Error('Server response was not JSON');
                                    }
                                })
                                .then(data => {
                                    this.disabled = false;
                                    if (data.success) {
                                        showToast('Pengaturan paralel berhasil disimpan', 'success');
                                    } else {
                                        // Revert checkbox
                                        this.checked = !canParallel;
                                        throw new Error(data.error || 'Gagal menyimpan');
                                    }
                                })
                                .catch(error => {
                                    console.error('Fetch Error:', error);
                                    this.disabled = false;
                                    this.checked = !canParallel;
                                    showToast(error.message || 'Terjadi kesalahan', 'error');
                                });
                        });
                    });

                    function showToast(message, type) {
                        const toast = document.createElement('div');
                        toast.className = `fixed bottom-4 right-4 px-4 py-2 rounded-lg text-white text-sm z-50 ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
                        toast.textContent = message;
                        document.body.appendChild(toast);
                        setTimeout(() => toast.remove(), 3000);
                    }
                @endif
                                        });
        </script>
    @endpush
</x-app-layout>