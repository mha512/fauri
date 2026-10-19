<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Message;
use App\Models\User;
use App\Services\WhatsAppExportParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FauriTriageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_dashboard_and_has_default_business(): void
    {
        $user = User::factory()->create(['name' => 'Tariq Mehmood']);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        $business = $user->currentBusiness();
        $this->assertNotNull($business);
        $this->assertEquals("Tariq Mehmood's Store", $business->name);
    }

    public function test_whatsapp_export_parser_creates_customers_and_messages(): void
    {
        $user = User::factory()->create();
        $business = $user->currentBusiness();

        $sampleChat = "19/02/2026, 14:20 - Messages and calls are end-to-end encrypted.\n" .
                      "19/02/2026, 14:22 - Fatima Zahra: AOA bhai mera parcel kab tak dispatch hoga?\n" .
                      "19/02/2026, 14:25 - +92 300 1234567: please urgent refund send karein order cancel krna ha\n";

        $parser = new WhatsAppExportParser();
        $result = $parser->parseAndImport($sampleChat, $business);

        $this->assertEquals(2, $result['messages_created']);
        $this->assertEquals(2, $result['customers_created']);

        $this->assertDatabaseHas('customers', [
            'business_id' => $business->id,
            'name' => 'Fatima Zahra',
        ]);

        $this->assertDatabaseHas('messages', [
            'business_id' => $business->id,
            'source' => 'whatsapp_export',
        ]);
    }

    public function test_livewire_dashboard_status_change(): void
    {
        $user = User::factory()->create();
        $business = $user->currentBusiness();

        $message = Message::create([
            'business_id' => $business->id,
            'body' => 'bhai order confirm karein',
            'source' => 'paste',
            'category' => 'order',
            'urgency' => 'low',
            'status' => 'pending',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Dashboard::class)
            ->call('updateMessageStatus', $message->id, 'resolved');

        $this->assertEquals('resolved', $message->fresh()->status);
    }

    public function test_urdu_script_detection_and_direction(): void
    {
        $urduMessage = new Message([
            'body' => 'میرا آرڈر کب تک ڈیلیور ہوگا؟ ٹریکنگ نمبر دے دیں',
        ]);

        $this->assertTrue($urduMessage->isUrdu());
        $this->assertEquals('rtl', $urduMessage->direction());

        $englishMessage = new Message([
            'body' => 'Where is my order dispatch tracking?',
        ]);

        $this->assertFalse($englishMessage->isUrdu());
        $this->assertEquals('ltr', $englishMessage->direction());
    }
}
