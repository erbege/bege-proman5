<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Pengaturan Sistem']
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Pengaturan Sistem
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('settings.system.update') }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Storage Settings --}}
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Pengaturan Penyimpanan</h3>

                        {{-- Storage Driver --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Driver Penyimpanan</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="relative flex cursor-pointer rounded-lg border p-4 focus:outline-none 
                                    {{ ($storageSettings['storage_driver']->value ?? 'local') === 'local' ? 'border-gold-500 bg-gold-50 dark:bg-gold-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                                    <input type="radio" name="storage_driver" value="local" class="sr-only"
                                        {{ ($storageSettings['storage_driver']->value ?? 'local') === 'local' ? 'checked' : '' }}
                                        onchange="toggleS3Settings()">
                                    <div class="flex flex-1">
                                        <div class="flex flex-col">
                                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Local Storage</span>
                                            <span class="mt-1 text-sm text-gray-500">Simpan file di server lokal</span>
                                        </div>
                                    </div>
                                    <x-heroicon-o-server class="h-6 w-6 text-gray-400" />
                                </label>

                                <label class="relative flex cursor-pointer rounded-lg border p-4 focus:outline-none 
                                    {{ ($storageSettings['storage_driver']->value ?? 'local') === 's3' ? 'border-gold-500 bg-gold-50 dark:bg-gold-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                                    <input type="radio" name="storage_driver" value="s3" class="sr-only"
                                        {{ ($storageSettings['storage_driver']->value ?? 'local') === 's3' ? 'checked' : '' }}
                                        onchange="toggleS3Settings()">
                                    <div class="flex flex-1">
                                        <div class="flex flex-col">
                                            <span class="block text-sm font-medium text-gray-900 dark:text-white">Amazon S3</span>
                                            <span class="mt-1 text-sm text-gray-500">Simpan file di cloud AWS S3</span>
                                        </div>
                                    </div>
                                    <x-heroicon-o-cloud class="h-6 w-6 text-gray-400" />
                                </label>
                            </div>
                        </div>

                        {{-- Max File Size --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ukuran File Maksimum</label>
                            <div class="flex items-center gap-4">
                                <input type="range" name="max_file_size" id="maxFileSize" min="1048576" max="104857600" step="1048576"
                                    value="{{ $storageSettings['max_file_size']->value ?? 52428800 }}"
                                    class="flex-1" onchange="updateFileSizeLabel()">
                                <span id="fileSizeLabel" class="text-sm font-medium text-gray-900 dark:text-white w-20 text-right">
                                    {{ number_format(($storageSettings['max_file_size']->value ?? 52428800) / 1048576) }} MB
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Range: 1 MB - 100 MB</p>
                        </div>

                        {{-- S3 Configuration --}}
                        <div id="s3Settings" class="{{ ($storageSettings['storage_driver']->value ?? 'local') === 's3' ? '' : 'hidden' }}">
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Konfigurasi Amazon S3</h4>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bucket Name</label>
                                        <input type="text" name="s3_bucket" value="{{ $storageSettings['s3_bucket']->value ?? '' }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Region</label>
                                        <select name="s3_region" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                            @foreach(['us-east-1', 'us-west-2', 'eu-west-1', 'ap-southeast-1', 'ap-southeast-2', 'ap-southeast-3','ap-northeast-1'] as $region)
                                                <option value="{{ $region }}" {{ ($storageSettings['s3_region']->value ?? '') === $region ? 'selected' : '' }}>{{ $region }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Access Key ID</label>
                                        <input type="password" name="s3_key" placeholder="••••••••"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                        <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin mengubah</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Secret Access Key</label>
                                        <input type="password" name="s3_secret" placeholder="••••••••"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                        <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin mengubah</p>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="button" onclick="testS3Connection()"
                                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700">
                                        <x-heroicon-o-signal class="w-4 h-4 mr-2" />
                                        Test Koneksi S3
                                    </button>
                                    <span id="s3TestResult" class="ml-4 text-sm"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- AI Configuration Settings --}}
                <div class="bg-white dark:bg-dark-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">
                            <x-heroicon-o-cpu-chip class="w-5 h-5 inline mr-1" />
                            Konfigurasi AI
                        </h3>

                        {{-- AI Provider Selection --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Provider AI Utama</label>
                            <div class="grid grid-cols-5 gap-3">
                                @php
                                    $providers = [
                                        'none' => ['label' => 'Tidak Aktif', 'icon' => 'x-circle', 'color' => 'gray-400'],
                                        'openai' => ['label' => 'OpenAI', 'icon' => 'bolt', 'color' => 'green-500'],
                                        'gemini' => ['label' => 'Gemini', 'icon' => 'sparkles', 'color' => 'blue-500'],
                                        'claude' => ['label' => 'Claude', 'icon' => 'chat-bubble-left-right', 'color' => 'orange-500'],
                                        'openrouter' => ['label' => 'OpenRouter', 'icon' => 'globe-alt', 'color' => 'purple-500'],
                                    ];
                                @endphp
                                @foreach($providers as $key => $provider)
                                    <label class="relative flex cursor-pointer rounded-lg border p-3 focus:outline-none 
                                        {{ ($aiSettings['ai_provider']->value ?? 'none') === $key ? 'border-gold-500 bg-gold-50 dark:bg-gold-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                                        <input type="radio" name="ai_provider" value="{{ $key }}" class="sr-only"
                                            {{ ($aiSettings['ai_provider']->value ?? 'none') === $key ? 'checked' : '' }}
                                            onchange="toggleAISettings()">
                                        <div class="flex flex-1 flex-col text-center">
                                            <x-dynamic-component :component="'heroicon-o-' . $provider['icon']" class="mx-auto h-5 w-5 text-{{ $provider['color'] }}" />
                                            <span class="mt-1 text-xs font-medium text-gray-900 dark:text-white">{{ $provider['label'] }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- OpenAI Settings --}}
                        <div id="openaiSettings" class="{{ ($aiSettings['ai_provider']->value ?? 'none') === 'openai' ? '' : 'hidden' }}">
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">
                                    <x-heroicon-o-bolt class="w-4 h-4 inline mr-1 text-green-500" />
                                    Konfigurasi OpenAI
                                </h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Key</label>
                                        <input type="password" name="openai_api_key" placeholder="sk-••••••••"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                        <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin mengubah</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
                                        <select name="openai_model" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                            @foreach(['gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-4', 'gpt-3.5-turbo'] as $model)
                                                <option value="{{ $model }}" {{ ($aiSettings['openai_model']->value ?? 'gpt-4o-mini') === $model ? 'selected' : '' }}>{{ $model }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @if(isset($aiSettings['openai_api_key']) && $aiSettings['openai_api_key']->value)
                                    <p class="mt-2 text-xs text-green-600">✓ API Key sudah dikonfigurasi</p>
                                @endif
                            </div>
                        </div>

                        {{-- Gemini Settings --}}
                        <div id="geminiSettings" class="{{ ($aiSettings['ai_provider']->value ?? 'none') === 'gemini' ? '' : 'hidden' }}">
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">
                                    <x-heroicon-o-sparkles class="w-4 h-4 inline mr-1 text-blue-500" />
                                    Konfigurasi Google Gemini
                                </h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Key</label>
                                        <input type="password" name="gemini_api_key" placeholder="••••••••"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                        <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin mengubah</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
                                        <select name="gemini_model" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                            @foreach(['gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-2.0-flash', 'gemini-pro'] as $model)
                                                <option value="{{ $model }}" {{ ($aiSettings['gemini_model']->value ?? 'gemini-1.5-flash') === $model ? 'selected' : '' }}>{{ $model }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @if(isset($aiSettings['gemini_api_key']) && $aiSettings['gemini_api_key']->value)
                                    <p class="mt-2 text-xs text-green-600">✓ API Key sudah dikonfigurasi</p>
                                @endif
                            </div>
                        </div>

                        {{-- Claude Settings --}}
                        <div id="claudeSettings" class="{{ ($aiSettings['ai_provider']->value ?? 'none') === 'claude' ? '' : 'hidden' }}">
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">
                                    <x-heroicon-o-chat-bubble-left-right class="w-4 h-4 inline mr-1 text-orange-500" />
                                    Konfigurasi Anthropic Claude
                                </h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Key</label>
                                        <input type="password" name="claude_api_key" placeholder="sk-ant-••••••••"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                        <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin mengubah</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
                                        <select name="claude_model" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                            @foreach(['claude-sonnet-4-20250514', 'claude-3-7-sonnet-20250219', 'claude-3-5-sonnet-20241022', 'claude-3-5-haiku-20241022', 'claude-3-opus-20240620'] as $model)
                                                <option value="{{ $model }}" {{ ($aiSettings['claude_model']->value ?? 'claude-sonnet-4-20250514') === $model ? 'selected' : '' }}>{{ $model }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @if(isset($aiSettings['claude_api_key']) && $aiSettings['claude_api_key']->value)
                                    <p class="mt-2 text-xs text-green-600">✓ API Key sudah dikonfigurasi</p>
                                @endif
                            </div>
                        </div>

                        {{-- OpenRouter Settings --}}
                        <div id="openrouterSettings" class="{{ ($aiSettings['ai_provider']->value ?? 'none') === 'openrouter' ? '' : 'hidden' }}">
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">
                                    <x-heroicon-o-globe-alt class="w-4 h-4 inline mr-1 text-purple-500" />
                                    Konfigurasi OpenRouter
                                </h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Key</label>
                                        <input type="password" name="openrouter_api_key" placeholder="sk-or-••••••••"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                        <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin mengubah</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
                                        <select name="openrouter_model" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-dark-700 dark:text-white">
                                            @foreach(['openai/gpt-4o', 'anthropic/claude-3.5-sonnet', 'google/gemini-pro-1.5', 'meta-llama/llama-3.1-70b-instruct', 'mistralai/mistral-large'] as $model)
                                                <option value="{{ $model }}" {{ ($aiSettings['openrouter_model']->value ?? 'openai/gpt-4o') === $model ? 'selected' : '' }}>{{ $model }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">OpenRouter memberikan akses ke berbagai model AI melalui satu API</p>
                                @if(isset($aiSettings['openrouter_api_key']) && $aiSettings['openrouter_api_key']->value)
                                    <p class="mt-1 text-xs text-green-600">✓ API Key sudah dikonfigurasi</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Save Button --}}
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-gold-500 text-white font-medium rounded-md hover:bg-gold-600">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleS3Settings() {
            const s3Settings = document.getElementById('s3Settings');
            const isS3 = document.querySelector('input[name="storage_driver"]:checked').value === 's3';
            s3Settings.classList.toggle('hidden', !isS3);

            // Update visual selection
            document.querySelectorAll('input[name="storage_driver"]').forEach(input => {
                const label = input.closest('label');
                if (input.checked) {
                    label.classList.add('border-gold-500', 'bg-gold-50', 'dark:bg-gold-900/20');
                    label.classList.remove('border-gray-200', 'dark:border-gray-700');
                } else {
                    label.classList.remove('border-gold-500', 'bg-gold-50', 'dark:bg-gold-900/20');
                    label.classList.add('border-gray-200', 'dark:border-gray-700');
                }
            });
        }

        function updateFileSizeLabel() {
            const slider = document.getElementById('maxFileSize');
            const label = document.getElementById('fileSizeLabel');
            const mb = Math.round(slider.value / 1048576);
            label.textContent = mb + ' MB';
        }

        function testS3Connection() {
            const resultSpan = document.getElementById('s3TestResult');
            resultSpan.innerHTML = '<span class="text-gray-500">Testing...</span>';

            fetch('{{ route("settings.system.test-s3") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultSpan.innerHTML = '<span class="text-green-600">✓ ' + data.message + '</span>';
                } else {
                    resultSpan.innerHTML = '<span class="text-red-600">✗ ' + data.message + '</span>';
                }
            })
            .catch(error => {
                resultSpan.innerHTML = '<span class="text-red-600">✗ Error: ' + error.message + '</span>';
            });
        }

        function toggleAISettings() {
            const openaiSettings = document.getElementById('openaiSettings');
            const geminiSettings = document.getElementById('geminiSettings');
            const claudeSettings = document.getElementById('claudeSettings');
            const openrouterSettings = document.getElementById('openrouterSettings');
            const selected = document.querySelector('input[name="ai_provider"]:checked').value;

            openaiSettings.classList.toggle('hidden', selected !== 'openai');
            geminiSettings.classList.toggle('hidden', selected !== 'gemini');
            claudeSettings.classList.toggle('hidden', selected !== 'claude');
            openrouterSettings.classList.toggle('hidden', selected !== 'openrouter');

            // Update visual selection
            document.querySelectorAll('input[name="ai_provider"]').forEach(input => {
                const label = input.closest('label');
                if (input.checked) {
                    label.classList.add('border-gold-500', 'bg-gold-50', 'dark:bg-gold-900/20');
                    label.classList.remove('border-gray-200', 'dark:border-gray-700');
                } else {
                    label.classList.remove('border-gold-500', 'bg-gold-50', 'dark:bg-gold-900/20');
                    label.classList.add('border-gray-200', 'dark:border-gray-700');
                }
            });
        }
    </script>
</x-app-layout>
