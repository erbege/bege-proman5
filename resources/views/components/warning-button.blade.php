<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-warning-500 dark:bg-warning-400 border border-transparent rounded-lg font-semibold text-xs text-white dark:text-gray-900 uppercase tracking-widest hover:bg-warning-600 dark:hover:bg-warning-300 focus:bg-warning-600 dark:focus:bg-warning-300 active:bg-warning-700 dark:active:bg-warning-500 focus:outline-none focus:ring-2 focus:ring-warning-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>


