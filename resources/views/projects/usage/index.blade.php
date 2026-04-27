<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
        ['label' => 'Proyek', 'url' => route('projects.index')],
        ['label' => $project->name, 'url' => route('projects.show', $project)],
        ['label' => 'Penggunaan Material']
    ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Penggunaan Material - {{ $project->name }}
        </h2>
    </x-slot>

    @include('projects.navigation')

    <livewire:material-usage-manager :project="$project" />

</x-app-layout>


