<?php

namespace App\Jobs;

use App\Models\RabItem;
use App\Services\AiMaterialAnalyzer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeRabItemMaterials implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public RabItem $rabItem,
        public string $provider = 'openai'
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(AiMaterialAnalyzer $analyzer): void
    {
        Log::info('Starting AI analysis for RAB item', [
            'rab_item_id' => $this->rabItem->id,
            'work_name' => $this->rabItem->work_name,
            'provider' => $this->provider,
        ]);

        $result = $analyzer->useProvider($this->provider)->analyzeRabItem($this->rabItem);

        if ($result['success']) {
            Log::info('AI analysis completed successfully', [
                'rab_item_id' => $this->rabItem->id,
                'materials_count' => $result['count'] ?? 0,
                'provider' => $this->provider,
            ]);
        } else {
            Log::warning('AI analysis failed', [
                'rab_item_id' => $this->rabItem->id,
                'message' => $result['message'] ?? 'Unknown error',
                'provider' => $this->provider,
            ]);
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AI analysis job failed', [
            'rab_item_id' => $this->rabItem->id,
            'provider' => $this->provider,
            'error' => $exception->getMessage(),
        ]);
    }
}
