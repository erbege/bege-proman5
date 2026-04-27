<div wire:poll.30s.visible="loadNotifications" x-data="{ open: @entangle('isOpen') }" @click.outside="open = false" class="relative">
    {{-- Notification Bell Button --}}
    <button @click="open = !open; $wire.loadNotifications()"
        class="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-dark-800 text-gray-500 dark:text-gray-400 transition-colors focus:outline-none focus:ring-2 focus:ring-gold-500">
        <x-heroicon-o-bell class="w-5 h-5" />
        
        {{-- Unread Badge --}}
        @if($unreadCount > 0)
            <span class="absolute top-1 right-1 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-xs font-bold text-white bg-red-500 rounded-full ring-2 ring-white dark:ring-dark-900">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown Panel --}}
    <div x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 sm:w-96 bg-white dark:bg-dark-800 rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50 overflow-hidden"
        style="display: none;">
        
        {{-- Header --}}
        <div class="flex items-center justify-between px-3 py-1.5 border-b border-gray-100 dark:border-dark-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Notifikasi</h3>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" 
                    class="text-xs text-gold-600 hover:text-gold-700 dark:text-gold-400 dark:hover:text-gold-300 font-medium">
                    Tandai semua dibaca
                </button>
            @endif
        </div>

        {{-- Notification List --}}
        <div class="max-h-96 overflow-y-auto divide-y divide-gray-100 dark:divide-dark-700">
            @forelse($this->notifications as $notification)
                <div class="relative group {{ is_null($notification->read_at) ? 'bg-gold-50 dark:bg-gold-900/10' : '' }}">
                    <a href="{{ $notification->data['url'] ?? '#' }}" 
                       wire:click="markAsRead('{{ $notification->id }}')"
                       class="block px-3 py-1.5 hover:bg-gray-50 dark:hover:bg-dark-700 transition-colors">
                        <div class="flex items-start gap-3">
                            {{-- Icon based on type --}}
                            <div class="flex-shrink-0 mt-0.5">
                                @php
                                    $type = $notification->data['type'] ?? 'general';
                                    $iconClass = match($type) {
                                        'purchase_request_status' => 'text-blue-500',
                                        'purchase_request_created' => 'text-blue-600',
                                        'purchase_order_created' => 'text-green-500',
                                        'progress_report_created' => 'text-purple-500',
                                        'material_request_status' => 'text-orange-500',
                                        'material_request_created' => 'text-orange-600',
                                        'project_assignment' => 'text-indigo-500',
                                        'schedule_changed' => 'text-yellow-500',
                                        default => 'text-gray-500'
                                    };
                                @endphp
                                <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-dark-700 flex items-center justify-center">
                                    @switch($type)
                                        @case('purchase_request_status')
                                            <x-heroicon-s-document-check class="w-4 h-4 {{ $iconClass }}" />
                                            @break
                                        @case('purchase_request_created')
                                            <x-heroicon-s-document-plus class="w-4 h-4 {{ $iconClass }}" />
                                            @break
                                        @case('purchase_order_created')
                                            <x-heroicon-s-shopping-cart class="w-4 h-4 {{ $iconClass }}" />
                                            @break
                                        @case('progress_report_created')
                                            <x-heroicon-s-chart-bar class="w-4 h-4 {{ $iconClass }}" />
                                            @break
                                        @case('material_request_status')
                                            <x-heroicon-s-cube class="w-4 h-4 {{ $iconClass }}" />
                                            @break
                                        @case('material_request_created')
                                            <x-heroicon-s-cube-transparent class="w-4 h-4 {{ $iconClass }}" />
                                            @break
                                        @case('project_assignment')
                                            <x-heroicon-s-user-plus class="w-4 h-4 {{ $iconClass }}" />
                                            @break
                                        @case('schedule_changed')
                                            <x-heroicon-s-calendar-days class="w-4 h-4 {{ $iconClass }}" />
                                            @break
                                        @default
                                            <x-heroicon-s-bell class="w-4 h-4 {{ $iconClass }}" />
                                    @endswitch
                                </div>
                            </div>
                            
                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                    {{ $notification->data['title'] ?? 'Notifikasi' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                                    {{ $notification->data['message'] ?? '' }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                            
                            {{-- Unread indicator --}}
                            @if(is_null($notification->read_at))
                                <div class="flex-shrink-0">
                                    <span class="block w-2 h-2 bg-gold-500 rounded-full"></span>
                                </div>
                            @endif
                        </div>
                    </a>
                    
                    {{-- Delete button --}}
                    <button wire:click="deleteNotification('{{ $notification->id }}')"
                        class="absolute top-2 right-2 p-1 rounded opacity-0 group-hover:opacity-100 hover:bg-gray-200 dark:hover:bg-dark-600 transition-opacity">
                        <x-heroicon-m-x-mark class="w-4 h-4 text-gray-400 hover:text-red-500" />
                    </button>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <x-heroicon-o-bell-slash class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" />
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada notifikasi</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        @if($this->notifications->count() > 0)
            <div class="border-t border-gray-100 dark:border-dark-700 px-4 py-2">
                <a href="{{ route('notifications.index') }}" 
                   class="block text-center text-sm text-gold-600 hover:text-gold-700 dark:text-gold-400 dark:hover:text-gold-300 font-medium">
                    Lihat semua notifikasi
                </a>
            </div>
        @endif
    </div>
</div>


