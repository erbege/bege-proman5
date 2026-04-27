<div class="min-h-screen flex bg-white dark:bg-dark-950 font-sans antialiased overflow-hidden">
    <!-- Left Side: Visual/Branding (Hidden on mobile) -->
    <div class="hidden lg:flex lg:w-1/2 bg-dark-900 relative overflow-hidden items-center justify-center">
        <!-- Modern Abstract Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary-600/20 via-dark-900 to-dark-950 z-10"></div>

        <!-- Background Pattern (Geometric dots) -->
        <div class="absolute inset-0 opacity-10"
            style="background-image: radial-gradient(circle at 2px 2px, #eab308 1px, transparent 0); background-size: 24px 24px;">
        </div>

        <!-- Floating Glass Orbs for Depth -->
        <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-primary-500/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-primary-600/5 rounded-full blur-3xl animate-pulse"
            style="animation-delay: 1s;"></div>

        <div class="relative z-20 text-center p-12 max-w-lg">
            <div class="mb-10 transform hover:scale-105 transition duration-500 flex justify-center">
                {{ $logo }}
            </div>
            <h1 class="text-5xl font-black text-white mb-4 tracking-tighter uppercase">
                PRO<span class="text-primary-500">MAN</span> 5
            </h1>
            <div class="h-1 w-20 bg-primary-500 mx-auto mb-6 rounded-full"></div>
            <p class="text-xl text-gray-400 font-medium leading-relaxed">
                {{ __('Streamlined Project & Material Management for Modern Enterprise') }}
            </p>

            <div class="mt-16 grid grid-cols-3 gap-4 opacity-40">
                <div class="flex flex-col items-center">
                    <x-heroicon-o-shield-check class="w-10 h-10 text-primary-500 mb-2" />
                    <span class="text-xs text-gray-400 font-semibold uppercase tracking-widest">Secure</span>
                </div>
                <div class="flex flex-col items-center">
                    <x-heroicon-o-chart-bar class="w-10 h-10 text-primary-500 mb-2" />
                    <span class="text-xs text-gray-400 font-semibold uppercase tracking-widest">Analytics</span>
                </div>
                <div class="flex flex-col items-center">
                    <x-heroicon-o-cpu-chip class="w-10 h-10 text-primary-500 mb-2" />
                    <span class="text-xs text-gray-400 font-semibold uppercase tracking-widest">Smart</span>
                </div>
            </div>
        </div>

        <!-- Bottom Branding -->
        <div class="absolute bottom-8 left-12 z-20">
            <p class="text-gray-500 text-sm font-medium tracking-wider">POWERED BY <span
                    class="text-gray-300 font-bold">CODECRAFTER</span></p>
        </div>
    </div>

    <!-- Right Side: Form Content -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-4 sm:p-12 bg-gray-50 dark:bg-dark-950 relative">
        <!-- Subtle mobile background element -->
        <div class="lg:hidden absolute top-0 right-0 w-32 h-32 bg-primary-500/5 rounded-bl-full blur-2xl"></div>

        <div class="w-full max-w-[440px] relative">
            <!-- Mobile Logo (Centered) -->
            <div class="lg:hidden flex justify-center mb-10">
                <div
                    class="p-3 bg-white dark:bg-dark-900 rounded-2xl shadow-xl border border-gray-100 dark:border-dark-800">
                    {{ $logo }}
                </div>
            </div>

            <!-- Auth Card -->
            <div
                class="bg-white dark:bg-dark-900 shadow-[0_20px_50px_rgba(0,0,0,0.1)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.3)] rounded-[2rem] overflow-hidden border border-gray-100 dark:border-dark-800/50 p-4 sm:p-12 transition-all duration-300 hover:shadow-[0_30px_60px_rgba(0,0,0,0.12)]">
                <div class="animate-fade-in">
                    {{ $slot }}
                </div>
            </div>

            <!-- Contextual Footer -->
            <div
                class="mt-10 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-400 dark:text-gray-500 font-medium px-4">
                <p>&copy; {{ date('Y') }} CODECRAFTER. v5.0.0</p>
                <div class="flex space-x-4 mt-2 sm:mt-0">
                    <a href="#" class="hover:text-primary-500 transition-colors">Privacy</a>
                    <a href="#" class="hover:text-primary-500 transition-colors">Terms</a>
                    <a href="#" class="hover:text-primary-500 transition-colors">Support</a>
                </div>
            </div>
        </div>
    </div>
</div>


