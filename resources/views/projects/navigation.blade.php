<div class="bg-white dark:bg-dark-800 border-b border-gray-100 dark:border-dark-700 shadow-sm sticky top-0 z-30"
    style="overflow: visible;">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8" style="overflow: visible;">
        <div class="flex space-x-2 py-1.5 no-scrollbar flex-nowrap"
            style="overflow-x: auto; overflow-y: visible; padding-bottom: 5px;">
            <!-- Overview -->
            <a href="{{ route('projects.show', $project) }}"
                class="px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors duration-150 {{ request()->routeIs('projects.show') ? 'bg-gold-50 text-gold-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700' }}">
                <div class="flex items-center">
                    <x-heroicon-o-home class="w-4 h-4 mr-2" />
                    Overview
                </div>
            </a>

            <!-- Team -->
            <a href="{{ route('projects.team.index', $project) }}"
                class="px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors duration-150 {{ request()->routeIs('projects.team.*') ? 'bg-gold-50 text-gold-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700' }}">
                <div class="flex items-center">
                    <x-heroicon-o-user-group class="w-4 h-4 mr-2" />
                    Tim
                </div>
            </a>

            <!-- Files -->
            @can('files.view')
            <a href="{{ route('projects.files.index', $project) }}"
                class="px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors duration-150 {{ request()->routeIs('projects.files.*') ? 'bg-gold-50 text-gold-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700' }}">
                <div class="flex items-center">
                    <x-heroicon-o-folder-open class="w-4 h-4 mr-2" />
                    Files
                </div>
            </a>
            @endcan

            <!-- RAB -->
            @can('rab.view')
            <a href="{{ route('projects.rab.index', $project) }}"
                class="px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors duration-150 {{ request()->routeIs('projects.rab.*') ? 'bg-gold-50 text-gold-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700' }}">
                <div class="flex items-center">
                    <x-heroicon-o-document-currency-dollar class="w-4 h-4 mr-2" />
                    RAB
                </div>
            </a>
            @endcan

            <!-- Schedule -->
            @can('schedule.view')
            <a href="{{ route('projects.schedule.index', $project) }}"
                class="px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors duration-150 {{ request()->routeIs('projects.schedule.*') ? 'bg-gold-50 text-gold-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700' }}">
                <div class="flex items-center">
                    <x-heroicon-o-calendar class="w-4 h-4 mr-2" />
                    Jadwal
                </div>
            </a>
            @endcan

            <!-- Analysis -->
            @can('analysis.view')
            <a href="{{ route('projects.analysis.index', $project) }}"
                class="px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors duration-150 {{ request()->routeIs('projects.analysis.*') ? 'bg-gold-50 text-gold-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700' }}">
                <div class="flex items-center">
                    <x-heroicon-o-beaker class="w-4 h-4 mr-2" />
                    Analisis
                </div>
            </a>
            @endcan

            @canany(['mr.view', 'pr.view', 'po.view', 'gr.view', 'usage.view', 'procurement.view'])
            <div class="relative" x-data="{ 
                open: false,
                updatePosition() {
                    if (this.open) {
                        const btn = this.$refs.logistikBtn;
                        const rect = btn.getBoundingClientRect();
                        const dropdown = this.$refs.logistikDropdown;
                        dropdown.style.top = (rect.bottom + 4) + 'px';
                        dropdown.style.left = rect.left + 'px';
                    }
                }
            }" @click.away="open = false">
                <button x-ref="logistikBtn" @click="open = !open; $nextTick(() => updatePosition())" type="button"
                    class="px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors duration-150 {{ request()->routeIs('projects.mr.*') || request()->routeIs('projects.pr.*') || request()->routeIs('projects.po.*') || request()->routeIs('projects.gr.*') || request()->routeIs('projects.usage.*') ? 'bg-gold-50 text-gold-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700' }}">
                    <div class="flex items-center">
                        <x-heroicon-o-cube class="w-4 h-4 mr-2" />
                        Logistik
                        <svg class="w-4 h-4 ml-1 transition-transform" :class="{ 'rotate-180': open }" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </button>
                <template x-teleport="body">
                    <div x-ref="logistikDropdown" x-show="open" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        x-cloak
                        class="fixed w-48 rounded-md shadow-lg bg-white dark:bg-dark-700 ring-1 ring-black ring-opacity-5"
                        style="z-index: 9999;">
                        <div class="py-1">
                            @can('mr.view')
                            <a href="{{ route('projects.mr.index', $project) }}"
                                class="flex items-center px-4 py-2 text-sm {{ request()->routeIs('projects.mr.*') ? 'bg-gray-100 dark:bg-dark-600 text-gold-700 dark:text-gold-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-600' }}">
                                <x-heroicon-o-archive-box class="w-4 h-4 mr-2" />
                                Material Request
                            </a>
                            @endcan
                            @can('pr.view')
                            <a href="{{ route('projects.pr.index', $project) }}"
                                class="flex items-center px-4 py-2 text-sm {{ request()->routeIs('projects.pr.*') ? 'bg-gray-100 dark:bg-dark-600 text-gold-700 dark:text-gold-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-600' }}">
                                <x-heroicon-o-shopping-cart class="w-4 h-4 mr-2" />
                                Purchase Request
                            </a>
                            @endcan
                            @can('po.view')
                            <a href="{{ route('projects.po.index', $project) }}"
                                class="flex items-center px-4 py-2 text-sm {{ request()->routeIs('projects.po.*') ? 'bg-gray-100 dark:bg-dark-600 text-gold-700 dark:text-gold-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-600' }}">
                                <x-heroicon-o-document-text class="w-4 h-4 mr-2" />
                                Purchase Order
                            </a>
                            @endcan
                            @can('gr.view')
                            <a href="{{ route('projects.gr.index', $project) }}"
                                class="flex items-center px-4 py-2 text-sm {{ request()->routeIs('projects.gr.*') ? 'bg-gray-100 dark:bg-dark-600 text-gold-700 dark:text-gold-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-600' }}">
                                <x-heroicon-o-truck class="w-4 h-4 mr-2" />
                                Terima Barang
                            </a>
                            @endcan
                            <hr class="my-1 border-gray-200 dark:border-gray-600">
                            @can('usage.view')
                            <a href="{{ route('projects.usage.index', $project) }}"
                                class="flex items-center px-4 py-2 text-sm {{ request()->routeIs('projects.usage.*') ? 'bg-gray-100 dark:bg-dark-600 text-gold-700 dark:text-gold-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-600' }}">
                                <x-heroicon-o-chart-bar class="w-4 h-4 mr-2" />
                                Material Usage
                            </a>
                            @endcan
                        </div>
                    </div>
                </template>
            </div>
            @endcanany

            <!-- Progress -->
            <a href="{{ route('projects.progress.index', $project) }}"
                class="px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors duration-150 {{ request()->routeIs('projects.progress.*') ? 'bg-gold-50 text-gold-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700' }}">
                <div class="flex items-center">
                    <x-heroicon-o-chart-bar class="w-4 h-4 mr-2" />
                    Laporan
                </div>
            </a>

            <!-- Weekly Reports -->
            @can('weekly_report.view')
            <a href="{{ route('projects.weekly-reports.index', $project) }}"
                class="px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors duration-150 {{ request()->routeIs('projects.weekly-reports.*') ? 'bg-gold-50 text-gold-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700' }}">
                <div class="flex items-center">
                    <x-heroicon-o-document-chart-bar class="w-4 h-4 mr-2" />
                    Weekly Report
                </div>
            </a>
            @endcan

            <!-- Financial / Cost Control -->
            @can('financials.view-report')
            <a href="{{ route('projects.financial.index', $project) }}"
                class="px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors duration-150 {{ request()->routeIs('projects.financial.*') ? 'bg-gold-50 text-gold-700 dark:bg-indigo-900/50 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700' }}">
                <div class="flex items-center">
                    <x-heroicon-o-banknotes class="w-4 h-4 mr-2" />
                    Finansial
                </div>
            </a>
            @endcan

        </div>
    </div>
</div>


