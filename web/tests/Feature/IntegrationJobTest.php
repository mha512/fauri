<?php

namespace Tests\Feature;

use App\Jobs\ClassifyMessageJob;
use App\Models\Business;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IntegrationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_classify_message_job_handles_success(): void
    {
        $user = User::factory()->create();
        $business = $user->currentBusiness();

        $message = Message::create([
            'business_id' => $business->id,
            'body' => 'bhai mera parcel kab tak dispatch hoga? tracking dain',
            'source' => 'paste',
            'category' => 'unclassified',
            'urgency' => 'medium',
            'status' => 'pending',
            'ai_status' => 'pending',
        ]);

        // Fake AI service response
        Http::fake([
            '*/classify' => Http::response([
                'category' => 'inquiry',
                'urgency' => 'medium',
                'summary' => 'Product/Service Inquiry: Tracking dispatch update',
                'confidence' => 0.9123,
            ], 200),
        ]);

        $job = new ClassifyMessageJob($message);
        $job->handle();

        $fresh = $message->fresh();
        $this->assertEquals('inquiry', $fresh->category);
        $this->assertEquals('medium', $fresh->urgency);
        $this->assertEquals('completed', $fresh->ai_status);
        $this->assertEquals(0.9123, $fresh->confidence);
    }

    public function test_classify_message_job_fallback_when_service_down(): void
    {
        $user = User::factory()->create();
        $business = $user->currentBusiness();

        $message = Message::create([
            'business_id' => $business->id,
            'body' => 'bhai parcel deliver hua k nahi?',
            'source' => 'paste',
            'category' => 'unclassified',
            'urgency' => 'medium',
            'status' => 'pending',
            'ai_status' => 'pending',
        ]);

        // Fake AI service connection failure
        Http::fake([
            '*/classify' => Http::response('Server Error', 500),
        ]);

        $job = new ClassifyMessageJob($message);
        // Exhaust retries to trigger fallback
        $job->failed(new \Exception("Connection refused"));

        $fresh = $message->fresh();
        $this->assertEquals('unclassified', $fresh->category);
        $this->assertEquals('failed', $fresh->ai_status);
        $this->assertStringContainsString('AI Service unavailable', $fresh->ai_error);
    }
}
