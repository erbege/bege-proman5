<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PROMAN5') }} - Owner Portal</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>

<body class="antialiased selection:bg-primary-400 selection:text-slate-900">
    <div class="min-h-screen flex flex-col" x-data="{
        mobileMenuOpen: false,
        darkMode: localStorage.getItem('darkMode') === 'true',
        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('darkMode', this.darkMode);
            document.documentElement.classList.toggle('dark', this.darkMode);
        }
    }">
        <!-- Modern Compact Navigation Bar -->
        <nav class="sticky top-0 z-40 bg-white dark:bg-dark-900 border-b border-slate-200 dark:border-dark-700 shadow-sm">
            <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-14">
                    <!-- Logo -->
                    <a href="{{ route('owner.dashboard') }}" class="flex-shrink-0 flex items-center group gap-2.5">
                        <div
                            class="w-8 h-8 rounded-md bg-gradient-to-br from-yellow-400 to-amber-600 flex items-center justify-center">
                            <span class="text-sm font-black text-white font-outfit">P5</span>
                        </div>
                        <h1
                            class="text-sm font-bold font-outfit tracking-tight text-slate-800 dark:text-white hidden sm:block">
                            PROMAN5 <span
                                class="text-slate-400 dark:text-slate-500 font-normal text-xs ml-1">Owner</span>
                        </h1>
                    </a>

                    <!-- Desktop Nav Links -->
                    <div class="hidden md:flex items-center gap-1">
                        <a href="{{ route('owner.dashboard') }}"
                            class="px-3 py-2 rounded text-xs font-semibold uppercase tracking-tight transition {{ request()->routeIs('owner.dashboard') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('owner.projects.index') }}"
                            class="px-3 py-2 rounded text-xs font-semibold uppercase tracking-tight transition {{ request()->routeIs('owner.projects.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                            Proyek
                        </a>
                    </div>

                    <!-- Right Actions -->
                    <div class="flex items-center gap-2">
                        <!-- Theme Toggle -->
                        <button @click="toggleDarkMode()"
                            class="p-2 text-slate-400 dark:text-slate-500 hover:text-primary-700 dark:hover:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded transition-colors"
                            title="Toggle Theme">
                            <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z">
                                </path>
                            </svg>
                            <svg x-show="darkMode" x-cloak class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                        </button>

                        <!-- Notification Bell -->
                        <div x-data="notificationBell()" class="relative">
                            <button @click="toggle()"
                                class="p-2 text-slate-400 dark:text-slate-500 hover:text-info-600 hover:bg-info-50 dark:hover:bg-dark-800 rounded-lg transition-all relative">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                    </path>
                                </svg>
                                <template x-if="unreadCount > 0">
                                    <span class="absolute top-1.5 right-1.5 flex h-2 w-2">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-sky-500"></span>
                                    </span>
                                </template>
                            </button>

                            <!-- Notification Dropdown -->
                            <div x-show="isOpen" @click.away="isOpen = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                class="absolute right-0 mt-3 w-80 bg-white dark:bg-dark-800 rounded-lg shadow-2xl ring-1 ring-black ring-opacity-5 z-50 overflow-hidden border dark:border-dark-700"
                                x-cloak>
                                <div
                                    class="px-4 py-3 bg-slate-50 dark:bg-dark-900/50 border-b border-slate-100 dark:border-dark-700 flex justify-between items-center">
                                    <h3
                                        class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-widest">
                                        Notifikasi</h3>
                                    <button @click="markAllAsRead()"
                                        class="text-xs font-bold text-info-600 dark:text-info-400 hover:underline uppercase tracking-tight">Mark
                                        all read</button>
                                </div>
                                <div class="max-h-96 overflow-y-auto bg-white dark:bg-dark-800 portal-scrollbar">
                                    <template x-for="notif in notifications" :key="notif.id">
                                        <div @click="handleNotifClick(notif)"
                                            class="p-4 border-b border-slate-50 dark:border-dark-700/50 hover:bg-slate-50 dark:hover:bg-dark-700 transition cursor-pointer relative"
                                            :class="!notif.read_at ? 'bg-sky-50/30 dark:bg-sky-900/10' : ''">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center bg-info-100 dark:bg-info-900/30 text-info-600 dark:text-info-400 border border-info-200 dark:border-info-800">
                                                    <svg x-show="notif.type.includes('Comment')" class="w-5 h-5"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z">
                                                        </path>
                                                    </svg>
                                                    <svg x-show="!notif.type.includes('Comment')" class="w-5 h-5"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                        </path>
                                                    </svg>
                                                </div>
                                                <div class="ml-3 flex-1">
                                                    <p class="text-sm font-bold text-slate-900 dark:text-white leading-tight"
                                                        x-text="notif.data.title || 'Update Proyek'"></p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2"
                                                        x-text="notif.data.message"></p>
                                                    <p class="text-[11px] text-slate-400 dark:text-slate-600 mt-1.5 font-medium"
                                                        x-text="notif.created_at_human"></p>
                                                </div>
                                            <div x-show="!notif.read_at"
                                                    class="w-2 h-2 bg-info-500 rounded-full mt-2 flex-shrink-0 shadow-sm shadow-info-400">
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="notifications.length === 0"
                                        class="p-12 text-center text-slate-400 dark:text-slate-600">
                                        <svg class="w-10 h-10 mx-auto mb-3 opacity-20" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                            </path>
                                        </svg>
                                        <p class="text-xs font-bold uppercase tracking-widest italic">Belum ada
                                            notifikasi</p>
                                    </div>
                                </div>
                                <div
                                    class="px-4 py-3 bg-slate-50 dark:bg-dark-900/50 border-t border-slate-100 dark:border-dark-700 text-center">
                                    <a href="{{ route('owner.notifications.index') }}"
                                        class="text-xs font-bold text-slate-500 dark:text-slate-500 hover:text-info-600 dark:hover:text-info-400 uppercase tracking-widest transition-colors">See
                                        all history</a>
                                </div>
                            </div>
                        </div>

                        <!-- User Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open"
                                class="flex items-center space-x-2 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-dark-800 transition">
                                <div class="text-right hidden sm:block">
                                    <p
                                        class="text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-tight">
                                        {{ Auth::user()->name }}</p>
                                    <p class="text-[8px] text-slate-400 font-bold uppercase">Owner Account</p>
                                </div>
                                <img class="h-8 w-8 rounded-md object-cover ring-2 ring-white dark:ring-dark-700 shadow-sm"
                                    src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
                            </button>

                            <div x-show="open" @click.away="open = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 mt-3 w-52 bg-white dark:bg-dark-800 rounded-lg shadow-2xl ring-1 ring-black ring-opacity-5 z-50 overflow-hidden border dark:border-dark-700"
                                x-cloak>
                                <div
                                    class="px-4 py-3 bg-slate-50 dark:bg-dark-900/50 border-b border-slate-100 dark:border-dark-700">
                                    <p class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                        {{ Auth::user()->name }}</p>
                                    <p
                                        class="text-xs text-slate-400 dark:text-slate-500 font-medium uppercase tracking-widest mt-0.5">
                                        Owner Account</p>
                                </div>
                                <div class="py-1">
                                    <a href="#"
                                        class="block px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-dark-700 transition-colors">Profile
                                        Settings</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="w-full text-left block px-4 py-2.5 text-sm font-bold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">Sign
                                            Out</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Menu Button -->
                        <div class="md:hidden flex items-center">
                            <button @click="mobileMenuOpen = !mobileMenuOpen"
                                class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition">
                                <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                                <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                class="md:hidden border-t border-slate-100 dark:border-dark-700 bg-white dark:bg-dark-800" x-cloak>
                <div class="px-4 pt-2 pb-6 space-y-2">
                    <a href="{{ route('owner.dashboard') }}"
                        class="block px-4 py-3 rounded-lg text-xs font-black uppercase tracking-widest {{ request()->routeIs('owner.dashboard') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-700' }}">Dashboard</a>
                    <a href="{{ route('owner.projects.index') }}"
                        class="block px-4 py-3 rounded-lg text-xs font-black uppercase tracking-widest {{ request()->routeIs('owner.projects.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-700' }}">My
                        Projects</a>
                </div>
            </div>
        </nav>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col">
            <!-- Dynamic Header / Breadcrumb Area -->
            <div class="bg-white dark:bg-dark-800 border-b border-slate-100 dark:border-dark-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="space-y-1">
                            @hasSection('header')
                                @yield('header')
                            @else
                                <h2
                                    class="text-2xl font-bold text-slate-800 dark:text-white font-outfit tracking-tight">
                                    Overview</h2>
                            @endif
                            <p class="text-sm font-medium text-slate-400 dark:text-slate-500">Selamat datang kembali di
                                portal owner Anda.</p>
                        </div>
                        <div class="flex items-center">
                            @yield('header_actions')
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full pb-24 md:pb-8">
                @if (session('error'))
                    <div
                        class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 text-red-700 dark:text-red-400 rounded-lg flex items-center animate-in fade-in slide-in-from-top-2 duration-300">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-[11px] font-black uppercase tracking-tight">{{ session('error') }}</span>
                    </div>
                @endif

                @if (session('success'))
                    <div
                        class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-lg flex items-center animate-in fade-in slide-in-from-top-2 duration-300 shadow-sm shadow-emerald-100/50 dark:shadow-none">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        <span class="text-[11px] font-black uppercase tracking-tight">{{ session('success') }}</span>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Mobile Bottom Navigation (Efficiency / Mobile-first) -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-50 border-t border-slate-200/70 dark:border-dark-700/70 bg-white/90 dark:bg-dark-900/85 backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 py-2">
            <div class="grid grid-cols-3 gap-2">
                <a href="{{ route('owner.dashboard') }}"
                    class="flex flex-col items-center justify-center rounded-xl py-2.5 transition-colors {{ request()->routeIs('owner.dashboard') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="mt-1 text-[10px] font-black uppercase tracking-widest">Home</span>
                </a>
                <a href="{{ route('owner.projects.index') }}"
                    class="flex flex-col items-center justify-center rounded-xl py-2.5 transition-colors {{ request()->routeIs('owner.projects.*') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <span class="mt-1 text-[10px] font-black uppercase tracking-widest">Proyek</span>
                </a>
                <a href="{{ route('owner.notifications.index') }}"
                    class="flex flex-col items-center justify-center rounded-xl py-2.5 transition-colors {{ request()->routeIs('owner.notifications.*') ? 'bg-info-50 dark:bg-info-900/20 text-info-700 dark:text-info-300' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-dark-800' }}">
                    <div class="relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </div>
                    <span class="mt-1 text-[10px] font-black uppercase tracking-widest">Inbox</span>
                </a>
            </div>
        </div>
    </nav>
    @livewireScripts

    <!-- Discussion Side Panel (Global) -->
    <div x-data="discussionPanel()" @open-discussion.window="open($event.detail)" x-show="isOpen" x-cloak
        class="fixed inset-0 z-[100] overflow-hidden" aria-labelledby="slide-over-title" role="dialog"
        aria-modal="true">
        <div class="absolute inset-0 overflow-hidden">
            <!-- Background overlay -->
            <div x-show="isOpen" x-transition:enter="ease-in-out duration-500" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-500"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="close()"
                class="absolute inset-0 bg-slate-900 bg-opacity-70 backdrop-blur-sm transition-opacity"
                aria-hidden="true"></div>

            <div class="pointer-events-none fixed inset-0 flex items-end justify-center sm:items-stretch sm:justify-end z-[110]">
                <div x-show="isOpen" 
                    x-transition:enter="transform transition ease-in-out duration-500"
                    x-transition:enter-start="translate-y-full sm:translate-y-0 sm:translate-x-full" 
                    x-transition:enter-end="translate-y-0 sm:translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-500"
                    x-transition:leave-start="translate-y-0 sm:translate-x-0" 
                    x-transition:leave-end="translate-y-full sm:translate-y-0 sm:translate-x-full"
                    @keydown.escape.window="close()"
                    class="pointer-events-auto w-full sm:max-w-md h-[88vh] sm:h-full bg-white dark:bg-dark-800 shadow-2xl rounded-t-[2.5rem] sm:rounded-t-none sm:rounded-l-[2.5rem] overflow-hidden border-t sm:border-t-0 sm:border-l border-slate-200 dark:border-dark-700 flex flex-col">
                    
                    <!-- Bottom Sheet Handle (Mobile Only) -->
                    <div class="sm:hidden flex justify-center pt-3 pb-1">
                        <div class="w-12 h-1.5 bg-slate-200 dark:bg-dark-700 rounded-full"></div>
                    </div>
                        <!-- Header -->
                        <div class="px-5 py-4 bg-info-600 dark:bg-info-700 text-white relative overflow-hidden">
                            <div
                                class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-bl-full translate-x-8 -translate-y-8">
                            </div>
                            <div class="flex items-center justify-between relative z-10">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-sm font-bold font-outfit leading-tight" id="slide-over-title">Diskusi</h2>
                                        <p class="text-[9px] text-white/80 uppercase tracking-widest font-medium">
                                            <span x-show="reportId">Weekly Report</span>
                                            <span x-show="!reportId">Real-time Feed</span>
                                            <span x-show="reportId" class="opacity-80">•</span>
                                            <span x-show="reportId" x-text="`#${reportId}`" class="opacity-90"></span>
                                        </p>
                                    </div>
                                </div>
                                <button @click="close()" type="button"
                                    class="rounded-lg p-1.5 bg-white/10 text-white hover:bg-white/20 transition">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="relative flex-1 flex flex-col min-h-0 bg-slate-50 dark:bg-dark-900/30">
                            <!-- Context Preview -->
                            <div x-show="currentTarget" x-transition
                                class="p-4 bg-white dark:bg-dark-800 border-b border-slate-100 dark:border-dark-700 shadow-sm animate-in slide-in-from-top duration-300">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="portal-kicker portal-subtle">Referensi</span>
                                    <button @click="clearContext()"
                                        class="text-[10px] font-black text-danger-600 dark:text-danger-400 hover:underline uppercase tracking-widest">Hapus</button>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="previewPhoto(currentTarget)"
                                        class="h-12 w-12 rounded-lg overflow-hidden ring-2 ring-info-100 dark:ring-info-900/30 shadow-sm flex-shrink-0">
                                        <img :src="currentTarget?.url" class="h-full w-full object-cover">
                                    </button>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate" x-text="currentTarget?.name"></p>
                                        <p class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-widest mt-0.5">Klik thumbnail untuk preview</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Comments List -->
                            <div class="flex-1 overflow-y-auto p-5 space-y-4 portal-scrollbar" id="global-comments-container">
                                <!-- Loading -->
                                <div x-show="isLoading" class="py-10">
                                    <div class="space-y-3">
                                        <div class="h-12 rounded-xl bg-slate-200/60 dark:bg-dark-700/60 animate-pulse"></div>
                                        <div class="h-10 rounded-xl bg-slate-200/40 dark:bg-dark-700/40 animate-pulse w-4/5"></div>
                                        <div class="h-12 rounded-xl bg-slate-200/60 dark:bg-dark-700/60 animate-pulse w-3/4 ml-auto"></div>
                                    </div>
                                    <p class="mt-6 text-center text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Memuat diskusi…</p>
                                </div>

                                <!-- Error -->
                                <div x-show="errorMessage && !isLoading" class="py-10 text-center">
                                    <div class="mx-auto w-12 h-12 rounded-2xl bg-danger-50 dark:bg-danger-900/20 text-danger-600 dark:text-danger-400 flex items-center justify-center border border-danger-100 dark:border-danger-900/30">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <p class="mt-3 text-sm font-bold text-slate-800 dark:text-slate-100">Gagal memuat diskusi</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400" x-text="errorMessage"></p>
                                    <button type="button" class="mt-4 portal-btn portal-btn-primary" @click="loadComments()">Coba lagi</button>
                                </div>

                                <template x-for="comment in comments" :key="comment.id">
                                    <div class="flex flex-col animate-in fade-in slide-in-from-bottom-2 duration-300"
                                        :class="comment.user?.id === {{ auth()->id() }} ? 'items-end' : 'items-start'">

                                        <div class="flex items-center mb-1 space-x-2"
                                            :class="comment.user?.id === {{ auth()->id() }} ?
                                                'flex-row-reverse space-x-reverse' : ''">
                                            <span class="text-[10px] font-bold text-slate-900" x-text="comment.user?.name"></span>
                                            <span class="text-[9px] text-slate-400" x-text="comment.created_at_human"></span>

                                            <!-- Actions (own message only) -->
                                            <div x-show="comment.user?.id === {{ auth()->id() }} && !comment.is_deleted"
                                                class="relative"
                                                :class="comment.user?.id === {{ auth()->id() }} ? 'mr-1' : 'ml-1'">
                                                <button type="button" @click="toggleActions(comment.id)"
                                                    class="p-1 rounded-md text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-dark-800 transition"
                                                    title="Aksi pesan">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 6v.01M12 12v.01M12 18v.01"></path>
                                                    </svg>
                                                </button>

                                                <div x-show="openActionFor === comment.id" @click.away="openActionFor = null"
                                                    x-transition:enter="transition ease-out duration-150"
                                                    x-transition:enter-start="opacity-0 scale-95"
                                                    x-transition:enter-end="opacity-100 scale-100"
                                                    class="absolute right-0 mt-1 w-44 bg-white dark:bg-dark-800 border border-slate-200 dark:border-dark-700 rounded-xl shadow-xl overflow-hidden z-20"
                                                    x-cloak>
                                                    <button type="button" @click="confirmDelete(comment)"
                                                        class="w-full px-4 py-3 text-left text-xs font-black uppercase tracking-widest text-danger-600 dark:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-900/15 transition">
                                                        Hapus pesan
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Context in comment -->
                                        <div x-show="comment.metadata?.target === 'photo' || comment.metadata?.photo"
                                            class="mb-2 max-w-[80%]">
                                            <button type="button" @click="previewPhoto(comment.metadata?.photo || comment.metadata)"
                                                class="group relative rounded-xl overflow-hidden border-2 border-white shadow-sm ring-1 ring-slate-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-info-400">
                                                <img :src="comment.metadata?.photo?.url || comment.metadata?.url"
                                                    class="h-24 w-full object-cover">
                                                <div
                                                    class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                                    <span
                                                        class="text-[8px] text-white font-bold uppercase tracking-widest">Lihat
                                                        Foto</span>
                                                </div>
                                            </button>
                                        </div>

                                        <div class="max-w-[85%] px-3 py-2 rounded-2xl text-[11px] shadow-sm transition-colors"
                                            :class="comment.user?.id === {{ auth()->id() }} ?
                                                'bg-info-600 text-white rounded-tr-md shadow-info-100 dark:shadow-none' :
                                                'bg-white dark:bg-dark-700 text-slate-700 dark:text-slate-200 border border-slate-100 dark:border-dark-600 rounded-tl-md'">
                                            <template x-if="comment.is_deleted">
                                                <p class="leading-relaxed italic opacity-80">Pesan dihapus</p>
                                            </template>
                                            <template x-if="!comment.is_deleted">
                                                <p x-text="comment.content" class="leading-relaxed"></p>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="comments.length === 0"
                                    class="flex flex-col items-center justify-center h-full text-slate-400 dark:text-slate-500 opacity-70 py-20 text-center">
                                    <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <p class="text-sm font-bold">Belum ada diskusi</p>
                                    <p class="mt-1 text-xs">Mulai dengan mengirim tanggapan singkat.</p>
                                </div>
                            </div>

                            <!-- Input Area -->
                            <div class="p-4 bg-white dark:bg-dark-800 border-t border-slate-100 dark:border-dark-700">
                                <form @submit.prevent="postComment" class="relative">
                                    <textarea
                                        x-ref="composer"
                                        x-model="newComment"
                                        @keydown.enter.prevent="if(!$event.shiftKey) postComment()"
                                        rows="2"
                                        maxlength="1000"
                                        class="block w-full rounded-xl border-slate-200 dark:border-dark-700 bg-slate-50 dark:bg-dark-900 text-slate-700 dark:text-slate-300 focus:border-info-500 focus:ring-info-500 text-[12px] shadow-inner py-3 pl-4 pr-14 resize-none transition"
                                        placeholder="Ketik tanggapan… (Enter kirim, Shift+Enter baris baru)"></textarea>

                                    <div class="absolute right-2 bottom-2 flex items-center gap-2">
                                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 tabular-nums"
                                            x-text="`${(newComment || '').length}/1000`"></span>
                                        <button type="submit" :disabled="!canSend"
                                            class="p-2 bg-info-600 text-white rounded-lg shadow-lg shadow-info-200 dark:shadow-none hover:bg-info-700 disabled:opacity-50 transition-all duration-300">
                                        <svg x-show="!isPosting" class="h-4 w-4" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                        </svg>
                                        <svg x-show="isPosting" class="h-4 w-4 animate-spin" fill="none"
                                            viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </button>
                                    </div>
                                </form>
                                <div class="mt-2 flex items-center justify-between">
                                    <p class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                                        Esc untuk tutup
                                    </p>
                                    <p class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest" x-show="postError" x-text="postError"></p>
                                </div>
                            </div>

                            <!-- Toast (in-panel, non-blocking) -->
                            <div x-show="toast.show" x-transition.opacity x-cloak
                                class="absolute left-0 right-0 bottom-3 px-4 pointer-events-none">
                                <div class="max-w-sm mx-auto pointer-events-auto">
                                    <div class="rounded-2xl shadow-2xl border px-4 py-3 backdrop-blur bg-white/95 dark:bg-dark-900/90"
                                        :class="toast.type === 'success'
                                            ? 'border-emerald-200/70 dark:border-emerald-900/30'
                                            : 'border-danger-200/70 dark:border-danger-900/30'">
                                        <div class="flex items-start gap-3">
                                            <div class="mt-0.5 w-8 h-8 rounded-xl flex items-center justify-center"
                                                :class="toast.type === 'success'
                                                    ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400'
                                                    : 'bg-danger-50 dark:bg-danger-900/15 text-danger-600 dark:text-danger-400'">
                                                <svg x-show="toast.type === 'success'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <svg x-show="toast.type !== 'success'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white" x-text="toast.title"></p>
                                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400" x-text="toast.message"></p>
                                            </div>
                                            <button type="button" @click="toast.show = false"
                                                class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-dark-800 transition"
                                                title="Tutup">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Confirm Modal (inside panel scope) -->
            <div x-show="deleteModal.show" x-cloak class="absolute inset-0 z-[120] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeDeleteModal()"></div>
                <div x-show="deleteModal.show"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    class="relative w-full max-w-sm rounded-3xl bg-white dark:bg-dark-800 border border-slate-200 dark:border-dark-700 shadow-2xl overflow-hidden">
                    <div class="p-5">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-2xl bg-danger-50 dark:bg-danger-900/15 text-danger-600 dark:text-danger-400 flex items-center justify-center border border-danger-100 dark:border-danger-900/30 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white">Hapus pesan?</p>
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                                    Pesan tidak akan hilang total—akan berubah menjadi <span class="font-semibold">“Pesan dihapus”</span>.
                                </p>
                                <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">
                                    Hanya pesan milik Anda yang bisa dihapus.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="px-5 pb-5 flex gap-2">
                        <button type="button" class="portal-btn flex-1" @click="closeDeleteModal()" :disabled="isDeleting">
                            Batal
                        </button>
                        <button type="button" class="portal-btn portal-btn-primary flex-1 bg-danger-600 hover:bg-danger-700 focus-visible:ring-danger-400"
                            @click="performDelete()" :disabled="isDeleting">
                            <span x-show="!isDeleting">Hapus</span>
                            <span x-show="isDeleting">Menghapus…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        function notificationBell() {
            return {
                isOpen: false,
                notifications: [],
                unreadCount: 0,

                init() {
                    this.loadNotifications();

                    // Listen for real-time notifications
                    if (window.Echo) {
                        window.Echo.private(`App.Models.User.{{ auth()->id() }}`)
                            .notification((notification) => {
                                console.log('New notification received:', notification);
                                this.loadNotifications(); // Refresh on new notification
                            });
                    }
                },

                async loadNotifications() {
                    try {
                        const response = await fetch(window.__OWNER_PORTAL?.notifRecentUrl || '/api/owner/notifications/recent');
                        const result = await response.json();
                        if (result.success) {
                            this.notifications = result.notifications;
                            this.unreadCount = result.unread_count;
                        }
                    } catch (e) {
                        console.error('Failed to load notifications:', e);
                    }
                },

                toggle() {
                    this.isOpen = !this.isOpen;
                    if (this.isOpen) {
                        this.loadNotifications();
                    }
                },

                async markAsRead(id) {
                    try {
                        const base = window.__OWNER_PORTAL?.notifReadUrlBase || '/api/owner/notifications';
                        await fetch(`${base}/${id}/read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            }
                        });
                        this.loadNotifications();
                    } catch (e) {
                        console.error('Failed to mark as read:', e);
                    }
                },

                async markAllAsRead() {
                    try {
                        await fetch(window.__OWNER_PORTAL?.notifReadAllUrl || '/api/owner/notifications/read-all', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            }
                        });
                        this.loadNotifications();
                    } catch (e) {
                        console.error('Failed to mark all as read:', e);
                    }
                },

                handleNotifClick(notif) {
                    this.markAsRead(notif.id);
                    if (notif.data.action_url) {
                        window.location.href = notif.data.action_url;
                    }
                    this.isOpen = false;
                }
            };
        }

        function discussionPanel() {
            return {
                isOpen: false,
                comments: [],
                newComment: '',
                isPosting: false,
                isLoading: false,
                errorMessage: '',
                postError: '',
                currentTarget: null,
                projectId: null,
                reportId: null,
                photoPreview: null,
                isDeleting: false,
                toast: {
                    show: false,
                    type: 'success',
                    title: '',
                    message: '',
                    timeoutId: null,
                },
                deleteModal: {
                    show: false,
                    comment: null,
                },

                init() {
                    // Listen for global events to update real-time
                    if (window.Echo) {
                        // This will be re-bound when reportId changes
                    }
                },

                async open(data) {
                    this.reportId = data.reportId;
                    this.projectId = data.projectId;
                    this.currentTarget = data.context || null;
                    this.comments = [];
                    this.errorMessage = '';
                    this.postError = '';
                    this.isOpen = true;

                    this.$nextTick(() => {
                        if (this.$refs.composer) this.$refs.composer.focus();
                    });

                    await this.loadComments();
                    this.bindEcho();
                    this.scrollToBottom();
                },

                async loadComments() {
                    this.isLoading = true;
                    this.errorMessage = '';
                    try {
                        const response = await fetch(
                            `/api/comments?commentable_type=App\\Models\\WeeklyReport&commentable_id=${this.reportId}`
                            );
                        const result = await response.json();
                        if (result.success) {
                            this.comments = result.data
                        .reverse(); // index returns latest first, we want oldest first for chat
                        } else {
                            this.errorMessage = result.message || 'Server merespons tidak valid.';
                        }
                    } catch (e) {
                        console.error('Failed to load comments:', e);
                        this.errorMessage = 'Koneksi bermasalah. Periksa jaringan lalu coba lagi.';
                    } finally {
                        this.isLoading = false;
                    }
                },

                bindEcho() {
                    if (window.Echo && this.projectId) {
                        // Leave previous channel if any
                        window.Echo.leave(`project.${this.projectId}`);

                        window.Echo.private(`project.${this.projectId}`)
                            .listen('CommentPosted', (e) => {
                                if (e.commentable_type === 'App\\Models\\WeeklyReport' &&
                                    e.commentable_id == this.reportId) {
                                    if (!this.comments.find(c => c.id == e.comment.id)) {
                                        this.comments.push(e.comment);
                                        this.scrollToBottom();
                                    }
                                }
                            })
                            .listen('CommentDeleted', (e) => {
                                if (e.commentable_type === 'App\\Models\\WeeklyReport' &&
                                    e.commentable_id == this.reportId) {
                                    const idx = this.comments.findIndex(c => c.id == e.comment.id);
                                    if (idx !== -1) {
                                        this.comments[idx] = { ...this.comments[idx], ...e.comment };
                                    }
                                }
                            });
                    }
                },

                close() {
                    this.isOpen = false;
                    this.photoPreview = null;
                    this.postError = '';
                    this.deleteModal.show = false;
                    this.deleteModal.comment = null;
                    this.toast.show = false;
                },

                clearContext() {
                    this.currentTarget = null;
                },

                previewPhoto(photo) {
                    if (!photo) return;
                    this.photoPreview = {
                        url: photo.url || photo?.photo?.url,
                        name: photo.name || 'Photo',
                    };
                    // lightweight: open in new tab for now (fast & no extra modal logic)
                    if (this.photoPreview.url) window.open(this.photoPreview.url, '_blank', 'noopener,noreferrer');
                },

                get canSend() {
                    const c = (this.newComment || '').trim();
                    return !!c && c.length <= 1000 && !this.isPosting && !this.isLoading;
                },

                openActionFor: null,

                toggleActions(commentId) {
                    this.openActionFor = this.openActionFor === commentId ? null : commentId;
                },

                confirmDelete(comment) {
                    this.openActionFor = null;
                    if (!comment || comment.is_deleted) return;

                    this.deleteModal.comment = comment;
                    this.deleteModal.show = true;
                },

                closeDeleteModal() {
                    if (this.isDeleting) return;
                    this.deleteModal.show = false;
                    this.deleteModal.comment = null;
                },

                async performDelete() {
                    const comment = this.deleteModal.comment;
                    if (!comment) return;
                    await this.deleteComment(comment);
                    this.closeDeleteModal();
                },

                async deleteComment(comment) {
                    if (this.isDeleting) return;
                    this.isDeleting = true;
                    try {
                        const res = await fetch(`/api/comments/${comment.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        });
                        const result = await res.json();
                        if (res.ok && result.success) {
                            const idx = this.comments.findIndex(c => c.id == comment.id);
                            if (idx !== -1) {
                                this.comments[idx] = { ...this.comments[idx], ...(result.data || {}), is_deleted: true, content: null };
                            }
                            this.showToast('success', 'Pesan dihapus', 'Pesan telah dihapus dan disembunyikan dari isi chat.');
                        } else {
                            this.showToast('error', 'Gagal menghapus', result.message || 'Gagal menghapus pesan.');
                        }
                    } catch (e) {
                        console.error('Failed to delete comment:', e);
                        this.showToast('error', 'Gagal menghapus', 'Gagal menghapus pesan. Coba lagi.');
                    } finally {
                        this.isDeleting = false;
                    }
                },

                showToast(type, title, message) {
                    if (this.toast.timeoutId) window.clearTimeout(this.toast.timeoutId);
                    this.toast.type = type === 'success' ? 'success' : 'error';
                    this.toast.title = title || (this.toast.type === 'success' ? 'Sukses' : 'Error');
                    this.toast.message = message || '';
                    this.toast.show = true;
                    this.toast.timeoutId = window.setTimeout(() => {
                        this.toast.show = false;
                    }, 3000);
                },

                async postComment() {
                    this.postError = '';
                    if (!this.canSend) return;

                    this.isPosting = true;
                    try {
                        const response = await fetch('/api/comments', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                commentable_type: 'App\\Models\\WeeklyReport',
                                commentable_id: this.reportId,
                                content: this.newComment,
                                metadata: this.currentTarget ? {
                                    target: 'photo',
                                    id: this.currentTarget.id,
                                    url: this.currentTarget.url,
                                    name: this.currentTarget.name
                                } : null
                            })
                        });

                        const result = await response.json();
                        if (response.ok && result.success) {
                            if (!this.comments.find(c => c.id == result.data.id)) {
                                this.comments.push(result.data);
                            }
                            this.newComment = '';
                            this.currentTarget = null;
                            this.scrollToBottom();
                        } else {
                            this.postError = result.message || 'Gagal mengirim.';
                        }
                    } catch (e) {
                        console.error('Failed to post comment:', e);
                        this.postError = 'Gagal mengirim. Coba lagi.';
                    } finally {
                        this.isPosting = false;
                    }
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = document.getElementById('global-comments-container');
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    });
                }
            };
        }
    </script>

    <script>
        window.__OWNER_PORTAL = {
            notifRecentUrl: @json(route('api.owner.notifications.recent')),
            notifReadAllUrl: @json(route('api.owner.notifications.read-all')),
            notifReadUrlBase: @json(url('/api/owner/notifications')),
        };
    </script>

    @stack('scripts')
</body>

</html>
