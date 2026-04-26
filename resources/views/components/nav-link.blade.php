@props(['active'])

@php
    $classes = ($active ?? false)
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-gold-500 dark:border-gold-400 text-sm font-medium leading-5 text-gray-900 dark:text-gold-400 focus:outline-none focus:border-gold-700 transition duration-150 ease-in-out'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gold-400 hover:border-gray-300 dark:hover:border-gold-600 focus:outline-none focus:text-gray-700 dark:focus:text-gold-400 focus:border-gray-300 dark:focus:border-gold-600 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
