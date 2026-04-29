<x-app-layout>
    <div class="py-12 bg-gradient-to-br from-indigo-50 to-white dark:from-dark-950 dark:to-dark-900 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Premium Header -->
            <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-1">
                    <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                        Selamat Datang, <span class="text-indigo-600 dark:text-indigo-400">{{ auth()->user()->name }}</span>
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 font-medium italic">Dashboard Eksklusif Pemilik Proyek</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="px-4 py-2 bg-white/50 dark:bg-dark-800/50 backdrop-blur-md rounded-2xl border border-white/20 dark:border-gray-700/30 shadow-sm flex items-center">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 mr-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Status Terakhir</p>
                            <p class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ now()->translatedFormat('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <!-- Total Projects Card -->
                <div class="relative group overflow-hidden bg-white dark:bg-dark-800 p-6 rounded-3xl shadow-xl shadow-indigo-100/20 dark:shadow-none border border-gray-100 dark:border-gray-700/50 transition-all hover:scale-[1.02]">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-indigo-50 dark:bg-indigo-900/20 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="relative z-10 flex items-center">
                        <div class="p-4 bg-indigo-100 dark:bg-indigo-900/50 rounded-2xl text-indigo-600 dark:text-indigo-400 mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">Total Proyek</p>
                            <h3 class="text-3xl font-black text-gray-900 dark:text-white">{{ $stats['total_projects'] }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Active Projects Card -->
                <div class="relative group overflow-hidden bg-white dark:bg-dark-800 p-6 rounded-3xl shadow-xl shadow-green-100/20 dark:shadow-none border border-gray-100 dark:border-gray-700/50 transition-all hover:scale-[1.02]">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-green-50 dark:bg-green-900/20 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="relative z-10 flex items-center">
                        <div class="p-4 bg-green-100 dark:bg-green-900/50 rounded-2xl text-green-600 dark:text-green-400 mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">Proyek Aktif</p>
                            <h3 class="text-3xl font-black text-gray-900 dark:text-white">{{ $stats['active_projects'] }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Avg Progress Card -->
                <div class="relative group overflow-hidden bg-white dark:bg-dark-800 p-6 rounded-3xl shadow-xl shadow-amber-100/20 dark:shadow-none border border-gray-100 dark:border-gray-700/50 transition-all hover:scale-[1.02]">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-50 dark:bg-amber-900/20 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="relative z-10 flex items-center">
                        <div class="p-4 bg-amber-100 dark:bg-amber-900/50 rounded-2xl text-amber-600 dark:text-amber-400 mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">Rata-rata Progres</p>
                            <h3 class="text-3xl font-black text-gray-900 dark:text-white">{{ number_format($stats['avg_progress'], 1) }}%</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Project Progress Tracking -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="flex items-center justify-between mb-2">
                        <h2 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">Ringkasan Progres Proyek</h2>
                        <a href="{{ route('projects.index') }}" class="text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:underline">Lihat Semua Proyek</a>
                    </div>
                    
                    @forelse($projects as $project)
                        @php $latestSchedule = $project->schedules->first(); @endphp
                        <div class="bg-white dark:bg-dark-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700/50">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 transition-colors">{{ $project->name }}</h4>
                                    <div class="flex items-center mt-1 space-x-3">
                                        <div class="flex items-center text-[10px] text-gray-400 font-bold uppercase tracking-wider">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            {{ $project->location ?? 'N/A' }}
                                        </div>
                                        <div class="flex items-center text-[10px] text-amber-500 font-bold uppercase tracking-wider">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9h-1m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 15.364l-.707.707M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Cerah / 30°C
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-full text-[10px] font-black uppercase tracking-widest mb-2">
                                        {{ $project->status }}
                                    </span>
                                    <!-- Mini Sparkline Placeholder (CSS only) -->
                                    <div class="flex items-end space-x-0.5 h-6">
                                        @foreach([20, 35, 30, 45, 55, 60, 50, 70] as $h)
                                            <div class="w-1 bg-indigo-200 dark:bg-indigo-700 rounded-full" style="height: {{ $h }}%"></div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Milestone Timeline -->
                            <div class="mb-6 grid grid-cols-3 gap-2 px-2">
                                <div class="relative">
                                    <div class="h-1 bg-indigo-600 rounded-full"></div>
                                    <p class="mt-1 text-[8px] font-bold text-gray-400 uppercase">Mulai</p>
                                    <p class="text-[9px] font-bold text-gray-700 dark:text-gray-300">{{ $project->start_date->format('d M y') }}</p>
                                </div>
                                <div class="relative">
                                    <div class="h-1 bg-gray-200 dark:bg-dark-600 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-400" style="width: {{ $latestSchedule?->actual_cumulative ?? 0 }}%"></div>
                                    </div>
                                    <p class="mt-1 text-[8px] font-bold text-gray-400 uppercase text-center">Progres</p>
                                    <p class="text-[9px] font-bold text-indigo-600 dark:text-indigo-400 text-center">{{ number_format($latestSchedule?->actual_cumulative ?? 0, 0) }}%</p>
                                </div>
                                <div class="relative">
                                    <div class="h-1 bg-gray-200 dark:bg-dark-600 rounded-full"></div>
                                    <p class="mt-1 text-[8px] font-bold text-gray-400 uppercase text-right">Selesai</p>
                                    <p class="text-[9px] font-bold text-gray-700 dark:text-gray-300 text-right">{{ $project->end_date->format('d M y') }}</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between items-end mb-2">
                                        <span class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase">Progres Realisasi</span>
                                        <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400 tracking-tighter">{{ number_format($latestSchedule?->actual_cumulative ?? 0, 1) }}%</span>
                                    </div>
                                    <div class="w-full h-4 bg-gray-100 dark:bg-dark-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-indigo-500 to-indigo-700 dark:from-indigo-600 dark:to-indigo-400 rounded-full transition-all duration-1000" 
                                             style="width: {{ $latestSchedule?->actual_cumulative ?? 0 }}%"></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 pt-2">
                                    <div class="p-3 bg-gray-50 dark:bg-dark-700/50 rounded-2xl border dark:border-gray-600/30">
                                        <p class="text-[10px] text-gray-400 uppercase font-black">Target Rencana</p>
                                        <p class="text-sm font-bold text-gray-700 dark:text-gray-200">{{ number_format($latestSchedule?->planned_cumulative ?? 0, 1) }}%</p>
                                    </div>
                                    <div class="p-3 bg-gray-50 dark:bg-dark-700/50 rounded-2xl border dark:border-gray-600/30">
                                        <p class="text-[10px] text-gray-400 uppercase font-black">Deviasi</p>
                                        <p class="text-sm font-bold {{ ($latestSchedule?->deviation ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ number_format($latestSchedule?->deviation ?? 0, 1) }}%
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center text-xs font-black text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    DETAIL PROYEK
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white dark:bg-dark-800 rounded-3xl p-12 text-center">
                            <p class="text-gray-400 italic">Belum ada proyek yang ditugaskan kepada Anda.</p>
                        </div>
                    @endforelse

                    <!-- Latest Documentation Gallery -->
                    <div class="bg-white dark:bg-dark-800 rounded-3xl p-6 shadow-xl border border-gray-100 dark:border-gray-700/50">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tighter">Galeri Dokumentasi Terbaru</h2>
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($recentReports->take(4) as $report)
                                @php $docs = $report->documentation_files; @endphp
                                @if(count($docs) > 0)
                                    <div class="relative group aspect-square rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-700 shadow-sm transition-transform hover:scale-105">
                                        <img src="{{ $docs[0]['url'] }}" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <p class="text-[8px] text-white font-bold uppercase tracking-widest">W-{{ $report->week_number }} · {{ $report->project->name }}</p>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Side Panel: Recent Updates & Discussion -->
                <div class="space-y-8">
                    <!-- Recent Reports Section -->
                    <div class="bg-white/80 dark:bg-dark-800/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-white/20 dark:border-gray-700/30">
                        <h2 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tighter mb-6 flex items-center">
                            <x-heroicon-o-document-chart-bar class="w-5 h-5 mr-2 text-indigo-600" />
                            Laporan Terbaru
                        </h2>
                        <div class="space-y-4">
                            @forelse($recentReports as $report)
                                <a href="{{ route('projects.weekly-reports.show', [$report->project, $report]) }}" 
                                   class="block group p-4 bg-gray-50/50 dark:bg-dark-700/30 hover:bg-white dark:hover:bg-dark-700 rounded-2xl border border-transparent hover:border-indigo-100 dark:hover:border-indigo-900 transition-all">
                                    <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mb-1">{{ $report->project->name }}</p>
                                    <h5 class="text-sm font-bold text-gray-800 dark:text-gray-200">Week {{ $report->week_number }}</h5>
                                    <div class="mt-3 flex justify-between items-center">
                                        <span class="text-[10px] text-gray-400">{{ $report->period_label }}</span>
                                        <span class="text-[10px] font-black text-green-600 bg-green-50 dark:bg-green-900/30 px-2 py-0.5 rounded uppercase">Published</span>
                                    </div>
                                </a>
                            @empty
                                <p class="text-xs text-gray-400 italic text-center py-4">Belum ada laporan dipublish.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Recent Discussions Section -->
                    <div class="bg-indigo-600 rounded-3xl p-6 shadow-xl shadow-indigo-200 dark:shadow-none text-white overflow-hidden relative">
                        <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
                        <h2 class="text-lg font-black uppercase tracking-tighter mb-6 flex items-center relative z-10">
                            <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 mr-2" />
                            Diskusi Terbaru
                        </h2>
                        <div class="space-y-4 relative z-10">
                            @forelse($recentComments as $comment)
                                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10">
                                    <div class="flex items-center mb-2">
                                        <div class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center text-[10px] font-bold mr-2">
                                            {{ substr($comment->user->name, 0, 1) }}
                                        </div>
                                        <span class="text-xs font-bold truncate">{{ $comment->user->name }}</span>
                                        <span class="ml-auto text-[9px] text-indigo-200">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-xs text-indigo-50 line-clamp-2 leading-relaxed italic">"{{ $comment->content }}"</p>
                                    <div class="mt-2 pt-2 border-t border-white/5">
                                        <a href="{{ route('projects.weekly-reports.show', [$comment->commentable->project, $comment->commentable]) }}" 
                                           class="text-[9px] font-bold uppercase tracking-widest hover:underline">
                                           Balas di Laporan W-{{ $comment->commentable->week_number }}
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-indigo-100 italic text-center py-4 opacity-70">Belum ada diskusi terbaru.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        .tracking-tighter { letter-spacing: -0.05em; }
        .tracking-widest { letter-spacing: 0.1em; }
    </style>
</x-app-layout>
