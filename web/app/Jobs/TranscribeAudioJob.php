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
use Illuminate\Support\Facades\Storage;
use Throwable;

class TranscribeAudioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [5, 15, 45];
    public int $timeout = 90; // Whisper can take a few seconds on CPU

    public function __construct(public Message $message)
    {
    }

    public function handle(): void
    {
        $this->message->update(['ai_status' => 'processing']);

        if (!$this->message->audio_path) {
            $this->message->update([
                'ai_status' => 'failed',
                'ai_error' => 'No audio file attached to message',
            ]);
            return;
        }

        $fullPath = Storage::disk('public')->path($this->message->audio_path);
        if (!file_exists($fullPath)) {
            $this->message->update([
                'ai_status' => 'failed',
                'ai_error' => "Audio file missing at path: {$this->message->audio_path}",
            ]);
            return;
        }

        $aiUrl = rtrim(config('services.ai.url', 'http://127.0.0.1:8001'), '/');
        $timeout = (int) config('services.ai.timeout', 45);

        try {
            $audioContents = file_get_contents($fullPath);
            $filename = basename($fullPath);

            $response = Http::timeout($timeout)
                ->attach('file', $audioContents, $filename)
                ->post("{$aiUrl}/transcribe");

            if ($response->successful()) {
                $data = $response->json();
                $transcript = trim($data['text'] ?? '');
                $language = $data['language'] ?? 'ur';

                $this->message->update([
                    'body' => $transcript ?: '[Voice Note: Inaudible or Silence]',
                    'detected_language' => $language,
                    'ai_status' => 'processing',
                ]);

                Log::info("Voice message #{$this->message->id} transcribed: \"{$transcript}\" ({$language})");

                // Immediately trigger classification of transcribed text
                if ($transcript) {
                    ClassifyMessageJob::dispatch($this->message);
                } else {
                    $this->message->update([
                        'category' => 'other',
                        'urgency' => 'low',
                        'summary' => 'Voice Note: Inaudible / Empty audio',
                        'ai_status' => 'completed',
                    ]);
                }
            } else {
                throw new \Exception("AI Service returned HTTP {$response->status()}: " . $response->body());
            }
        } catch (Throwable $e) {
            Log::warning("TranscribeAudioJob attempt #{$this->attempts()} failed for message #{$this->message->id}: {$e->getMessage()}");

            if ($this->attempts() < $this->tries) {
                throw $e;
            }

            $this->fallback($e);
        }
    }

    public function failed(Throwable $exception): void
    {
        $this->fallback($exception);
    }

    protected function fallback(Throwable $exception): void
    {
        $this->message->update([
            'category' => 'unclassified',
            'urgency' => 'medium',
            'ai_status' => 'failed',
            'ai_error' => 'Audio transcription unavailable: ' . substr($exception->getMessage(), 0, 250),
        ]);
    }
}
