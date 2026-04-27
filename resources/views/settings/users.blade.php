<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Pengaturan'],
            ['label' => 'Kelola User']
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Kelola User
        </h2>
    </x-slot>

    <livewire:user-manager />
</x-app-layout>


