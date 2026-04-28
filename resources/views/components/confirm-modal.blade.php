@props([
    'id' => 'confirmModal',
    'title' => 'Konfirmasi',
    'message' => 'Apakah Anda yakin?',
    'confirmText' => 'Ya, Lanjutkan',
    'cancelText' => 'Batal',
    'confirmColor' => 'red', // red, green, blue, yellow
    'icon' => 'exclamation-triangle', // heroicon name
])

@php
    $colorClasses = [
        'red' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
        'green' => 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
        'blue' => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
        'yellow' => 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
    ];
    $iconBgClasses = [
        'red' => 'bg-red-100 dark:bg-red-900/30',
        'green' => 'bg-green-100 dark:bg-green-900/30',
        'blue' => 'bg-blue-100 dark:bg-blue-900/30',
        'yellow' => 'bg-yellow-100 dark:bg-yellow-900/30',
    ];
    $iconColorClasses = [
        'red' => 'text-red-600 dark:text-red-400',
        'green' => 'text-green-600 dark:text-green-400',
        'blue' => 'text-blue-600 dark:text-blue-400',
        'yellow' => 'text-yellow-600 dark:text-yellow-400',
    ];
@endphp

<div id="{{ $id }}" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-80 transition-opacity" 
            onclick="document.getElementById('{{ $id }}').classList.add('hidden')" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 dark:border-dark-700">
            <div class="bg-white dark:bg-dark-800 px-6 pt-6 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full {{ $iconBgClasses[$confirmColor] ?? 'bg-red-100' }} sm:mx-0 sm:h-12 sm:w-12">
                        @if($icon === 'exclamation-triangle')
                            <x-heroicon-o-exclamation-triangle class="h-6 w-6 {{ $iconColorClasses[$confirmColor] ?? 'text-red-600' }}" />
                        @elseif($icon === 'trash')
                            <x-heroicon-o-trash class="h-6 w-6 {{ $iconColorClasses[$confirmColor] ?? 'text-red-600' }}" />
                        @elseif($icon === 'check')
                            <x-heroicon-o-check class="h-6 w-6 {{ $iconColorClasses[$confirmColor] ?? 'text-green-600' }}" />
                        @elseif($icon === 'x-mark')
                            <x-heroicon-o-x-mark class="h-6 w-6 {{ $iconColorClasses[$confirmColor] ?? 'text-red-600' }}" />
                        @elseif($icon === 'arrow-path')
                            <x-heroicon-o-arrow-path class="h-6 w-6 {{ $iconColorClasses[$confirmColor] ?? 'text-blue-600' }}" />
                        @else
                            <x-heroicon-o-question-mark-circle class="h-6 w-6 {{ $iconColorClasses[$confirmColor] ?? 'text-blue-600' }}" />
                        @endif
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-5 sm:text-left flex-1">
                        <h3 class="text-xl leading-6 font-bold text-gray-900 dark:text-white" id="modal-title-{{ $id }}">
                            {{ $title }}
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $message }}
                            </p>
                        </div>

                        {{-- Body slot for forms/inputs --}}
                        @if(isset($body))
                            <div class="mt-4">
                                {{ $body }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 dark:bg-dark-700/50 px-6 py-4 sm:flex sm:flex-row-reverse gap-3 border-t border-gray-100 dark:border-dark-700">
                @if(isset($footer))
                    {{ $footer }}
                @else
                    {{-- Default slot usage (legacy) --}}
                    @if($slot->isNotEmpty())
                        {{ $slot }}
                    @else
                        <button type="button" 
                            onclick="document.getElementById('{{ $id }}').classList.add('hidden')"
                            class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 {{ $colorClasses[$confirmColor] ?? 'bg-red-600' }} text-base font-bold text-white transition-all transform hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ $confirmText }}
                        </button>
                    @endif
                    
                    <button type="button" onclick="document.getElementById('{{ $id }}').classList.add('hidden')"
                        class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 dark:border-dark-600 shadow-sm px-5 py-2.5 bg-white dark:bg-dark-800 text-base font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all sm:mt-0 sm:w-auto sm:text-sm">
                        {{ $cancelText }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>


