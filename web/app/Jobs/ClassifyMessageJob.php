<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ClassifyMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of attempts before marking failed.
     */
    public int $tries = 3;

    /**
     * Backoff sequence in seconds: 5s, 15s, 45s.
     */
    public array $backoff = [5, 15, 45];

    /**
     * Maximum execution timeout in seconds.
     */
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(public Message $message)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Mark message as processing
        $this->message->update(['ai_status' => 'processing']);

        $aiUrl = rtrim(config('services.ai.url', 'http://127.0.0.1:8001'), '/');
        $timeout = (int) config('services.ai.timeout', 20);
        $headers = [];
        if ($apiKey = config('services.ai.key')) {
            $headers['X-API-Key'] = $apiKey;
        }

        try {
            $response = Http::timeout($timeout)
                ->withHeaders($headers)
                ->acceptJson()
                ->post("{$aiUrl}/classify", [
                    'text' => $this->message->body,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                $this->message->update([
                    'category' => $data['category'] ?? 'other',
                    'urgency' => $data['urgency'] ?? 'medium',
                    'summary' => $data['summary'] ?? null,
                    'confidence' => $data['confidence'] ?? 0.5,
                    'ai_status' => 'completed',
                    'ai_error' => null,
                ]);

                Log::info("Message #{$this->message->id} successfully classified: {$data['category']} ({$data['urgency']})");
            } else {
                throw new \Exception("AI Service returned HTTP {$response->status()}: " . $response->body());
            }
        } catch (Throwable $e) {
            Log::warning("ClassifyMessageJob attempt #{$this->attempts()} failed for message #{$this->message->id}: {$e->getMessage()}");

            // If we still have retries, rethrow so queue mechanism applies backoff
            if ($this->attempts() < $this->tries) {
                throw $e;
            }

            // Fallback: save as unclassified with error note so UI can offer retry
            $this->fallback($e);
        }
    }

    /**
     * Handle job failure after all retries are exhausted.
     */
    public function failed(Throwable $exception): void
    {
        $this->fallback($exception);
    }

    /**
     * Apply graceful fallback state.
     */
    protected function fallback(Throwable $exception): void
    {
        $this->message->update([
            'category' => 'unclassified',
            'urgency' => 'medium',
            'ai_status' => 'failed',
            'ai_error' => 'AI Service unavailable: ' . substr($exception->getMessage(), 0, 250),
        ]);
    }
}
