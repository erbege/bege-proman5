@props([
    'items' => []
])

@if(count($items) > 0)
    <nav class="flex mb-2 border-b border-gray-100 dark:border-dark-800 py-1" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            {{-- Home --}}
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gold-600 dark:text-gray-400 dark:hover:text-gold-400">
                    <x-heroicon-s-home class="w-4 h-4 mr-1" />
                    Dashboard
                </a>
            </li>

            @foreach($items as $item)
                <li>
                    <div class="flex items-center">
                        <x-heroicon-s-chevron-right class="w-4 h-4 text-gray-400" />
                        @if(isset($item['url']) && !$loop->last)
                            <a href="{{ $item['url'] }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-gold-600 dark:text-gray-400 dark:hover:text-gold-400 md:ml-2">
                                {{ $item['label'] }}
                            </a>
                        @else
                            <span class="ml-1 text-sm font-medium text-gray-700 dark:text-gray-200 md:ml-2">
                                {{ $item['label'] }}
                            </span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </nav>
@endif


