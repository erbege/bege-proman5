@props(['on'])

<div x-data="{ shown: false, timeout: null }"
    x-init="
        Livewire.on('{{ $on }}', () => {
            clearTimeout(timeout);
            shown = true;
            timeout = setTimeout(() => { shown = false }, 2000);
        })
    "
    x-show="shown"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    style="display: none;"
    {{ $attributes->merge(['class' => 'text-sm text-gray-600 dark:text-gray-400']) }}>
    {{ $slot->isEmpty() ? 'Saved.' : $slot }}
</div>


