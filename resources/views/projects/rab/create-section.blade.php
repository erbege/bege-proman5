<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Tambah Bagian Pekerjaan - {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('projects.rab.sections.store', $project) }}" class="p-6">
                    @csrf

                    <div class="mb-6">
                        <x-input-label for="code" :value="__('Kode Bagian')" />
                        <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code')"
                            required autofocus placeholder="A, B, C atau I, II, III" />
                        <x-input-error class="mt-2" :messages="$errors->get('code')" />
                    </div>

                    <div class="mb-6">
                        <x-input-label for="name" :value="__('Nama Bagian')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')"
                            required placeholder="Contoh: Pekerjaan Persiapan" />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div class="flex items-center justify-end">
                        <a href="{{ route('projects.rab.index', $project) }}"
                            class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 mr-4">Batal</a>
                        <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
