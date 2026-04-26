<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Files']
    ]" />
    </x-slot>

    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Project Files - {{ $project->name }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->code }}</p>
        </div>
    </x-slot>

    @include('projects.navigation')

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:project-file-manager :project="$project" :folder="request()->query('folder')" />
        </div>
    </div>
</x-app-layout>