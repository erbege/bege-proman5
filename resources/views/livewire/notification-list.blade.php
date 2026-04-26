<div>
    {{-- Header with filters and actions --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-dark-700">
        <div class="flex items-center space-x-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Semua Notifikasi</h3>
            @if($unreadCount > 0)
                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gold-100 text-gold-800 dark:bg-gold-900/30 dark:text-gold-400">
                    {{ $unreadCount }} belum dibaca
                </span>
            @endif
        </div>
        
        <div class="flex items-center space-x-2">
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" 
                    class="px-3 py-1.5 text-sm font-medium text-gold-600 hover:text-gold-700 dark:text-gold-400 hover:bg-gold-50 dark:hover:bg-gold-900/20 rounded-lg transition-colors">
                    Tandai semua dibaca
                </button>
            @endif
            
            @if($notifications->count() > 0)
                <button wire:click="deleteAll" 
                    wire:confirm="Apakah Anda yakin ingin menghapus semua notifikasi?"
                    class="px-3 py-1.5 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                    Hapus Semua
                </button>
            @endif
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="flex border-b border-gray-100 dark:border-dark-700">
        <button wire:click="setFilter('all')"
            class="px-6 py-3 text-sm font-medium transition-colors {{ $filter === 'all' ? 'text-gold-600 dark:text-gold-400 border-b-2 border-gold-500' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            Semua
        </button>
        <button wire:click="setFilter('unread')"
            class="px-6 py-3 text-sm font-medium transition-colors {{ $filter === 'unread' ? 'text-gold-600 dark:text-gold-400 border-b-2 border-gold-500' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            Belum Dibaca
            @if($unreadCount > 0)
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                    {{ $unreadCount }}
                </span>
            @endif
        </button>
    </div>

    {{-- Notification List --}}
    <div class="divide-y divide-gray-100 dark:divide-dark-700">
        @forelse($notifications as $notification)
            <div class="relative group {{ is_null($notification->read_at) ? 'bg-gold-50/50 dark:bg-gold-900/5' : '' }}">
                <a href="{{ $notification->data['url'] ?? '#' }}" 
                   wire:click="markAsRead('{{ $notification->id }}')"
                   class="block px-6 py-4 hover:bg-gray-50 dark:hover:bg-dark-700/50 transition-colors">
                    <div class="flex items-start gap-4">
                        {{-- Icon --}}
                        <div class="flex-shrink-0">
                            @php
                                $type = $notification->data['type'] ?? 'general';
                                $iconClass = match($type) {
                                    'purchase_request_status' => 'text-blue-500 bg-blue-100 dark:bg-blue-900/30',
                                    'purchase_order_created' => 'text-green-500 bg-green-100 dark:bg-green-900/30',
                                    'progress_report_created' => 'text-purple-500 bg-purple-100 dark:bg-purple-900/30',
                                    'material_request_status' => 'text-orange-500 bg-orange-100 dark:bg-orange-900/30',
                                    'project_assignment' => 'text-indigo-500 bg-indigo-100 dark:bg-indigo-900/30',
                                    'schedule_changed' => 'text-yellow-500 bg-yellow-100 dark:bg-yellow-900/30',
                                    default => 'text-gray-500 bg-gray-100 dark:bg-gray-900/30'
                                };
                            @endphp
                            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $iconClass }}">
                                @switch($type)
                                    @case('purchase_request_status')
                                        <x-heroicon-s-document-check class="w-5 h-5" />
                                        @break
                                    @case('purchase_order_created')
                                        <x-heroicon-s-shopping-cart class="w-5 h-5" />
                                        @break
                                    @case('progress_report_created')
                                        <x-heroicon-s-chart-bar class="w-5 h-5" />
                                        @break
                                    @case('material_request_status')
                                        <x-heroicon-s-cube class="w-5 h-5" />
                                        @break
                                    @case('project_assignment')
                                        <x-heroicon-s-user-plus class="w-5 h-5" />
                                        @break
                                    @case('schedule_changed')
                                        <x-heroicon-s-calendar-days class="w-5 h-5" />
                                        @break
                                    @default
                                        <x-heroicon-s-bell class="w-5 h-5" />
                                @endswitch
                            </div>
                        </div>
                        
                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $notification->data['title'] ?? 'Notifikasi' }}
                                </p>
                                @if(is_null($notification->read_at))
                                    <span class="w-2 h-2 bg-gold-500 rounded-full"></span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ $notification->data['message'] ?? '' }}
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                {{ $notification->created_at->diffForHumans() }} · {{ $notification->created_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                    </div>
                </a>
                
                {{-- Delete button --}}
                <button wire:click="deleteNotification('{{ $notification->id }}')"
                    class="absolute top-4 right-4 p-2 rounded-lg opacity-0 group-hover:opacity-100 hover:bg-gray-200 dark:hover:bg-dark-600 transition-opacity">
                    <x-heroicon-m-trash class="w-4 h-4 text-gray-400 hover:text-red-500" />
                </button>
            </div>
        @empty
            <div class="px-6 py-16 text-center">
                <x-heroicon-o-bell-slash class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600" />
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">Tidak ada notifikasi</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    @if($filter === 'unread')
                        Semua notifikasi sudah dibaca.
                    @else
                        Anda belum menerima notifikasi.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 dark:border-dark-700">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
