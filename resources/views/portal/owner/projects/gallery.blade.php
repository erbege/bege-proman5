@extends('portal.owner.layouts.app')

@section('header')
<div class="flex items-center space-x-2 text-sm text-slate-500 mb-1">
    <a href="{{ route('owner.dashboard') }}" class="hover:text-sky-600">Dashboard</a>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
    <a href="{{ route('owner.projects.show', $project) }}" class="hover:text-sky-600">{{ $project->name }}</a>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
    <span>Project Gallery</span>
</div>
<h2 class="text-2xl font-bold text-slate-800">Project Gallery</h2>
@endsection

@section('content')
<div x-data="{ 
    openWithContext(photo, reportId) {
        window.dispatchEvent(new CustomEvent('open-discussion', { 
            detail: { 
                reportId: reportId, 
                projectId: {{ $project->id }},
                context: photo
            } 
        }));
    }
}" class="portal-card p-8">
    <div class="flex justify-between items-center mb-8">
        <h3 class="text-sm font-bold text-slate-500 uppercase tracking-widest font-outfit">All Documentation Photos</h3>
        <p class="text-xs text-slate-400 font-bold uppercase tracking-tight">{{ count($photos) }} Total Photos</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($photos as $photo)
            <div class="group relative aspect-square rounded-2xl overflow-hidden bg-slate-100 dark:bg-dark-900 shadow-md border border-slate-200 dark:border-dark-700">
                <img src="{{ $photo['url'] }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition duration-300 p-4 flex flex-col justify-between items-end">
                    <button @click="openWithContext({ id: '{{ $photo['id'] }}', url: '{{ $photo['url'] }}', name: '{{ $photo['name'] }}' }, {{ $photo['report_id'] }})" 
                            class="p-2 bg-white/20 backdrop-blur-md rounded-xl text-white hover:bg-sky-600 transition-all shadow-xl border border-white/30"
                            title="Diskusikan foto ini">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </button>
                    
                    <div class="w-full">
                        <span class="inline-block px-2.5 py-1 bg-sky-600 text-white text-[10px] font-bold rounded-lg uppercase tracking-widest mb-2 w-fit">
                            Week {{ $photo['week_number'] }}
                        </span>
                        <p class="text-xs text-white font-bold truncate">{{ $photo['name'] }}</p>
                        <p class="text-[10px] text-slate-300 font-medium mt-0.5">{{ $photo['period'] }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center border-2 border-dashed border-slate-200 dark:border-dark-700 rounded-3xl">
                <div class="w-20 h-20 bg-slate-100 dark:bg-dark-900 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 00-2 2z"></path></svg>
                </div>
                <p class="text-slate-500 dark:text-slate-400 italic">No documentation photos found for this project yet.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
