<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-8 text-center sm:text-left">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Reset Password') }}</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">
                {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.') }}
            </p>
        </div>

        @session('status')
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-100 dark:border-green-800 rounded-xl font-medium text-sm text-green-600 dark:text-green-400">
                {{ $value }}
            </div>
        @endsession

        <x-validation-errors class="mb-6" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email Address') }}" class="text-gray-700 dark:text-gray-300 font-semibold mb-1.5" />
                <x-input id="email" class="block w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border-gray-200 dark:border-dark-700 focus:ring-primary-500 focus:border-primary-500 rounded-xl transition duration-200" type="email" name="email" :value="old('email')" required
                    autofocus autocomplete="username" placeholder="name@company.com" />
            </div>

            <div class="pt-2">
                <x-button class="w-full justify-center py-3.5 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-bold rounded-xl shadow-lg shadow-primary-600/20 transform active:scale-[0.98] transition-all duration-200">
                    {{ __('Email Password Reset Link') }}
                </x-button>
            </div>

            <div class="text-center mt-6">
                <a href="{{ route('login') }}" class="text-sm font-bold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors">
                    {{ __('Back to login') }}
                </a>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>