<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    /**
     * Show system settings page (Superadmin only)
     */
    public function index()
    {
        $storageSettings = SystemSetting::where('group', 'storage')->get()->keyBy('key');
        $aiSettings = SystemSetting::where('group', 'ai')->get()->keyBy('key');

        return view('settings.system', compact('storageSettings', 'aiSettings'));
    }

    /**
     * Update system settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'storage_driver' => 'required|in:local,s3',
            'max_file_size' => 'required|integer|min:1048576|max:104857600', // 1MB - 100MB
            's3_bucket' => 'nullable|string|max:255',
            's3_region' => 'nullable|string|max:50',
            's3_key' => 'nullable|string|max:255',
            's3_secret' => 'nullable|string|max:255',
            // AI Settings
            'ai_provider' => 'nullable|in:openai,gemini,claude,openrouter,none',
            'openai_api_key' => 'nullable|string|max:255',
            'openai_model' => 'nullable|string|max:100',
            'gemini_api_key' => 'nullable|string|max:255',
            'gemini_model' => 'nullable|string|max:100',
            'claude_api_key' => 'nullable|string|max:255',
            'claude_model' => 'nullable|string|max:100',
            'openrouter_api_key' => 'nullable|string|max:255',
            'openrouter_model' => 'nullable|string|max:100',
        ]);

        // Update settings
        SystemSetting::setValue('storage_driver', $request->storage_driver);
        SystemSetting::setValue('max_file_size', $request->max_file_size);

        if ($request->storage_driver === 's3') {
            SystemSetting::setValue('s3_bucket', $request->s3_bucket);
            SystemSetting::setValue('s3_region', $request->s3_region);

            // Only update credentials if provided
            if ($request->filled('s3_key')) {
                $this->updateEnvValue('AWS_ACCESS_KEY_ID', $request->s3_key);
            }
            if ($request->filled('s3_secret')) {
                $this->updateEnvValue('AWS_SECRET_ACCESS_KEY', $request->s3_secret);
            }
            if ($request->filled('s3_bucket')) {
                $this->updateEnvValue('AWS_BUCKET', $request->s3_bucket);
            }
            if ($request->filled('s3_region')) {
                $this->updateEnvValue('AWS_DEFAULT_REGION', $request->s3_region);
            }
        }

        // Update AI Settings (stored in database only, no .env update needed)
        if ($request->filled('ai_provider')) {
            $this->updateOrCreateSetting('ai_provider', $request->ai_provider, 'string', 'ai');
        }
        if ($request->filled('openai_api_key')) {
            $this->updateOrCreateSetting('openai_api_key', $request->openai_api_key, 'encrypted', 'ai');
        }
        if ($request->filled('openai_model')) {
            $this->updateOrCreateSetting('openai_model', $request->openai_model, 'string', 'ai');
        }
        if ($request->filled('gemini_api_key')) {
            $this->updateOrCreateSetting('gemini_api_key', $request->gemini_api_key, 'encrypted', 'ai');
        }
        if ($request->filled('gemini_model')) {
            $this->updateOrCreateSetting('gemini_model', $request->gemini_model, 'string', 'ai');
        }
        if ($request->filled('claude_api_key')) {
            $this->updateOrCreateSetting('claude_api_key', $request->claude_api_key, 'encrypted', 'ai');
        }
        if ($request->filled('claude_model')) {
            $this->updateOrCreateSetting('claude_model', $request->claude_model, 'string', 'ai');
        }
        if ($request->filled('openrouter_api_key')) {
            $this->updateOrCreateSetting('openrouter_api_key', $request->openrouter_api_key, 'encrypted', 'ai');
        }
        if ($request->filled('openrouter_model')) {
            $this->updateOrCreateSetting('openrouter_model', $request->openrouter_model, 'string', 'ai');
        }

        return redirect()
            ->route('settings.system')
            ->with('success', 'Pengaturan sistem berhasil disimpan.');
    }

    /**
     * Update or create a setting
     */
    private function updateOrCreateSetting(string $key, mixed $value, string $type = 'string', string $group = 'general'): void
    {
        SystemSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group]
        );
        \Illuminate\Support\Facades\Cache::forget("setting_{$key}");
    }

    /**
     * Update .env value
     */
    private function updateEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        // Check if key exists
        if (preg_match("/^{$key}=.*/m", $content)) {
            $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
        } else {
            $content .= "\n{$key}={$value}";
        }

        file_put_contents($envPath, $content);
    }

    /**
     * Test S3 connection
     */
    public function testS3Connection(Request $request)
    {
        try {
            // Check if S3 is configured
            $bucket = config('filesystems.disks.s3.bucket');
            $key = config('filesystems.disks.s3.key');
            $region = config('filesystems.disks.s3.region');

            if (empty($bucket) || empty($key)) {
                return response()->json([
                    'success' => false,
                    'message' => 'S3 belum dikonfigurasi. Silakan isi AWS credentials di file .env atau simpan pengaturan terlebih dahulu.'
                ]);
            }

            $disk = \Illuminate\Support\Facades\Storage::disk('s3');
            $testFile = 'proman-test-' . time() . '.txt';
            $disk->put($testFile, 'connection test');
            $disk->delete($testFile);

            return response()->json(['success' => true, 'message' => 'Koneksi S3 berhasil! Bucket: ' . $bucket]);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            // Extract cleaner error message
            if (str_contains($message, 'Credentials must be an instance')) {
                $message = 'AWS credentials tidak valid atau belum dikonfigurasi.';
            } elseif (str_contains($message, 'Could not resolve host')) {
                $message = 'Tidak dapat terhubung ke S3. Periksa region dan koneksi internet.';
            }
            return response()->json(['success' => false, 'message' => 'Error: ' . $message]);
        }
    }
}
