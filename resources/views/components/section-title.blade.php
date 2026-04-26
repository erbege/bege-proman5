<div class="md:col-span-1 flex justify-between">
    <div class="px-4 sm:px-0">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">{{ $title }}</h3>

        <p class="mt-2 text-xs font-bold text-gray-500 dark:text-gray-400">
            {{ $description }}
        </p>
    </div>

    <div class="px-4 sm:px-0">
        {{ $aside ?? '' }}
    </div>
</div>