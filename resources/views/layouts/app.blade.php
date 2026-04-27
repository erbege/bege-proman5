<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Theme initialization (prevent flash of wrong theme) -->
    <script>
        (function () {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.classList.remove('light', 'dark');
            document.documentElement.classList.add(theme);
        })();
    </script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Theme button styles -->
    <style>
        .theme-btn-light,
        .theme-btn-dark {
            border-color: #d1d5db;
            background-color: #f9fafb;
            color: #374151;
        }

        .dark .theme-btn-light,
        .dark .theme-btn-dark {
            border-color: #434343;
            background-color: #383838;
            color: #d1d5db;
        }

        html.light .theme-btn-light {
            border-color: #eab308;
            background-color: #fefce8;
            color: #a16207;
        }

        html.dark .theme-btn-dark {
            border-color: #eab308;
            background-color: #422006;
            color: #fde047;
        }
    </style>

    @stack('styles')
    @livewireStyles
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-dark-950">
        @include('layouts.navigation')

        <!-- Global Search Component -->
        <livewire:global-search />

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white dark:bg-dark-900 shadow dark:shadow-dark-800">
                <div class="max-w-full mx-auto py-3 px-4 sm:px-6 lg:px-8">
                    @isset($breadcrumb)
                        {{ $breadcrumb }}
                    @endisset
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    <!-- Theme Switcher & Search JavaScript -->
    <script>
        function setTheme(theme) {
            document.documentElement.classList.remove('light', 'dark');
            document.documentElement.classList.add(theme);
            localStorage.setItem('theme', theme);
            updateThemeButtons(theme);
        }

        function toggleTheme() {
            const currentTheme = localStorage.getItem('theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            setTheme(newTheme);
        }

        function updateThemeButtons(theme) {
            // Optional: You can add logic here to animate the icon change if needed
        }

        function initTheme() {
            const currentTheme = localStorage.getItem('theme') || 'light';
            setTheme(currentTheme);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function () {
            initTheme();

            // Global Search Shortcut (Ctrl+K)
            document.addEventListener('keydown', function (e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    Livewire.dispatch('open-search-modal');
                }
            });
        });

        // Initialize on Livewire navigation
        document.addEventListener('livewire:navigated', function () {
            initTheme();
        });
    </script>

    @livewireScripts
    @stack('scripts')
</body>

</html>


