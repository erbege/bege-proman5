@extends('portal.owner.layouts.app')

@section('header', 'Daftar Proyek')

@section('content')
    <div class="space-y-4">
        <!-- Filter Bar -->
        <form method="GET" action="{{ route('owner.projects.index') }}" class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex-1">
                <input
                    type="search"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    placeholder="Cari proyek (nama, kode, lokasi)..."
                    class="portal-input"
                    autocomplete="off"
                >
            </div>
            <div class="flex items-center gap-2">
                <select name="status" class="portal-select">
                    <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Semua Status</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                    <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Selesai</option>
                    <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Tunda</option>
                </select>
                <button type="submit" class="portal-btn portal-btn-primary whitespace-nowrap">Terapkan</button>
                <a href="{{ route('owner.projects.index') }}" class="portal-btn whitespace-nowrap">Reset</a>
            </div>
        </form>

        <!-- Projects Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @forelse($projects as $project)
                <div class="portal-card portal-card-hover p-5 group">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <span
                                    class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-tight">{{ $project->code }}</span>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-tight rounded {{ $project->status === 'active' ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-slate-50 dark:bg-slate-900/30 text-slate-600 dark:text-slate-400' }}">
                                    {{ $project->status }}
                                </span>
                            </div>
                            <h3
                                class="text-base font-bold text-slate-900 dark:text-white group-hover:text-primary-700 dark:group-hover:text-primary-300 transition-colors truncate">
                                {{ $project->name }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center mt-1.5">
                                <svg class="w-3.5 h-3.5 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor"
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
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>
                    </div>

                    <!-- Progress Section -->
                    @php $progress = $project->schedules->first()?->actual_cumulative ?? 0; @endphp
                    <div class="space-y-2">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-semibold text-slate-600 dark:text-slate-400">Progress Realisasi</span>
                            <span
                                class="font-black text-slate-900 dark:text-white">{{ number_format($progress, 1) }}%</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 dark:bg-dark-700 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-primary-400 to-primary-600 transition-all duration-500"
                                style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    <!-- Project Meta -->
                    <div
                        class="mt-4 flex items-center justify-between text-[10px] font-medium text-slate-500 dark:text-slate-400 pt-4 border-t border-slate-100 dark:border-dark-700">
                        <span>Dibuat: {{ $project->created_at->format('d M Y') }}</span>
                        <span class="text-slate-400 dark:text-slate-600">{{ $project->tasks_count ?? 0 }} tugas</span>
                    </div>
                </div>
            @empty
                <div class="lg:col-span-2 portal-card p-12 text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                        </path>
                    </svg>
                    <p class="text-slate-400 font-medium">Belum ada proyek.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($projects->hasPages())
            <div class="flex justify-center mt-8">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
@endsection
