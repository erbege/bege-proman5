@props(['active'])

@php
    $classes = ($active ?? false)
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-gold-400 dark:border-gold-600 text-start text-base font-medium text-gold-700 dark:text-gold-300 bg-gold-50 dark:bg-gold-900/50 focus:outline-none focus:text-gold-800 dark:focus:text-gold-200 focus:bg-gold-100 dark:focus:bg-gold-900 focus:border-gold-700 dark:focus:border-gold-300 transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-dark-700 hover:border-gray-300 dark:hover:border-dark-600 focus:outline-none focus:text-gray-800 dark:focus:text-gray-200 focus:bg-gray-50 dark:focus:bg-dark-700 focus:border-gray-300 dark:focus:border-dark-600 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>


