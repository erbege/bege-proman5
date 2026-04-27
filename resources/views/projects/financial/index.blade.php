<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Proyek', 'url' => route('projects.index')],
            ['label' => $project->name, 'url' => route('projects.show', $project)],
            ['label' => 'Cost Control']
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Cost Control / Finansial
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->code }} - {{ $project->name }}</p>
            </div>
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $summary['cost_variance'] >= 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                    {{ $summary['health_status'] }}
                </span>
            </div>
        </div>
    </x-slot>

    @include('projects.navigation')

    <div class="py-4">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <livewire:financial-dashboard :project="$project" />
        </div>
    </div>
</x-app-layout>


