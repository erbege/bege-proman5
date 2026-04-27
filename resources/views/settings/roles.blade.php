<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Pengaturan'],
            ['label' => 'Kelola Role & Permission']
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Kelola Role & Permission
        </h2>
    </x-slot>

    <livewire:role-manager />
</x-app-layout>


