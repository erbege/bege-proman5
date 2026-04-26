<nav x-data="{ open: false }" class="bg-white dark:bg-dark-900 border-b border-gray-100 dark:border-dark-800">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        <x-heroicon-o-home class="w-5 h-5 mr-2" />
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">
                        <x-heroicon-o-folder class="w-5 h-5 mr-2" />
                        {{ __('Proyek') }}
                    </x-nav-link>
                    <x-nav-link :href="route('inventory.index')" :active="request()->routeIs('inventory.*')">
                        <x-heroicon-o-building-office-2 class="w-5 h-5 mr-2" />
                        {{ __('Gudang') }}
                    </x-nav-link>

                    <!-- Master Data Dropdown -->
                    <div class="hidden sm:flex sm:items-center" x-data="{ open: false }">
                        <div class="relative">
                            <button @click="open = !open" @click.away="open = false"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none {{ request()->routeIs('materials.*') || request()->routeIs('suppliers.*') || request()->routeIs('clients.*') ? 'border-gold-400 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700' }}">
                                <x-heroicon-o-circle-stack class="w-5 h-5 mr-2" />
                                {{ __('Master Data') }}
                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute z-50 mt-2 w-48 rounded-md shadow-lg origin-top-left left-0"
                                style="display: none;">
                                <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white dark:bg-dark-800">
                                    <a href="{{ route('materials.index') }}"
                                        class="block px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-dark-700 {{ request()->routeIs('materials.*') ? 'bg-gray-100 dark:bg-dark-700' : '' }}">
                                        <x-heroicon-o-cube class="w-4 h-4 inline mr-2" />
                                        {{ __('Material') }}
                                    </a>
                                    <a href="{{ route('suppliers.index') }}"
                                        class="block px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-dark-700 {{ request()->routeIs('suppliers.*') ? 'bg-gray-100 dark:bg-dark-700' : '' }}">
                                        <x-heroicon-o-truck class="w-4 h-4 inline mr-2" />
                                        {{ __('Supplier') }}
                                    </a>
                                    <a href="{{ route('clients.index') }}"
                                        class="block px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-dark-700 {{ request()->routeIs('clients.*') ? 'bg-gray-100 dark:bg-dark-700' : '' }}">
                                        <x-heroicon-o-user-group class="w-4 h-4 inline mr-2" />
                                        {{ __('Klien') }}
                                    </a>
                                    <div class="border-t border-gray-100 dark:border-dark-700 my-1"></div>
                                    <a href="{{ route('ahsp.index') }}"
                                        class="block px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-dark-700 {{ request()->routeIs('ahsp.*') ? 'bg-gray-100 dark:bg-dark-700' : '' }}">
                                        <x-heroicon-o-calculator class="w-4 h-4 inline mr-2" />
                                        {{ __('AHSP') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side Icons & Settings -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-4">

                <!-- Global Search -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                    </div>
                    <input type="text"
                        class="bg-gray-100 dark:bg-dark-800 border border-gray-200 dark:border-dark-700 text-gray-900 text-sm rounded-lg focus:ring-gold-500 focus:border-gold-500 block w-64 pl-10 p-2 dark:text-gray-200 dark:placeholder-gray-400"
                        placeholder="Cari..." readonly
                        onclick="window.dispatchEvent(new CustomEvent('open-search-modal'))">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <span
                            class="text-xs text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-dark-600 rounded px-1.5 py-0.5">Ctrl+K</span>
                    </div>
                </div>

                <!-- Notification Bell (Livewire) -->
                @livewire('notification-dropdown')

                <!-- User Avatar Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center transition duration-150 ease-in-out">
                            <div
                                class="h-8 w-8 rounded-full bg-gold-100 dark:bg-indigo-900/50 flex items-center justify-center border-2 border-indigo-200 dark:border-gold-500 text-gold-700 dark:text-gold-400 font-bold text-xs uppercase">
                                {{ substr(Auth::user()->name, 0, 2) }}
                            </div>
                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-gray-100 dark:border-dark-700">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ Auth::user()->name }}</p>
                            @php
                                $userRole = Auth::user()->roles->first()?->name ?? 'user';

                                // Generate consistent color based on role name hash
                                $colorPalette = [
                                    ['bg-purple-100 text-purple-800', 'dark:bg-purple-900/30 dark:text-purple-400'],
                                    ['bg-blue-100 text-blue-800', 'dark:bg-blue-900/30 dark:text-blue-400'],
                                    ['bg-green-100 text-green-800', 'dark:bg-green-900/30 dark:text-green-400'],
                                    ['bg-orange-100 text-orange-800', 'dark:bg-orange-900/30 dark:text-orange-400'],
                                    ['bg-pink-100 text-pink-800', 'dark:bg-pink-900/30 dark:text-pink-400'],
                                    ['bg-cyan-100 text-cyan-800', 'dark:bg-cyan-900/30 dark:text-cyan-400'],
                                    ['bg-indigo-100 text-indigo-800', 'dark:bg-indigo-900/30 dark:text-indigo-400'],
                                    ['bg-teal-100 text-teal-800', 'dark:bg-teal-900/30 dark:text-teal-400'],
                                    ['bg-violet-100 text-violet-800', 'dark:bg-violet-900/30 dark:text-violet-400'],
                                    ['bg-fuchsia-100 text-fuchsia-800', 'dark:bg-fuchsia-900/30 dark:text-fuchsia-400'],
                                    ['bg-emerald-100 text-emerald-800', 'dark:bg-emerald-900/30 dark:text-emerald-400'],
                                    ['bg-red-100 text-red-800', 'dark:bg-red-900/30 dark:text-red-400'],
                                    ['bg-amber-100 text-amber-800', 'dark:bg-amber-900/30 dark:text-amber-400'],
                                    ['bg-lime-100 text-lime-800', 'dark:bg-lime-900/30 dark:text-lime-400'],
                                    ['bg-rose-100 text-rose-800', 'dark:bg-rose-900/30 dark:text-rose-400'],
                                    ['bg-sky-100 text-sky-800', 'dark:bg-sky-900/30 dark:text-sky-400'],
                                ];
                                $colorIndex = abs(crc32($userRole)) % count($colorPalette);
                                $colorClass = $colorPalette[$colorIndex][0] . ' ' . $colorPalette[$colorIndex][1];
                                $roleLabel = ucwords(str_replace(['-', '_'], ' ', $userRole));
                            @endphp
                            <span
                                class="inline-flex items-center px-2 py-0.5 mt-1 rounded-full text-xs font-medium {{ $colorClass }}">
                                {{ $roleLabel }}
                            </span>
                        </div>

                        <x-dropdown-link :href="route('profile.show')">
                            <x-heroicon-o-user class="w-4 h-4 mr-2 inline" />
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        @role('Superadmin')
                        <x-dropdown-link :href="route('settings.system')">
                            <x-heroicon-o-cog-6-tooth class="w-4 h-4 mr-2 inline" />
                            {{ __('Pengaturan Sistem') }}
                        </x-dropdown-link>
                        @endrole

                        @if(auth()->user()->hasAnyRole(['super-admin', 'Superadmin', 'administrator']))
                            <x-dropdown-link :href="route('settings.users')">
                                <x-heroicon-o-users class="w-4 h-4 mr-2 inline" />
                                {{ __('Kelola User') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('settings.roles')">
                                <x-heroicon-o-shield-check class="w-4 h-4 mr-2 inline" />
                                {{ __('Kelola Role') }}
                            </x-dropdown-link>
                        @endif

                        <!-- Theme Toggle -->
                        <div class="px-4 py-2 border-t border-gray-100 dark:border-dark-700">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Tema</span>
                                <button onclick="toggleTheme()"
                                    class="flex items-center gap-1 px-2 py-1 rounded text-sm hover:bg-gray-100 dark:hover:bg-dark-600">
                                    <x-heroicon-o-sun class="w-4 h-4 hidden dark:block text-yellow-400" />
                                    <x-heroicon-o-moon class="w-4 h-4 block dark:hidden text-gray-500" />
                                    <span class="hidden dark:inline text-gray-300">Light</span>
                                    <span class="inline dark:hidden text-gray-500">Dark</span>
                                </button>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4 mr-2 inline" />
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">
                {{ __('Proyek') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('inventory.index')" :active="request()->routeIs('inventory.*')">
                {{ __('Gudang') }}
            </x-responsive-nav-link>

            <!-- Master Data Section -->
            <div class="px-4 py-2">
                <span class="block text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Master
                    Data</span>
            </div>
            <x-responsive-nav-link :href="route('materials.index')" :active="request()->routeIs('materials.*')"
                class="pl-8">
                <x-heroicon-o-cube class="w-4 h-4 inline mr-2" />
                {{ __('Material') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')"
                class="pl-8">
                <x-heroicon-o-truck class="w-4 h-4 inline mr-2" />
                {{ __('Supplier') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')"
                class="pl-8">
                <x-heroicon-o-user-group class="w-4 h-4 inline mr-2" />
                {{ __('Klien') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('ahsp.index')" :active="request()->routeIs('ahsp.*')" class="pl-8">
                <x-heroicon-o-calculator class="w-4 h-4 inline mr-2" />
                {{ __('AHSP') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-dark-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                @php
                    $userRole = Auth::user()->roles->first()?->name ?? 'user';
                    // Generate consistent color based on role name hash
                    $colorPalette = [
                        ['bg-purple-100 text-purple-800', 'dark:bg-purple-900/30 dark:text-purple-400'],
                        ['bg-blue-100 text-blue-800', 'dark:bg-blue-900/30 dark:text-blue-400'],
                        ['bg-green-100 text-green-800', 'dark:bg-green-900/30 dark:text-green-400'],
                        ['bg-orange-100 text-orange-800', 'dark:bg-orange-900/30 dark:text-orange-400'],
                        ['bg-pink-100 text-pink-800', 'dark:bg-pink-900/30 dark:text-pink-400'],
                        ['bg-cyan-100 text-cyan-800', 'dark:bg-cyan-900/30 dark:text-cyan-400'],
                        ['bg-indigo-100 text-indigo-800', 'dark:bg-indigo-900/30 dark:text-indigo-400'],
                        ['bg-teal-100 text-teal-800', 'dark:bg-teal-900/30 dark:text-teal-400'],
                        ['bg-violet-100 text-violet-800', 'dark:bg-violet-900/30 dark:text-violet-400'],
                        ['bg-fuchsia-100 text-fuchsia-800', 'dark:bg-fuchsia-900/30 dark:text-fuchsia-400'],
                        ['bg-emerald-100 text-emerald-800', 'dark:bg-emerald-900/30 dark:text-emerald-400'],
                        ['bg-red-100 text-red-800', 'dark:bg-red-900/30 dark:text-red-400'],
                        ['bg-amber-100 text-amber-800', 'dark:bg-amber-900/30 dark:text-amber-400'],
                        ['bg-lime-100 text-lime-800', 'dark:bg-lime-900/30 dark:text-lime-400'],
                        ['bg-rose-100 text-rose-800', 'dark:bg-rose-900/30 dark:text-rose-400'],
                        ['bg-sky-100 text-sky-800', 'dark:bg-sky-900/30 dark:text-sky-400'],
                    ];
                    $colorIndex = abs(crc32($userRole)) % count($colorPalette);
                    $colorClass = $colorPalette[$colorIndex][0] . ' ' . $colorPalette[$colorIndex][1];
                    $roleLabel = ucwords(str_replace(['-', '_'], ' ', $userRole));
                @endphp
                <span
                    class="inline-flex items-center px-2 py-0.5 mt-1 rounded-full text-xs font-medium {{ $colorClass }}">
                    {{ $roleLabel }}
                </span>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.show')">
                    <x-heroicon-o-user class="w-4 h-4 mr-2 inline" />
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Mobile Theme Switcher -->
                <div class="px-4 py-3 border-t border-gray-200 dark:border-dark-600">
                    <span
                        class="block text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Tema</span>
                    <div class="flex items-center space-x-2">
                        <button onclick="setTheme('light')" id="mobile-theme-light-btn"
                            class="flex-1 flex items-center justify-center px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 border theme-btn-light">
                            <x-heroicon-o-sun class="w-4 h-4 mr-1" />
                            Light
                        </button>
                        <button onclick="setTheme('dark')" id="mobile-theme-dark-btn"
                            class="flex-1 flex items-center justify-center px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 border theme-btn-dark">
                            <x-heroicon-o-moon class="w-4 h-4 mr-1" />
                            Dark
                        </button>
                    </div>
                </div>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4 mr-2 inline" />
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>