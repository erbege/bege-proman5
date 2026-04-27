<div {{ $attributes->merge(['class' => 'md:grid md:grid-cols-3 md:gap-4']) }}>
    <x-section-title>
        <x-slot name="title">{{ $title }}</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>
    </x-section-title>

    <div class="mt-5 md:mt-0 md:col-span-2">
        <div class="px-4 py-8 sm:p-4 bg-white dark:bg-dark-900 shadow-sm border border-gray-100 dark:border-dark-800 sm:rounded-[2rem]">
            {{ $content }}
        </div>
    </div>
</div>


