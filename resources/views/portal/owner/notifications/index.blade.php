@extends('portal.owner.layouts.app')

@section('header')
    <h2 class="text-xl font-bold text-slate-800 dark:text-white font-outfit uppercase tracking-tight">Riwayat Notifikasi</h2>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Semua Notifikasi</h3>
        @if($notifications->where('read_at', null)->count() > 0)
            <form action="{{ route('owner.notifications.mark-all-read') }}" method="POST">
                @csrf
                <button type="submit" class="text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline uppercase tracking-tight">
                    Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </div>

    <div class="space-y-4">
        @forelse($notifications as $notification)
            <div class="portal-card p-5 flex items-start gap-4 {{ !$notification->read_at ? 'border-l-4 border-l-sky-500 bg-sky-50/30 dark:bg-sky-900/10' : '' }}">
                <div class="flex-shrink-0 mt-1">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ str_contains($notification->type, 'Comment') ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400' : 'bg-sky-100 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400' }}">
                        @if(str_contains($notification->type, 'Comment'))
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        @endif
                    </div>
                </div>
                
                <div class="flex-1">
                    <div class="flex justify-between items-start mb-1">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                            {{ $notification->data['title'] ?? 'Update Proyek' }}
                        </h4>
                        <span class="text-xs text-slate-400 font-medium">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-3">
                        {{ $notification->data['message'] ?? '' }}
                    </p>
                    
                    @if(isset($notification->data['action_url']))
                        <a href="{{ $notification->data['action_url'] }}" class="inline-flex items-center text-xs font-bold text-sky-600 dark:text-sky-400 hover:text-sky-700 dark:hover:text-sky-300 transition-colors">
                            Lihat Detail
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    @endif
                </div>

                @if(!$notification->read_at)
                    <div class="w-2 h-2 bg-sky-500 rounded-full mt-2"></div>
                @endif
            </div>
        @empty
            <div class="portal-card p-12 text-center">
                <div class="w-16 h-16 bg-slate-100 dark:bg-dark-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                </div>
                <p class="text-slate-500 dark:text-slate-400 font-medium">Belum ada notifikasi untuk Anda.</p>
            </div>
        @endforelse

        <div class="mt-8">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection
