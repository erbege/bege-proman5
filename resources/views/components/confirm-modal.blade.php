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
        'red' => 'bg-red-100',
        'green' => 'bg-green-100',
        'blue' => 'bg-blue-100',
        'yellow' => 'bg-yellow-100',
    ];
    $iconColorClasses = [
        'red' => 'text-red-600',
        'green' => 'text-green-600',
        'blue' => 'text-blue-600',
        'yellow' => 'text-yellow-600',
    ];
@endphp

<div id="{{ $id }}" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('{{ $id }}').classList.add('hidden')" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white dark:bg-dark-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-dark-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full {{ $iconBgClasses[$confirmColor] ?? 'bg-red-100' }} sm:mx-0 sm:h-10 sm:w-10">
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
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title-{{ $id }}">
                            {{ $title }}
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $message }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-dark-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                {{ $slot }}
                <button type="button" onclick="document.getElementById('{{ $id }}').classList.add('hidden')"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-dark-800 dark:text-gray-300 dark:border-dark-600 dark:hover:bg-gray-700">
                    {{ $cancelText }}
                </button>
            </div>
        </div>
    </div>
</div>
