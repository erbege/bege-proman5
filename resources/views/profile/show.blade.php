<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Profil Pengguna']
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-black text-3xl text-gray-900 dark:text-white leading-tight tracking-tight uppercase">
                    {{ __('Profile') }}
                </h2>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Kelola informasi akun dan pengaturan keamanan Anda.') }}
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <div class="px-4 py-2 bg-white dark:bg-dark-800 rounded-xl border border-gray-100 dark:border-dark-700 shadow-sm flex items-center">
                    <x-heroicon-o-user class="w-5 h-5 text-primary-500 mr-2" />
                    <span class="text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-widest">{{ Auth::user()->name }}</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50/50 dark:bg-dark-950/50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')

                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.update-password-form')
                </div>

                <x-section-border />
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.two-factor-authentication-form')
                </div>

                <x-section-border />
            @endif

            <div class="mt-10 sm:mt-0">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <x-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire('profile.delete-user-form')
                </div>
            @endif
        </div>
    </div>
</x-app-layout>