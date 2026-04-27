<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name]
    ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $project->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->code }} | {{ $project->client_name }}
                </p>
            </div>
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Project Info -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Overview Card -->
                    <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-3">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Informasi Proyek</h3>
                            <a href="{{ route('projects.edit', $project) }}"
                                class="inline-flex items-center px-3 py-1.5 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                                <x-heroicon-o-pencil-square class="w-4 h-4 mr-1" />
                                Edit
                            </a>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Tipe</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ ucfirst($project->type) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    @if($project->status === 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                    @elseif($project->status === 'completed') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-300
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Mulai</p>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $project->start_date->format('d M Y') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Selesai</p>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $project->end_date->format('d M Y') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Durasi</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $project->duration_weeks }}
                                    Minggu</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase font-bold">Lokasi</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $project->location ?? '-' }}</p>
                            </div>
                        </div>

                        @if($project->description)
                            <div class="mt-4 pt-4 border-t dark:border-dark-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Deskripsi</p>
                                <p class="text-gray-900 dark:text-white">{{ $project->description }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- RAB Summary -->
                    <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ringkasan RAB</h3>
                            <a href="{{ route('projects.rab.index', $project) }}"
                                class="text-sm text-blue-600 hover:underline">Lihat Detail</a>
                        </div>

                        @if($project->rabSections->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach($project->rabSections as $section)
                                    <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-dark-700/50 rounded-lg border border-gray-100 dark:border-dark-700">
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $section->code }}.
                                                {{ $section->name }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $section->items->count() }}
                                                item pekerjaan</p>
                                        </div>
                                        <p class="font-semibold text-gray-900 dark:text-white">Rp
                                            {{ number_format($section->total_price, 0, ',', '.') }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <p class="text-gray-500 dark:text-gray-400 mb-4">Belum ada data RAB</p>
                                <a href="{{ route('projects.rab.import', $project) }}"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    Import RAB dari Excel
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-4">
                    <!-- Contract Value -->
                    <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-3">
                        <h3 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-1">Nilai Kontrak</h3>
                        <p class="text-xl font-black text-blue-600 dark:text-blue-400">
                            {{ $project->formatted_contract_value }}
                        </p>
                    </div>

                    <!-- Total Progress Circular Indicator -->
                    @php $totalProgress = $project->total_progress ?? 0; @endphp
                    <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 text-center">Progress Keseluruhan</h3>
                        <div class="flex flex-col items-center justify-center">
                            <!-- Circular Progress SVG -->
                            <div class="relative w-32 h-32">
                                <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 120 120">
                                    <!-- Background circle -->
                                    <circle 
                                        cx="60" cy="60" r="54" 
                                        fill="none" 
                                        stroke="currentColor" 
                                        stroke-width="12"
                                        class="text-gray-200 dark:text-dark-600"
                                    />
                                    <!-- Progress circle -->
                                    <circle 
                                        cx="60" cy="60" r="54" 
                                        fill="none" 
                                        stroke="currentColor" 
                                        stroke-width="12"
                                        stroke-linecap="round"
                                        stroke-dasharray="{{ 2 * 3.14159 * 54 }}"
                                        stroke-dashoffset="{{ 2 * 3.14159 * 54 * (1 - $totalProgress / 100) }}"
                                        class="text-gold-500 transition-all duration-1000 ease-out"
                                    />
                                </svg>
                                <!-- Percentage text -->
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalProgress, 1) }}%</span>
                                </div>
                            </div>
                            <!-- Status Text -->
                            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 text-center">
                                @if($totalProgress >= 100)
                                    <span class="text-green-600 dark:text-green-400 font-medium">✓ Selesai</span>
                                @elseif($totalProgress >= 75)
                                    <span class="text-blue-600 dark:text-blue-400">Hampir selesai</span>
                                @elseif($totalProgress >= 50)
                                    <span class="text-gold-600 dark:text-gold-400">Sedang berjalan</span>
                                @elseif($totalProgress > 0)
                                    <span class="text-orange-600 dark:text-orange-400">Tahap awal</span>
                                @else
                                    <span class="text-gray-500">Belum dimulai</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Financial Health -->
                    <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 {{ $financialMetrics['cost_variance'] >= 0 ? 'border-green-500' : 'border-red-500' }}">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Kesehatan Keuangan</h3>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                    <span>Biaya Riil (AC)</span>
                                    <span>Rp {{ number_format($financialMetrics['actual_cost'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                    <span>Nilai Hasil (EV)</span>
                                    <span>Rp {{ number_format($financialMetrics['earned_value'], 0, ',', '.') }}</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-dark-700 rounded-full h-2 mt-2">
                                    @php 
                                        $costRatio = $financialMetrics['earned_value'] > 0 
                                            ? ($financialMetrics['actual_cost'] / $financialMetrics['earned_value']) * 100 
                                            : 0;
                                    @endphp
                                    <div class="h-full rounded-full {{ $financialMetrics['cost_variance'] >= 0 ? 'bg-green-500' : 'bg-red-500' }}" 
                                        style="width: {{ min(100, $costRatio) }}%"></div>
                                </div>
                            </div>
                            
                            <div class="pt-2 border-t dark:border-dark-700">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Variansi Biaya</span>
                                    <span class="text-sm font-bold {{ $financialMetrics['cost_variance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $financialMetrics['cost_variance'] >= 0 ? '+' : '' }}Rp {{ number_format($financialMetrics['cost_variance'], 0, ',', '.') }}
                                    </span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">
                                    {{ $financialMetrics['cost_variance'] >= 0 ? 'Di bawah budget (Hemat)' : 'Melebihi budget (Boros)' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aksi Cepat</h3>
                        <div class="space-y-2">
                            <a href="{{ route('projects.rab.import', $project) }}"
                                class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                <span class="ml-3 text-sm font-medium text-blue-700 dark:text-blue-300">Import
                                    RAB</span>
                            </a>
                            <a href="{{ route('projects.schedule.scurve', $project) }}"
                                class="flex items-center p-3 bg-purple-50 dark:bg-purple-900/30 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/50 transition">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                </svg>
                                <span class="ml-3 text-sm font-medium text-purple-700 dark:text-purple-300">Lihat Kurva
                                    S</span>
                            </a>
                            <a href="{{ route('projects.schedule.gantt', $project) }}"
                                class="flex items-center p-3 bg-green-50 dark:bg-green-900/30 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/50 transition">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                                </svg>
                                <span class="ml-3 text-sm font-medium text-green-700 dark:text-green-300">Gantt
                                    Chart</span>
                            </a>
                        </div>
                    </div>

                    <!-- Team Members -->
                    <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tim Proyek</h3>
                        @if($project->team->count() > 0)
                            <div class="space-y-3">
                                @foreach($project->team as $member)
                                    <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-dark-700 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 rounded-full bg-gold-500 flex items-center justify-center text-white text-sm font-medium">
                                                {{ strtoupper(substr($member->name, 0, 1)) }}
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $member->name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ \App\Models\ProjectTeam::getRoles()[$member->pivot->role] ?? $member->pivot->role }}
                                                </p>
                                            </div>
                                        </div>
                                        @if($member->pivot->is_active)
                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Belum ada anggota tim</p>
                        @endif
                    </div>

                    <!-- Created By -->
                    <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Dibuat oleh</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $project->creator->name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $project->created_at->format('d M Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


