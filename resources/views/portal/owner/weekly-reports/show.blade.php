@extends('portal.owner.layouts.app')

@section('header')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">
            <a href="{{ route('owner.dashboard') }}" class="hover:text-sky-600 transition-colors">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
            <a href="{{ route('owner.projects.show', $report->project_id) }}" class="hover:text-sky-600 transition-colors truncate max-w-[150px]">{{ $report->project->name }}</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
            <span class="text-slate-500">Week {{ $report->week_number }}</span>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white font-outfit tracking-tight">Weekly Report - Mingguan {{ $report->week_number }}</h2>
    </div>
    <div class="flex items-center space-x-3">
        <a href="{{ route('owner.weekly-reports.pdf', $report) }}" class="inline-flex items-center px-4 py-2 bg-slate-100 dark:bg-dark-800 text-slate-700 dark:text-slate-300 text-xs font-bold uppercase tracking-widest rounded-lg hover:bg-slate-200 dark:hover:bg-dark-700 transition border border-slate-200 dark:border-dark-700 shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            PDF Report
        </a>
    </div>
</div>
@endsection

@section('content')
@php
    $detailData = $report->detail_data ?? [];
    $weatherCounts = [];
    $totalManpower = 0;
    $reportCount = count($detailData);
    
    foreach($detailData as $detail) {
        $w = strtolower($detail['weather'] ?? 'cerah');
        $weatherCounts[$w] = ($weatherCounts[$w] ?? 0) + 1;
        $totalManpower += (int)($detail['workers_count'] ?? 0);
    }
    arsort($weatherCounts);
    $primaryWeather = count($weatherCounts) > 0 ? array_key_first($weatherCounts) : 'cerah';
    $avgManpower = $reportCount > 0 ? round($totalManpower / $reportCount) : 0;
@endphp

<div x-data="{ 
    openWithContext(photo) {
        window.dispatchEvent(new CustomEvent('open-discussion', { 
            detail: { 
                reportId: {{ $report->id }}, 
                projectId: {{ $report->project_id }},
                context: photo
            } 
        }));
    }
}" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 space-y-6">
        <!-- Summary Card -->
        <div class="portal-card overflow-hidden">
            <div class="relative h-40 bg-slate-900">
                @if($report->cover_image_url)
                    <img src="{{ $report->cover_image_url }}" class="w-full h-full object-cover opacity-60">
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex flex-col justify-end p-6 text-white">
                    <h3 class="text-2xl font-bold font-outfit uppercase tracking-tight">{{ $report->cover_title }}</h3>
                    <p class="text-xs font-bold text-sky-400 uppercase tracking-widest mt-1">{{ $report->period_start->format('d M') }} - {{ $report->period_end->format('d M Y') }}</p>
                </div>
            </div>
            <div class="p-6 grid grid-cols-3 gap-4 border-b border-slate-100 dark:border-dark-700">
                <div class="text-center">
                    <p class="text-xs uppercase font-bold text-slate-400 dark:text-slate-500 tracking-widest mb-2">Rencana</p>
                    <p class="text-2xl font-black text-slate-800 dark:text-white leading-none">{{ number_format($report->cumulative_data['totals']['planned_cumulative'] ?? 0, 2) }}%</p>
                </div>
                <div class="text-center border-x border-slate-100 dark:border-dark-700 px-4">
                    <p class="text-xs uppercase font-bold text-slate-400 dark:text-slate-500 tracking-widest mb-2">Realisasi</p>
                    <p class="text-2xl font-black text-sky-600 dark:text-sky-400 leading-none">{{ number_format($report->cumulative_data['totals']['actual_cumulative'] ?? 0, 2) }}%</p>
                </div>
                <div class="text-center">
                    @php $dev = ($report->cumulative_data['totals']['actual_cumulative'] ?? 0) - ($report->cumulative_data['totals']['planned_cumulative'] ?? 0); @endphp
                    <p class="text-xs uppercase font-bold text-slate-400 dark:text-slate-500 tracking-widest mb-2">Deviasi</p>
                    <p class="text-2xl font-black leading-none {{ $dev >= 0 ? 'text-emerald-500 dark:text-emerald-400' : 'text-red-500 dark:text-red-400' }}">
                        {{ $dev >= 0 ? '+' : '' }}{{ number_format($dev, 2) }}%
                    </p>
                </div>
            </div>
            
            <!-- Site Conditions Summary -->
            <div class="px-6 py-5 bg-slate-50/50 dark:bg-dark-900/30 grid grid-cols-2 gap-6">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-xl flex items-center justify-center shadow-sm">
                        @if(str_contains($primaryWeather, 'hujan'))
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 16.9a5 5 0 01-8 4c-2 0-3.5-1.5-3.5-3.5a5 5 0 014.1-4.8c.1-3 2.5-5.5 5.4-5.5a5.5 5.5 0 015.4 4.5A4.5 4.5 0 0119 16.9zM9 13v.01M12 15v.01M15 13v.01"></path></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Kondisi Cuaca</p>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300 capitalize">{{ $primaryWeather ?: 'Cerah' }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4 border-l border-slate-100 dark:border-dark-700 pl-6">
                    <div class="w-10 h-10 bg-sky-100 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 rounded-xl flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Rata-rata Pekerja</p>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $avgManpower }} Orang / Hari</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Progress Table -->
        <div class="portal-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-dark-700 bg-slate-50/50 dark:bg-dark-900/50">
                <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest font-outfit">Capaian Pekerjaan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/30 dark:bg-dark-900/20">
                            <th class="px-6 py-4 text-xs uppercase font-bold text-slate-400 dark:text-slate-500 tracking-widest">Item Pekerjaan</th>
                            <th class="px-4 py-4 text-xs uppercase font-bold text-slate-400 dark:text-slate-500 tracking-widest text-center">Bobot</th>
                            <th class="px-4 py-4 text-xs uppercase font-bold text-sky-600 dark:text-sky-400 tracking-widest text-center">Rencana</th>
                            <th class="px-4 py-4 text-xs uppercase font-bold text-emerald-600 dark:text-emerald-400 tracking-widest text-center">Realisasi</th>
                            <th class="px-4 py-4 text-xs uppercase font-bold text-slate-400 dark:text-slate-500 tracking-widest text-center">Deviasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-dark-700">
                        @php 
                            $sections = $report->cumulative_data['sections'] ?? []; 
                            $rows = [];
                            $flatten = function($items, $level = 0) use (&$flatten, &$rows) {
                                foreach($items as $item) {
                                    $rows[] = [
                                        'type' => 'header',
                                        'label' => ($item['code'] ?? '') . ' ' . ($item['name'] ?? ''),
                                        'level' => $level
                                    ];
                                    if (!empty($item['items'])) {
                                        foreach($item['items'] as $workItem) {
                                            $rows[] = [
                                                'type' => 'item',
                                                'data' => $workItem,
                                                'level' => $level
                                            ];
                                        }
                                    }
                                    if (!empty($item['children'])) {
                                        $flatten($item['children'], $level + 1);
                                    }
                                }
                            };
                            $flatten($sections);
                        @endphp

                        @foreach($rows as $row)
                            @if($row['type'] === 'header')
                                <tr class="bg-slate-50/30 dark:bg-dark-900/10">
                                    <td colspan="5" class="px-6 py-2.5 text-xs font-bold text-slate-900 dark:text-white uppercase tracking-tight" style="padding-left: {{ 1.5 + ($row['level'] * 1.5) }}rem">
                                        {{ $row['label'] }}
                                    </td>
                                </tr>
                            @else
                                @php $item = $row['data']; @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-dark-700/30 transition group">
                                    <td class="px-6 py-4" style="padding-left: {{ 1.5 + ($row['level'] * 1.5) + 1.5 }}rem">
                                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300 leading-tight group-hover:text-sky-600 transition-colors">{{ $item['work_name'] }}</p>
                                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 uppercase font-medium">{{ $item['code'] }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-center text-xs font-bold text-slate-400">{{ number_format($item['weight'], 2) }}%</td>
                                    <td class="px-4 py-4 text-center text-xs font-bold text-sky-600/70">{{ number_format($item['planned']['cumulative'], 2) }}%</td>
                                    <td class="px-4 py-4 text-center text-xs font-bold text-emerald-600">{{ number_format($item['actual']['cumulative'], 2) }}%</td>
                                    <td class="px-4 py-4 text-center text-xs font-bold {{ ($item['actual']['cumulative'] - $item['planned']['cumulative']) >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                                        {{ number_format($item['actual']['cumulative'] - $item['planned']['cumulative'], 2) }}%
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Documentation Photos -->
        <div class="portal-card p-6">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest flex items-center">
                    <svg class="w-5 h-5 mr-3 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 00-2 2z"></path></svg>
                    Dokumentasi Proyek
                </h3>
                <span class="text-xs font-medium text-slate-400 dark:text-slate-500 italic">Klik ikon pesan untuk feedback foto</span>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @php $allPhotos = $report->all_documentation_photos; @endphp
                @forelse($allPhotos as $photo)
                    <div class="group relative aspect-square rounded-lg overflow-hidden bg-slate-100 dark:bg-dark-900 shadow-sm border border-slate-200 dark:border-dark-700">
                        <img src="{{ $photo['url'] }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition duration-300 p-2 flex flex-col justify-between items-end">
                            <button @click="openWithContext({ id: '{{ $photo['id'] }}', url: '{{ $photo['url'] }}', name: '{{ $photo['name'] }}' })" 
                                    class="p-1.5 bg-white/20 backdrop-blur-md rounded-md text-white hover:bg-sky-500 transition-all shadow-xl border border-white/30"
                                    title="Diskusikan foto ini">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            </button>
                            <p class="text-[8px] text-white font-black truncate w-full text-left uppercase tracking-tighter">{{ $photo['name'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-10 text-center border-2 border-dashed border-slate-100 dark:border-dark-700 rounded-lg">
                        <p class="text-slate-400 dark:text-slate-500 text-[10px] italic font-bold">Tidak ada foto dokumentasi minggu ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Activities & Problems -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="portal-card p-6">
                <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-5">Deskripsi Aktivitas</h4>
                <div class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line bg-slate-50 dark:bg-dark-900/50 p-5 rounded-xl border border-slate-100 dark:border-dark-700">
                    {!! $report->activities ?: '<p class="italic opacity-50">Belum ada deskripsi.</p>' !!}
                </div>
            </div>
            <div class="portal-card p-6">
                <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-5">Kendala & Masalah</h4>
                <div class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line bg-red-50/30 dark:bg-red-900/10 p-5 rounded-xl border border-red-100/50 dark:border-red-900/20">
                    {!! $report->problems ?: '<p class="italic opacity-50">Tidak ada kendala dilaporkan.</p>' !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar: Project Info -->
    <div class="space-y-6">
        <!-- Open Discussion Button (Prominent) -->
        <button 
            @click="window.dispatchEvent(new CustomEvent('open-discussion', { 
                detail: { 
                    reportId: {{ $report->id }}, 
                    projectId: {{ $report->project_id }} 
                } 
            }))"
            class="portal-card w-full p-8 group flex flex-col items-center text-center hover:border-sky-500 transition-all shadow-xl shadow-slate-200/40 dark:shadow-none border-2 border-transparent"
        >
            <div class="w-14 h-14 bg-sky-50 dark:bg-sky-900/20 text-sky-600 dark:text-sky-400 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition duration-500 shadow-sm">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            </div>
            <h3 class="text-base font-bold text-slate-800 dark:text-white mb-1 uppercase tracking-tight">Diskusi Laporan</h3>
            <p class="text-xs text-slate-400 dark:text-slate-500 uppercase tracking-widest font-bold">{{ $report->comments->count() }} Comments</p>
            <div class="mt-6 w-full py-3 bg-sky-600 dark:bg-sky-700 text-white text-xs font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-sky-200 dark:shadow-none group-hover:bg-sky-700 transition-colors">Buka Panel Chat</div>
        </button>

        <div class="portal-card p-6">
            <h3 class="text-xs font-black text-slate-800 dark:text-white mb-5 border-b border-slate-100 dark:border-dark-700 pb-2 uppercase tracking-widest">Detail Laporan</h3>
            <div class="space-y-4">
                <div>
                    <label class="text-[8px] uppercase font-black text-slate-400 dark:text-slate-500 tracking-widest block mb-0.5">Dibuat Oleh</label>
                    <p class="text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ $report->creator->name }}</p>
                </div>
                <div>
                    <label class="text-[8px] uppercase font-black text-slate-400 dark:text-slate-500 tracking-widest block mb-0.5">Proyek</label>
                    <p class="text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ $report->project->name }}</p>
                </div>
                <div>
                    <label class="text-[8px] uppercase font-black text-slate-400 dark:text-slate-500 tracking-widest block mb-0.5">Lokasi</label>
                    <p class="text-[11px] font-bold text-slate-700 dark:text-slate-300 truncate">{{ $report->project->location }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
