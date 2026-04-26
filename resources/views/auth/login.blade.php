<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Welcome Back') }}</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">{{ __('Please enter your details to sign in.') }}</p>
        </div>

        <x-validation-errors class="mb-6" />

        @session('status')
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-100 dark:border-green-800 rounded-xl font-medium text-sm text-green-600 dark:text-green-400">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email Address') }}" class="text-gray-700 dark:text-gray-300 font-semibold mb-1.5" />
                <x-input id="email" class="block w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border-gray-200 dark:border-dark-700 focus:ring-primary-500 focus:border-primary-500 rounded-xl transition duration-200" type="email" name="email" :value="old('email')" required
                    autofocus autocomplete="username" placeholder="name@company.com" />
            </div>

            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <x-label for="password" value="{{ __('Password') }}" class="text-gray-700 dark:text-gray-300 font-semibold" />
                    @if (Route::has('password.request'))
                        <a class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors"
                            href="{{ route('password.request') }}">
                            {{ __('Forgot Password?') }}
                        </a>
                    @endif
                </div>
                <x-input id="password" class="block w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border-gray-200 dark:border-dark-700 focus:ring-primary-500 focus:border-primary-500 rounded-xl transition duration-200" type="password" name="password" required
                    autocomplete="current-password" placeholder="••••••••" />
            </div>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                    <x-checkbox id="remember_me" name="remember" class="rounded text-primary-600 focus:ring-primary-500 bg-gray-50 dark:bg-dark-800 border-gray-300 dark:border-dark-700" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-200 transition-colors">{{ __('Stay signed in') }}</span>
                </label>
            </div>

            <div>
                <x-button class="w-full justify-center py-3.5 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-bold rounded-xl shadow-lg shadow-primary-600/20 transform active:scale-[0.98] transition-all duration-200">
                    {{ __('Sign In to Dashboard') }}
                </x-button>
            </div>

            @if (Route::has('register'))
                <div class="text-center mt-8">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __("Don't have an account?") }}
                        <a href="{{ route('register') }}" class="font-bold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors ml-1">
                            {{ __('Register here') }}
                        </a>
                    </p>
                </div>
            @endif
        </form>
    </x-authentication-card>
</x-guest-layout>