<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-black text-3xl text-gray-900 dark:text-white leading-tight tracking-tight uppercase">
                    PRO<span class="text-primary-500">MAN</span> <span
                        class="text-gray-400 dark:text-gray-500">DB</span>
                </h2>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Welcome back,') }} <span
                        class="text-primary-600 dark:text-primary-400 font-bold">{{ Auth::user()->name }}</span>
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <div
                    class="px-4 py-2 bg-white dark:bg-dark-800 rounded-xl border border-gray-100 dark:border-dark-700 shadow-sm flex items-center">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse mr-2"></div>
                    <span class="text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-widest">System
                        Online</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-4 bg-gray-50/50 dark:bg-dark-950/50">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            <!-- Row 1: High-Level Stats (Glassmorphism inspired) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <!-- Total Projects -->
                <div
                    class="group bg-white dark:bg-dark-900 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 p-4 border border-gray-100 dark:border-dark-800 relative overflow-hidden">
                    <div
                        class="absolute top-0 right-0 w-24 h-24 bg-primary-500/5 rounded-bl-full translate-x-8 -translate-y-8 group-hover:translate-x-4 group-hover:-translate-y-4 transition-transform duration-500">
                    </div>
                    <div class="flex items-center justify-between relative z-10">
                        <div>
                            <p class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">
                                Total Overview</p>
                            <p class="text-4xl font-black text-gray-900 dark:text-white mt-1 leading-none">
                                {{ $totalProjects }}
                            </p>
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 mt-2 uppercase">Proyek
                                Terdaftar</p>
                        </div>
                        <div
                            class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                            <x-heroicon-o-folder class="w-8 h-8 text-primary-600 dark:text-primary-400" />
                        </div>
                    </div>
                    <div
                        class="mt-6 pt-4 border-t border-gray-50 dark:border-dark-800 flex items-center justify-between text-[10px] font-black uppercase tracking-widest">
                        <span class="text-green-600 dark:text-green-400">{{ $activeProjects }} Aktif</span>
                        <div class="w-1 h-1 bg-gray-300 dark:bg-dark-700 rounded-full"></div>
                        <span class="text-blue-600 dark:text-blue-400">{{ $completedProjects }} Selesai</span>
                    </div>
                </div>

                <!-- Active Projects -->
                <div
                    class="group bg-white dark:bg-dark-900 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 p-4 border border-gray-100 dark:border-dark-800 relative overflow-hidden">
                    <div
                        class="absolute top-0 right-0 w-24 h-24 bg-green-500/5 rounded-bl-full translate-x-8 -translate-y-8 group-hover:translate-x-4 group-hover:-translate-y-4 transition-transform duration-500">
                    </div>
                    <div class="flex items-center justify-between relative z-10">
                        <div>
                            <p class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">
                                Execution</p>
                            <p class="text-4xl font-black text-green-600 dark:text-green-400 mt-1 leading-none">
                                {{ $activeProjects }}
                            </p>
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 mt-2 uppercase">Sedang Berjalan
                            </p>
                        </div>
                        <div
                            class="p-4 bg-green-50 dark:bg-green-900/20 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                            <x-heroicon-o-play-circle class="w-8 h-8 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                    <div
                        class="mt-6 pt-4 border-t border-gray-50 dark:border-dark-800 flex items-center justify-between text-[10px] font-black uppercase tracking-widest">
                        <span class="text-yellow-600 dark:text-yellow-400">{{ $onHoldProjects }} On Hold</span>
                        <div class="w-1 h-1 bg-gray-300 dark:bg-dark-700 rounded-full"></div>
                        <span class="text-gray-400 dark:text-gray-500">{{ $planningProjects }} Planning</span>
                    </div>
                </div>

                <!-- Pending Approvals -->
                <div
                    class="group bg-white dark:bg-dark-900 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 p-4 border border-gray-100 dark:border-dark-800 relative overflow-hidden">
                    <div
                        class="absolute top-0 right-0 w-24 h-24 bg-orange-500/5 rounded-bl-full translate-x-8 -translate-y-8 group-hover:translate-x-4 group-hover:-translate-y-4 transition-transform duration-500">
                    </div>
                    <div class="flex items-center justify-between relative z-10">
                        <div>
                            <p class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">
                                Workflow</p>
                            <p class="text-4xl font-black text-orange-600 dark:text-orange-400 mt-1 leading-none">
                                {{ $totalPendingApprovals }}
                            </p>
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 mt-2 uppercase">Butuh
                                Persetujuan</p>
                        </div>
                        <div
                            class="p-4 bg-orange-50 dark:bg-orange-900/20 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                            <x-heroicon-o-clock class="w-8 h-8 text-orange-600 dark:text-orange-400" />
                        </div>
                    </div>
                    <div
                        class="mt-6 pt-4 border-t border-gray-50 dark:border-dark-800 flex items-center justify-between text-[10px] font-black uppercase tracking-widest">
                        <span>MR: {{ $pendingMR }}</span>
                        <div class="w-1 h-1 bg-gray-300 dark:bg-dark-700 rounded-full"></div>
                        <span>PR: {{ $pendingPR }}</span>
                        <div class="w-1 h-1 bg-gray-300 dark:bg-dark-700 rounded-full"></div>
                        <span>PO: {{ $pendingPO }}</span>
                    </div>
                </div>

                <!-- Material Alerts -->
                <div
                    class="group bg-white dark:bg-dark-900 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-300 p-4 border border-gray-100 dark:border-dark-800 relative overflow-hidden">
                    <div
                        class="absolute top-0 right-0 w-24 h-24 bg-red-500/5 rounded-bl-full translate-x-8 -translate-y-8 group-hover:translate-x-4 group-hover:-translate-y-4 transition-transform duration-500">
                    </div>
                    <div class="flex items-center justify-between relative z-10">
                        <div>
                            <p class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">
                                Inventory</p>
                            <p class="text-4xl font-black text-red-600 dark:text-red-400 mt-1 leading-none">
                                {{ $lowStockItems->count() + $outOfStockCount }}
                            </p>
                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 mt-2 uppercase">Alert Logistik
                            </p>
                        </div>
                        <div
                            class="p-4 bg-red-50 dark:bg-red-900/20 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                            <x-heroicon-o-exclamation-triangle class="w-8 h-8 text-red-600 dark:text-red-400" />
                        </div>
                    </div>
                    <div
                        class="mt-6 pt-4 border-t border-gray-50 dark:border-dark-800 flex items-center justify-between text-[10px] font-black uppercase tracking-widest">
                        <span class="text-red-700 dark:text-red-400">{{ $outOfStockCount }} Habis</span>
                        <div class="w-1 h-1 bg-gray-300 dark:bg-dark-700 rounded-full"></div>
                        <span class="text-yellow-600 dark:text-yellow-500 font-bold">{{ $lowStockItems->count() }}
                            Rendah</span>
                    </div>
                </div>
            </div>

            <!-- Row 2: Charts & Visual Intelligence -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
                <!-- Project Status Distribution -->
                <div
                    class="bg-white dark:bg-dark-900 rounded-3xl shadow-sm p-4 border border-gray-100 dark:border-dark-800">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Alokasi
                            Status</h3>
                        <div class="w-8 h-8 bg-gray-50 dark:bg-dark-800 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-funnel class="w-4 h-4 text-gray-400" />
                        </div>
                    </div>
                    <div id="status-chart" class="flex justify-center min-h-[250px]"></div>
                </div>

                <!-- Performance Metrics -->
                <div
                    class="bg-white dark:bg-dark-900 rounded-3xl shadow-sm p-4 border border-gray-100 dark:border-dark-800">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Efisiensi
                            Tim</h3>
                        <div class="w-8 h-8 bg-gray-50 dark:bg-dark-800 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-bolt class="w-4 h-4 text-primary-500" />
                        </div>
                    </div>
                    <div id="completion-chart" class="flex justify-center min-h-[250px]"></div>
                </div>

                <!-- Projects Progress List -->
                <div
                    class="bg-white dark:bg-dark-900 rounded-[2.5rem] shadow-sm p-4 border border-gray-100 dark:border-dark-800 flex flex-col">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Live
                            Progress</h3>
                        <span
                            class="text-[10px] font-black bg-primary-500 text-white px-2 py-0.5 rounded-full uppercase tracking-tighter animate-pulse">Live</span>
                    </div>
                    <div class="space-y-5 overflow-y-auto max-h-[280px] custom-scrollbar pr-2">
                        @forelse($projectProgress as $project)
                            <div class="group">
                                <div class="flex justify-between items-center mb-2">
                                    <a href="{{ route('projects.show', $project['id']) }}"
                                        class="text-xs font-bold text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors truncate max-w-[180px]">
                                        {{ $project['name'] }}
                                    </a>
                                    <span
                                        class="text-xs font-black {{ $project['deviation'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $project['actual'] }}%
                                    </span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-dark-800 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-1000 ease-out {{ $project['deviation'] >= 0 ? 'bg-gradient-to-r from-green-400 to-green-600' : 'bg-gradient-to-r from-red-400 to-red-600' }}"
                                        style="width: {{ min($project['actual'], 100) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center h-full py-10 opacity-40">
                                <x-heroicon-o-inbox class="w-12 h-12 mb-3" />
                                <p class="text-xs font-bold uppercase tracking-widest">Hening di sini...</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Row 3: Operational Intelligence -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                <!-- Critical Project Alerts -->
                <div
                    class="bg-white dark:bg-dark-900 rounded-3xl shadow-sm p-4 border border-gray-100 dark:border-dark-800 relative overflow-hidden">
                    @if($projectsWithIssues->count() > 0)
                        <div
                            class="absolute top-0 right-0 w-32 h-32 bg-red-500/5 rounded-bl-full translate-x-12 -translate-y-12">
                        </div>
                    @endif
                    <div class="flex items-center justify-between mb-8 relative z-10">
                        <h3
                            class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest flex items-center">
                            <x-heroicon-o-fire class="w-5 h-5 mr-2 text-red-500 animate-bounce" />
                            Zona Kritis
                        </h3>
                        <span
                            class="text-[10px] font-black bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 px-3 py-1 rounded-full uppercase tracking-widest">
                            {{ $projectsWithIssues->count() }} Isu
                        </span>
                    </div>
                    <div class="space-y-4 max-h-[320px] overflow-y-auto pr-2 custom-scrollbar">
                        @forelse($projectsWithIssues as $project)
                            <a href="{{ route('projects.show', $project['id']) }}"
                                class="flex items-center justify-between p-4 bg-red-50/50 dark:bg-red-900/10 rounded-2xl border border-red-100 dark:border-red-900/30 hover:shadow-md transition group">
                                <div class="flex items-center">
                                    <div
                                        class="w-10 h-10 bg-white dark:bg-dark-800 rounded-xl flex items-center justify-center shadow-sm mr-4 group-hover:scale-110 transition">
                                        <x-heroicon-o-exclamation-circle class="w-6 h-6 text-red-500" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-extrabold text-gray-900 dark:text-white">
                                            {{ $project['name'] }}
                                        </p>
                                        <p class="text-[10px] text-red-500 font-bold uppercase tracking-tighter mt-0.5">
                                            Deviasi Progress Terdeteksi</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xl font-black text-red-600">{{ $project['deviation'] }}%</p>
                                    <p class="text-[9px] text-gray-400 font-bold uppercase leading-none mt-1">Below Target
                                    </p>
                                </div>
                            </a>
                        @empty
                            <div class="flex flex-col items-center justify-center py-16 text-gray-400 space-y-4">
                                <div
                                    class="w-20 h-20 bg-green-50 dark:bg-green-900/10 rounded-full flex items-center justify-center animate-pulse">
                                    <x-heroicon-o-check-badge class="w-10 h-10 text-green-500" />
                                </div>
                                <p class="text-xs font-black uppercase tracking-widest">Semua Proyek Sesuai Target!</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Activity Feed -->
                <div
                    class="bg-white dark:bg-dark-900 rounded-3xl shadow-sm p-4 border border-gray-100 dark:border-dark-800">
                    <div class="flex items-center justify-between mb-8">
                        <h3
                            class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest flex items-center">
                            <x-heroicon-o-list-bullet class="w-5 h-5 mr-2 text-primary-500" />
                            Log Aktivitas Terkini
                        </h3>
                    </div>
                    <div class="space-y-6 max-h-[320px] overflow-y-auto pr-2 custom-scrollbar">
                        @forelse($recentReports as $report)
                            <div class="relative pl-8 group">
                                <div
                                    class="absolute left-0 top-0 bottom-0 w-px bg-gray-100 dark:bg-dark-800 group-last:bg-transparent">
                                </div>
                                <div
                                    class="absolute left-[-4px] top-1.5 w-2 h-2 rounded-full border-2 border-primary-500 bg-white dark:bg-dark-900 ring-4 ring-primary-500/10">
                                </div>

                                <div
                                    class="p-4 bg-gray-50/50 dark:bg-dark-800/30 rounded-2xl border border-transparent group-hover:border-gray-100 dark:group-hover:border-dark-700 transition">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4
                                            class="text-xs font-extrabold text-gray-900 dark:text-white truncate max-w-[200px]">
                                            {{ $report->rabItem->work_name ?? 'Item Pekerjaan' }}
                                        </h4>
                                        <span
                                            class="text-[10px] font-black text-primary-500 bg-primary-500/5 px-2 py-0.5 rounded-full">+{{ number_format($report->progress_percentage, 1) }}%</span>
                                    </div>
                                    <p
                                        class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-tighter">
                                        {{ $report->project->name ?? 'Unknown Project' }} •
                                        {{ $report->report_date->format('d M, H:i') }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-20 opacity-30">
                                <x-heroicon-o-clock class="w-12 h-12 mb-2" />
                                <p class="text-xs font-black uppercase tracking-widest">Menunggu Laporan Masuk...</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Row 4: Grid Master & Controller -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Data Projects Table -->
                <div
                    class="lg:col-span-2 bg-white dark:bg-dark-900 rounded-3xl shadow-sm p-4 border border-gray-100 dark:border-dark-800 overflow-hidden">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Master
                            Proyek Terbaru</h3>
                        <a href="{{ route('projects.index') }}"
                            class="text-[10px] font-black text-primary-600 hover:text-primary-700 dark:text-primary-400 uppercase tracking-[0.2em] flex items-center">
                            Master View <x-heroicon-o-arrow-right class="w-3 h-3 ml-2" />
                        </a>
                    </div>
                    <div class="overflow-x-auto -mx-8 px-8">
                        <table class="min-w-full text-left">
                            <thead class="border-b border-gray-50 dark:border-dark-800">
                                <tr
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                                    <th class="pb-4 pt-2">Informasi Proyek</th>
                                    <th class="pb-4 pt-2">Klien Utama</th>
                                    <th class="pb-4 pt-2 text-right">Status State</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                @forelse($recentProjects as $project)
                                    <tr class="group hover:bg-gray-50/50 dark:hover:bg-dark-800/20 transition-colors">
                                        <td class="py-5">
                                            <a href="{{ route('projects.show', $project) }}" class="block">
                                                <p
                                                    class="text-sm font-extrabold text-gray-900 dark:text-white group-hover:text-primary-500 transition-colors leading-tight">
                                                    {{ $project->name }}
                                                </p>
                                                <p
                                                    class="text-[10px] text-gray-400 font-bold uppercase mt-1 tracking-tighter">
                                                    {{ $project->code }}
                                                </p>
                                            </a>
                                        </td>
                                        <td class="py-5">
                                            <p
                                                class="text-[11px] font-bold text-gray-600 dark:text-gray-300 uppercase leading-none">
                                                {{ $project->client_name }}
                                            </p>
                                            <p class="text-[9px] text-gray-400 uppercase mt-1">{{ ucfirst($project->type) }}
                                            </p>
                                        </td>
                                        <td class="py-5 text-right">
                                            <span class="inline-flex items-center px-4 py-1 rounded-full text-[9px] font-black uppercase tracking-widest
                                                                    @if($project->status === 'active') bg-green-500/10 text-green-600 border border-green-500/20
                                                                    @elseif($project->status === 'completed') bg-blue-500/10 text-blue-600 border border-blue-500/20
                                                                    @elseif($project->status === 'on_hold') bg-yellow-500/10 text-yellow-600 border border-yellow-500/20
                                                                    @else bg-gray-500/10 text-gray-600 border border-gray-500/20
                                                                    @endif">
                                                {{ str_replace('_', ' ', $project->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3"
                                            class="py-10 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">
                                            Log Kosong</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Strategic Controller -->
                <div class="bg-dark-900 rounded-3xl shadow-2xl p-4 border border-white/5 relative overflow-hidden">
                    <div
                        class="absolute top-0 right-0 w-32 h-32 bg-primary-500/10 rounded-bl-full translate-x-12 -translate-y-12 blur-3xl">
                    </div>

                    <h3 class="text-sm font-black text-white uppercase tracking-widest mb-8 relative z-10">Strategic
                        Access</h3>

                    <div class="grid grid-cols-1 gap-4 relative z-10 font-sans">
                        @can('projects.create')
                        <a href="{{ route('projects.create') }}"
                            class="flex items-center p-5 bg-gradient-to-r from-primary-600 to-primary-700 rounded-3xl hover:translate-y-[-4px] transition-all duration-300 shadow-xl shadow-primary-900/40 group">
                            <div class="p-3 bg-white/20 rounded-2xl group-hover:scale-110 transition">
                                <x-heroicon-o-plus class="w-6 h-6 text-white" />
                            </div>
                            <div class="ml-4">
                                <span class="block text-sm font-black text-white uppercase tracking-tight">Init
                                    Project</span>
                                <span class="text-[10px] text-white/60 font-medium tracking-widest leading-none">Create
                                    a new core record</span>
                            </div>
                        </a>
                        @endcan

                        <a href="{{ route('projects.index') }}"
                            class="flex items-center p-5 bg-dark-800 rounded-3xl hover:bg-dark-700 transition-all group border border-white/5">
                            <div class="p-3 bg-primary-500/10 rounded-2xl group-hover:bg-primary-500/20 transition">
                                <x-heroicon-o-folder-open class="w-6 h-6 text-primary-500" />
                            </div>
                            <div class="ml-4 text-white">
                                <span class="block text-sm font-black uppercase tracking-tight">Project Matrix</span>
                                <span class="text-[10px] text-white/40 font-medium tracking-widest leading-none">Access
                                    full database list</span>
                            </div>
                        </a>

                        @can('inventory.view')
                        <a href="{{ route('inventory.index') }}"
                            class="flex items-center p-5 bg-dark-800 rounded-3xl hover:bg-dark-700 transition-all group border border-white/5">
                            <div class="p-3 bg-white/5 rounded-2xl group-hover:bg-white/10 transition">
                                <x-heroicon-o-cube
                                    class="w-6 h-6 text-gray-400 group-hover:text-primary-400 transition" />
                            </div>
                            <div class="ml-4 text-white">
                                <span class="block text-sm font-black uppercase tracking-tight">Warehouse Ops</span>
                                <span class="text-[10px] text-white/40 font-medium tracking-widest leading-none">Monitor
                                    inventory & stock</span>
                            </div>
                        </a>
                        @endcan

                        @can('materials.view')
                        <a href="{{ route('materials.index') }}"
                            class="flex items-center p-5 bg-dark-800 rounded-3xl hover:bg-dark-700 transition-all group border border-white/5">
                            <div class="p-3 bg-white/5 rounded-2xl group-hover:bg-white/10 transition">
                                <x-heroicon-o-squares-2x2
                                    class="w-6 h-6 text-gray-400 group-hover:text-primary-400 transition" />
                            </div>
                            <div class="ml-4 text-white">
                                <span class="block text-sm font-black uppercase tracking-tight">Master Material</span>
                                <span
                                    class="text-[10px] text-white/40 font-medium tracking-widest leading-none">Technical
                                    data management</span>
                            </div>
                        </a>
                        @endcan
                    </div>

                    <div class="mt-10 p-4 bg-primary-500/5 rounded-[2rem] border border-primary-500/10">
                        <p class="text-[10px] font-black text-primary-500 uppercase tracking-[0.2em] mb-2">Technical
                            Summary</p>
                        <div class="flex justify-between items-end">
                            <span
                                class="text-2xl font-black text-white leading-none tracking-tighter">{{ $totalProjects }}
                                <span class="text-[10px] text-gray-500 uppercase font-bold ml-1">Nodes</span></span>
                            <div class="flex items-center text-[10px] font-bold text-gray-500 tracking-tighter">
                                <x-heroicon-o-cloud-arrow-up class="w-4 h-4 mr-1 text-primary-500" />
                                Synchronized
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const isDark = document.documentElement.classList.contains('dark');
                const textColor = isDark ? '#9CA3AF' : '#6B7280';
                const gridColor = isDark ? '#1F2937' : '#F3F4F6';

                // Status Distribution Donut Chart
                const statusOptions = {
                    series: [{{ $statusDistribution['planning'] }}, {{ $statusDistribution['active'] }}, {{ $statusDistribution['on_hold'] }}, {{ $statusDistribution['completed'] }}],
                    chart: {
                        type: 'donut',
                        height: 280,
                        background: 'transparent',
                        fontFamily: 'Inter, sans-serif'
                    },
                    labels: ['Planning Phase', 'Active Ops', 'On Deployment Hold', 'Finalized'],
                    colors: ['#eab308', '#22c55e', '#f97316', '#3b82f6'],
                    legend: {
                        position: 'bottom',
                        labels: {
                            colors: textColor,
                            useSeriesColors: false
                        },
                        fontSize: '11px',
                        fontWeight: 900,
                        markers: { radius: 12 }
                    },
                    dataLabels: { enabled: false },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '75%',
                                labels: {
                                    show: true,
                                    name: {
                                        show: true,
                                        fontSize: '10px',
                                        fontWeight: 900,
                                        color: textColor,
                                        offsetY: -10,
                                        textAnchor: 'middle',
                                        formatter: function (val) { return val.toUpperCase(); }
                                    },
                                    value: {
                                        show: true,
                                        fontSize: '32px',
                                        fontWeight: 900,
                                        color: isDark ? '#FFFFFF' : '#111827',
                                        offsetY: 10,
                                        formatter: function (val) { return val; }
                                    },
                                    total: {
                                        show: true,
                                        label: 'PROJECTS',
                                        color: textColor,
                                        formatter: function (w) {
                                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        }
                                    }
                                }
                            }
                        }
                    },
                    stroke: { show: false }
                };
                new ApexCharts(document.querySelector("#status-chart"), statusOptions).render();

                // Completion Radial Chart
                const completionOptions = {
                    series: [{{ $avgCompletion }}],
                    chart: {
                        type: 'radialBar',
                        height: 280,
                        background: 'transparent'
                    },
                    plotOptions: {
                        radialBar: {
                            startAngle: -135,
                            endAngle: 135,
                            hollow: { size: '65%' },
                            track: {
                                background: isDark ? '#111827' : '#F3F4F6',
                                strokeWidth: '100%',
                                margin: 5
                            },
                            dataLabels: {
                                name: {
                                    show: true,
                                    color: textColor,
                                    fontSize: '10px',
                                    fontWeight: 900,
                                    offsetY: 20
                                },
                                value: {
                                    show: true,
                                    color: isDark ? '#FFFFFF' : '#111827',
                                    fontSize: '44px',
                                    fontWeight: 900,
                                    offsetY: -15,
                                    formatter: function (val) { return val + '%'; }
                                }
                            }
                        }
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shade: 'dark',
                            type: 'horizontal',
                            gradientToColors: ['#eab308'],
                            stops: [0, 100]
                        }
                    },
                    colors: ['#22c55e'],
                    labels: ['AVG EFFICIENCY']
                };
                new ApexCharts(document.querySelector("#completion-chart"), completionOptions).render();
            });
        </script>
        <style>
            .custom-scrollbar::-webkit-scrollbar {
                width: 4px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #eab308;
                border-radius: 10px;
            }

            .dark .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #383838;
            }
        </style>
    @endpush
</x-app-layout>


