@props(['submit'])

<div {{ $attributes->merge(['class' => 'md:grid md:grid-cols-3 md:gap-8']) }}>
    <x-section-title>
        <x-slot name="title">{{ $title }}</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>
    </x-section-title>

    <div class="mt-5 md:mt-0 md:col-span-2">
        <form wire:submit="{{ $submit }}">
            <div
                class="px-4 py-8 bg-white dark:bg-dark-900 sm:p-8 shadow-sm border border-gray-100 dark:border-dark-800 {{ isset($actions) ? 'sm:rounded-t-[2rem]' : 'sm:rounded-[2rem]' }}">
                <div class="grid grid-cols-6 gap-6">
                    {{ $form }}
                </div>
            </div>

            @if (isset($actions))
                <div
                    class="flex items-center justify-end px-4 py-4 bg-gray-50/80 dark:bg-dark-900/50 text-end sm:px-8 border-t border-gray-100 dark:border-dark-800 shadow-sm sm:rounded-b-[2rem]">
                    {{ $actions }}
                </div>
            @endif
        </form>
    </div>
</div>