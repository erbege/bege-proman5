@extends('portal.owner.layouts.app')

@section('header', 'Dashboard')

@section('content')
    <!-- Compact Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <!-- Total Projects Card -->
        <div class="portal-card portal-card-hover p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-tight mb-1">Total
                        Proyek</p>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white font-outfit">{{ $stats['total_projects'] }}
                    </h3>
                </div>
                <div
                    class="w-12 h-12 rounded-lg bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-900/10 flex items-center justify-center text-primary-700 dark:text-primary-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Projects Card -->
        <div class="portal-card portal-card-hover p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-tight mb-1">Proyek
                        Aktif</p>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white font-outfit">
                        {{ $stats['active_projects'] }}</h3>
                </div>
                <div
                    class="w-12 h-12 rounded-lg bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900/30 dark:to-teal-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Avg Progress Card -->
        <div class="portal-card portal-card-hover p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-tight mb-1">Rata-rata
                        Progress</p>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white font-outfit">
                        {{ number_format($stats['avg_progress'], 1) }}%</h3>
                </div>
                <div
                    class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Projects List (2 columns on desktop) -->
        <div class="lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest">Proyek Terkini
                </h3>
                <a href="{{ route('owner.projects.index') }}"
                    class="text-xs font-semibold text-primary-700 dark:text-primary-300 hover:underline uppercase tracking-tight">Lihat
                    Semua →</a>
            </div>

            <div class="space-y-3">
                @forelse($projects as $project)
                    <div class="portal-card portal-card-hover p-4 hover:border-primary-200 dark:hover:border-primary-800/50">
                        <div class="flex items-start justify-between gap-4 mb-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span
                                        class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-tight">{{ $project->code }}</span>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold uppercase tracking-tight rounded {{ $project->status === 'active' ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-slate-50 dark:bg-slate-900/30 text-slate-600 dark:text-slate-400' }}">
                                        {{ $project->status }}
                                    </span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $project->name }}
                                </h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center mt-1">
                                    <svg class="w-3.5 h-3.5 mr-1 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    {{ $project->location }}
                                </p>
                            </div>
                            <a href="{{ route('owner.projects.show', $project) }}"
                                class="flex-shrink-0 p-2 text-primary-700 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                    </path>
                                </svg>
                            </a>
                        </div>

                        @php
                            $latestSchedule = $project->schedules->first();
                            $progress = $latestSchedule?->actual_cumulative ?? 0;
                            $planned = $latestSchedule?->planned_cumulative ?? 0;
                            $deviation = $progress - $planned;
                        @endphp

                        <!-- Progress Bar -->
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-semibold text-slate-600 dark:text-slate-400">Progress</span>
                                <span
                                    class="font-black text-slate-900 dark:text-white">{{ number_format($progress, 1) }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 dark:bg-dark-700 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-primary-400 to-primary-600 transition-all duration-500"
                                    style="width: {{ $progress }}%"></div>
                            </div>
                            <div
                                class="flex justify-between text-[10px] font-semibold text-slate-500 dark:text-slate-400 pt-1">
                                <span>Rencana: {{ number_format($planned, 1) }}%</span>
                                <span
                                    class="{{ $deviation >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $deviation >= 0 ? '↑' : '↓' }} {{ abs(number_format($deviation, 1)) }}%
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="portal-card p-12 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                            </path>
                        </svg>
                        <p class="text-sm text-slate-400 font-medium italic">Belum ada proyek yang terdaftar.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Sidebar: Recent Reports -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest">Laporan Terbaru
                </h3>
                <span class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></span>
            </div>

            <div class="portal-card overflow-hidden">
                <div class="divide-y divide-slate-100 dark:divide-dark-700 max-h-96 overflow-y-auto portal-scrollbar">
                    @forelse($recentReports as $report)
                        <div class="p-3 hover:bg-slate-50 dark:hover:bg-dark-700/50 transition border-b border-slate-100 dark:border-dark-700 last:border-0 cursor-pointer group"
                            @click="window.dispatchEvent(new CustomEvent('open-discussion', { detail: { reportId: {{ $report->id }}, projectId: {{ $report->project_id }} } }))">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Minggu
                                        {{ $report->week_number }}</p>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 truncate">
                                        {{ $report->project->name }}</p>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium mt-1">
                                        {{ $report->period_start->format('d M') }} -
                                        {{ $report->period_end->format('d M') }}</p>
                                </div>
                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <a href="{{ route('owner.weekly-reports.show', $report) }}"
                                        class="p-1.5 text-slate-400 dark:text-slate-500 hover:text-primary-700 dark:hover:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                            </path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400">
                            <p class="text-[10px] italic font-medium">Belum ada laporan.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Comments -->
            <div class="portal-card mt-4 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 dark:border-dark-700 bg-slate-50 dark:bg-dark-900/50">
                    <h3 class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest">Komentar
                        Terbaru</h3>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-dark-700 max-h-64 overflow-y-auto portal-scrollbar">
                    @forelse($recentComments as $comment)
                        <div @click="window.dispatchEvent(new CustomEvent('open-discussion', { detail: { reportId: {{ $comment->commentable_id }}, projectId: {{ $comment->commentable->project_id }} } }))"
                            class="p-3 hover:bg-slate-50 dark:hover:bg-dark-700/50 transition cursor-pointer group">
                            <div class="flex items-start gap-2.5">
                                <img src="{{ $comment->user->profile_photo_url }}"
                                    class="h-7 w-7 rounded object-cover flex-shrink-0">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">
                                            {{ $comment->user->name }}</p>
                                        <span
                                            class="text-[9px] text-slate-400 flex-shrink-0">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">
                                        {{ $comment->content }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400">
                            <p class="text-[10px] italic font-medium">Belum ada komentar.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
