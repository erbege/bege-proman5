<?php

namespace App\Services;

use App\Models\Material;
use App\Models\MaterialForecast;
use App\Models\RabItem;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiMaterialAnalyzer
{
    protected string $provider;
    protected string $model;

    public function __construct(protected MaterialMatcherService $matcher)
    {
        // Get provider from system settings, fallback to config
        $this->provider = SystemSetting::getValue('ai_provider', config('ai.default_provider', 'none'));
        $this->model = $this->getModelForProvider($this->provider);
    }

    /**
     * Get model for a specific provider from system settings
     */
    protected function getModelForProvider(string $provider): string
    {
        return match ($provider) {
            'openai' => SystemSetting::getValue('openai_model', 'gpt-4o-mini'),
            'gemini' => SystemSetting::getValue('gemini_model', 'gemini-1.5-flash'),
            'claude' => SystemSetting::getValue('claude_model', 'claude-sonnet-4-20250514'),
            'openrouter' => SystemSetting::getValue('openrouter_model', 'openai/gpt-4o'),
            default => 'gpt-4o-mini',
        };
    }

    /**
     * Get API key for a specific provider from system settings
     */
    public static function getApiKey(string $provider): ?string
    {
        return match ($provider) {
            'openai' => SystemSetting::getValue('openai_api_key') ?? config('openai.api_key'),
            'gemini' => SystemSetting::getValue('gemini_api_key') ?? config('gemini.api_key'),
            'claude' => SystemSetting::getValue('claude_api_key') ?? config('ai.providers.claude.api_key'),
            'openrouter' => SystemSetting::getValue('openrouter_api_key') ?? config('ai.providers.openrouter.api_key'),
            default => null,
        };
    }

    /**
     * Set the AI provider to use
     */
    public function useProvider(string $provider): self
    {
        $this->provider = $provider;
        $this->model = $this->getModelForProvider($provider);
        return $this;
    }

    /**
     * Check if a provider is configured (has API key)
     */
    public static function isProviderConfigured(string $provider): bool
    {
        return !empty(self::getApiKey($provider));
    }

    /**
     * Get the active provider from system settings
     */
    public static function getActiveProvider(): string
    {
        return SystemSetting::getValue('ai_provider', 'none');
    }

    /**
     * Get list of configured providers (that have API keys)
     */
    public static function getConfiguredProviders(): array
    {
        $providers = [];
        $activeProvider = self::getActiveProvider();

        // Always show the active provider first if configured
        if ($activeProvider !== 'none' && self::isProviderConfigured($activeProvider)) {
            $labels = [
                'openai' => 'OpenAI (' . SystemSetting::getValue('openai_model', 'gpt-4o-mini') . ')',
                'gemini' => 'Google Gemini (' . SystemSetting::getValue('gemini_model', 'gemini-1.5-flash') . ')',
                'claude' => 'Anthropic Claude (' . SystemSetting::getValue('claude_model', 'claude-sonnet-4-20250514') . ')',
                'openrouter' => 'OpenRouter (' . SystemSetting::getValue('openrouter_model', 'openai/gpt-4o') . ')',
            ];
            $providers[$activeProvider] = $labels[$activeProvider] ?? ucfirst($activeProvider);
        }

        // Add other configured providers
        foreach (['openai', 'gemini', 'claude', 'openrouter'] as $provider) {
            if ($provider !== $activeProvider && self::isProviderConfigured($provider)) {
                $labels = [
                    'openai' => 'OpenAI',
                    'gemini' => 'Google Gemini',
                    'claude' => 'Anthropic Claude',
                    'openrouter' => 'OpenRouter',
                ];
                $providers[$provider] = $labels[$provider];
            }
        }

        return $providers;
    }

    /**
     * Analyze a RAB item and predict required materials
     */
    public function analyzeRabItem(RabItem $item): array
    {
        $prompt = $this->buildPrompt($item);
        $systemPrompt = $this->getSystemPrompt();

        try {
            $response = match ($this->provider) {
                'openai' => $this->callOpenAI($systemPrompt, $prompt),
                'gemini' => $this->callGemini($systemPrompt, $prompt),
                'claude' => $this->callClaude($systemPrompt, $prompt),
                'openrouter' => $this->callOpenRouter($systemPrompt, $prompt),
                default => throw new \Exception("Unknown AI provider: {$this->provider}"),
            };

            $materials = json_decode($response, true);

            if (isset($materials['materials']) && is_array($materials['materials'])) {
                return $this->saveMaterialForecasts($item, $materials['materials'], $response);
            }

            return [
                'success' => false,
                'message' => 'Invalid response format from AI',
                'raw_response' => $response,
            ];
        } catch (\Exception $e) {
            Log::error('AI Material Analysis Error', [
                'provider' => $this->provider,
                'rab_item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Call OpenAI API via HTTP (reads API key from SystemSetting)
     */
    protected function callOpenAI(string $systemPrompt, string $userPrompt): string
    {
        $apiKey = self::getApiKey('openai');
        $model = $this->model ?? 'gpt-4o-mini';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.3,
                    'max_tokens' => 2000,
                ]);

        if (!$response->successful()) {
            throw new \Exception('OpenAI API error: ' . $response->body());
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Call Google Gemini API via HTTP (reads API key from SystemSetting)
     */
    protected function callGemini(string $systemPrompt, string $userPrompt): string
    {
        $apiKey = self::getApiKey('gemini');
        $model = $this->model ?? 'gemini-1.5-flash';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(60)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        ['parts' => [['text' => $systemPrompt . "\n\n" . $userPrompt . "\n\nRespond with valid JSON only."]]]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'maxOutputTokens' => 2000,
                    ],
                ]);

        if (!$response->successful()) {
            throw new \Exception('Gemini API error: ' . $response->body());
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        // Extract JSON from response if wrapped in markdown
        if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
            return $matches[1];
        }

        return $text;
    }

    /**
     * Call Anthropic Claude API via HTTP
     */
    protected function callClaude(string $systemPrompt, string $userPrompt): string
    {
        $apiKey = self::getApiKey('claude');
        $model = $this->model ?? 'claude-sonnet-4-20250514';

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
                    'model' => $model,
                    'max_tokens' => 2000,
                    'system' => $systemPrompt,
                    'messages' => [
                        ['role' => 'user', 'content' => $userPrompt . "\n\nRespond with valid JSON only."],
                    ],
                ]);

        if (!$response->successful()) {
            throw new \Exception('Claude API error: ' . $response->body());
        }

        $data = $response->json();
        $text = $data['content'][0]['text'] ?? '';

        // Extract JSON from response if wrapped in markdown
        if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
            return $matches[1];
        }

        return $text;
    }

    /**
     * Call OpenRouter API via HTTP
     */
    protected function callOpenRouter(string $systemPrompt, string $userPrompt): string
    {
        $apiKey = self::getApiKey('openrouter');
        $model = $this->model ?? 'openai/gpt-4o';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'HTTP-Referer' => config('app.url'),
            'X-Title' => config('app.name'),
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt . "\n\nRespond with valid JSON only."],
                    ],
                    'max_tokens' => 2000,
                    'temperature' => 0.3,
                ]);

        if (!$response->successful()) {
            throw new \Exception('OpenRouter API error: ' . $response->body());
        }

        $data = $response->json();
        $text = $data['choices'][0]['message']['content'] ?? '';

        // Extract JSON from response if wrapped in markdown
        if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
            return $matches[1];
        }

        return $text;
    }

    /**
     * Build the user prompt for material analysis
     */
    protected function buildPrompt(RabItem $item): string
    {
        return <<<PROMPT
Analisa item pekerjaan konstruksi berikut dan tentukan material yang dibutuhkan:

**Nama Pekerjaan:** {$item->work_name}
**Volume:** {$item->volume} {$item->unit}
**Deskripsi:** {$item->description}

Berdasarkan standar SNI dan koefisien analisa harga satuan pekerjaan konstruksi Indonesia, 
tentukan material-material yang dibutuhkan beserta estimasi kuantitasnya.

Kembalikan hasil dalam format JSON:
{
    "materials": [
        {
            "material_name": "nama material",
            "quantity": angka,
            "unit": "satuan",
            "coefficient": koefisien per satuan pekerjaan,
            "notes": "catatan jika ada"
        }
    ],
    "analysis_notes": "catatan analisis"
}
PROMPT;
    }

    /**
     * Get the system prompt for the AI
     */
    protected function getSystemPrompt(): string
    {
        return <<<SYSTEM
Anda adalah seorang estimator teknik sipil berpengalaman di Indonesia. 
Tugas Anda adalah menganalisa item pekerjaan konstruksi dan memperkirakan material yang dibutuhkan.

Aturan:
1. Gunakan koefisien standar SNI (Standar Nasional Indonesia) untuk analisa harga satuan
2. Pertimbangkan faktor wastage/losses yang wajar (5-10%)
3. Berikan estimasi yang realistis dan dapat dijadikan acuan untuk pengadaan
4. Jika item pekerjaan tidak jelas, berikan perkiraan terbaik dengan catatan
5. Selalu gunakan satuan yang umum digunakan di Indonesia (kg, m3, m2, bh, btg, zak, dll)

Contoh koefisien umum:
- Pasangan bata merah 1:4 per m2: bata 70 bh, semen 11.5 kg, pasir pasang 0.043 m3
- Plesteran 1:4 per m2: semen 6.24 kg, pasir pasang 0.024 m3
- Beton K-225 per m3: semen 371 kg, pasir 698 kg, kerikil 1047 kg, air 215 liter
- Pengecatan tembok per m2: cat 0.1 kg, plamir 0.1 kg

Selalu kembalikan response dalam format JSON yang valid.
SYSTEM;
    }

    /**
     * Save material forecasts to database
     * AI method: Save results directly without matching to local database
     */
    protected function saveMaterialForecasts(RabItem $item, array $materials, string $rawResponse): array
    {
        $savedCount = 0;
        $savedMaterials = [];

        foreach ($materials as $materialData) {
            // AI method: Do NOT match to local database
            // Save AI results directly as-is
            $forecast = MaterialForecast::create([
                'rab_item_id' => $item->id,
                'material_id' => null, // No local matching for AI
                'raw_material_name' => $materialData['material_name'] ?? 'Unknown',
                'estimated_qty' => $materialData['quantity'] ?? 0,
                'unit' => $materialData['unit'] ?? 'unit',
                'coefficient' => $materialData['coefficient'] ?? 0,
                'match_score' => 0, // No local matching score for AI
                'analysis_source' => $this->provider,
                'ai_response_raw' => ['full_response' => $rawResponse, 'provider' => $this->provider],
                'notes' => $materialData['notes'] ?? null,
            ]);

            $savedMaterials[] = $forecast;
            $savedCount++;
        }

        // Mark item as analyzed
        $item->update(['is_analyzed' => true]);

        return [
            'success' => true,
            'message' => "Berhasil menganalisis {$savedCount} material menggunakan " . ucfirst($this->provider),
            'count' => $savedCount,
            'materials' => $savedMaterials,
            'provider' => $this->provider,
        ];
    }

    /**
     * Get analysis summary for a project
     * Optimized: Uses database-level aggregation with pagination
     */
    public function getProjectAnalysisSummary(int $projectId, int $perPage = 20)
    {
        return MaterialForecast::query()
            ->join('rab_items', 'material_forecasts.rab_item_id', '=', 'rab_items.id')
            ->where('rab_items.project_id', $projectId)
            ->selectRaw('
                material_forecasts.raw_material_name as material_name,
                SUM(material_forecasts.estimated_qty) as total_qty,
                MIN(material_forecasts.unit) as unit,
                COUNT(*) as source_items
            ')
            ->groupBy('material_forecasts.raw_material_name')
            ->orderByDesc('total_qty')
            ->paginate($perPage, ['*'], 'summary_page');
    }
}
