<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Create Account') }}</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">{{ __('Join Proman 5 to manage your projects effectively.') }}</p>
        </div>

        <x-validation-errors class="mb-6" />

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <div>
                <x-label for="name" value="{{ __('Full Name') }}" class="text-gray-700 dark:text-gray-300 font-semibold mb-1.5" />
                <x-input id="name" class="block w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border-gray-200 dark:border-dark-700 focus:ring-primary-500 focus:border-primary-500 rounded-xl transition duration-200" type="text" name="name" :value="old('name')" required
                    autofocus autocomplete="name" placeholder="John Doe" />
            </div>

            <div>
                <x-label for="email" value="{{ __('Email Address') }}" class="text-gray-700 dark:text-gray-300 font-semibold mb-1.5" />
                <x-input id="email" class="block w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border-gray-200 dark:border-dark-700 focus:ring-primary-500 focus:border-primary-500 rounded-xl transition duration-200" type="email" name="email" :value="old('email')" required
                    autocomplete="username" placeholder="name@company.com" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-label for="password" value="{{ __('Password') }}" class="text-gray-700 dark:text-gray-300 font-semibold mb-1.5" />
                    <x-input id="password" class="block w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border-gray-200 dark:border-dark-700 focus:ring-primary-500 focus:border-primary-500 rounded-xl transition duration-200" type="password" name="password" required
                        autocomplete="new-password" placeholder="••••••••" />
                </div>

                <div>
                    <x-label for="password_confirmation" value="{{ __('Confirm') }}" class="text-gray-700 dark:text-gray-300 font-semibold mb-1.5" />
                    <x-input id="password_confirmation" class="block w-full px-4 py-3 bg-gray-50 dark:bg-dark-800 border-gray-200 dark:border-dark-700 focus:ring-primary-500 focus:border-primary-500 rounded-xl transition duration-200" type="password"
                        name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                </div>
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <label for="terms" class="flex items-start cursor-pointer group">
                        <x-checkbox name="terms" id="terms" required class="mt-1 rounded text-primary-600 focus:ring-primary-500 bg-gray-50 dark:bg-dark-800 border-gray-300 dark:border-dark-700" />
                        <div class="ms-3 text-sm text-gray-600 dark:text-gray-400 leading-tight">
                            {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                'terms_of_service' => '<a target="_blank" href="' . route('terms.show') . '" class="font-bold text-primary-600 hover:text-primary-700 underline">' . __('Terms') . '</a>',
                                'privacy_policy' => '<a target="_blank" href="' . route('policy.show') . '" class="font-bold text-primary-600 hover:text-primary-700 underline">' . __('Privacy') . '</a>',
                            ]) !!}
                        </div>
                    </label>
                </div>
            @endif

            <div class="pt-2">
                <x-button class="w-full justify-center py-3.5 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-bold rounded-xl shadow-lg shadow-primary-600/20 transform active:scale-[0.98] transition-all duration-200">
                    {{ __('Create Account') }}
                </x-button>
            </div>

            <div class="text-center mt-6">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Already have an account?') }}
                    <a href="{{ route('login') }}" class="font-bold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors ml-1">
                        {{ __('Sign in instead') }}
                    </a>
                </p>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>