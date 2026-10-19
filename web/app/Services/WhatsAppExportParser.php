<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WhatsAppExportParser
{
    /**
     * Parse raw WhatsApp export text and create customer/message records.
     *
     * @param string $content
     * @param Business $business
     * @param string|null $ownerName The business owner's sender name to ignore outgoing messages if desired
     * @return array Summary of import: ['total' => int, 'created' => int, 'customers' => int]
     */
    public function parseAndImport(string $content, Business $business, ?string $ownerName = null): array
    {
        $lines = preg_split("/\r\n|\n|\r/", $content);
        $rawMessages = [];
        $currentMsg = null;

        // Regex patterns for WhatsApp timestamps
        // Pattern 1 (Android): "19/02/2026, 14:22 - Sender: Message" or "19/02/26, 2:22 pm - Sender: Message"
        $patternAndroid = '/^(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4}),?\s+(\d{1,2}:\d{2}(?::\d{2})?(?:\s*[ap]m)?)\s*-\s*([^:]+):\s*(.*)$/i';
        
        // Pattern 2 (iOS): "[19/02/2026, 2:22:15 PM] Sender: Message"
        $patternIos = '/^\[(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{2,4}),?\s+(\d{1,2}:\d{2}(?::\d{2})?(?:\s*[ap]m)?)\]\s*([^:]+):\s*(.*)$/i';

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Check if line matches a new message header
            if (preg_match($patternAndroid, $line, $matches) || preg_match($patternIos, $line, $matches)) {
                if ($currentMsg !== null) {
                    $rawMessages[] = $currentMsg;
                }

                $dateStr = $matches[1];
                $timeStr = $matches[2];
                $sender = trim($matches[3]);
                $body = trim($matches[4]);

                // Try parsing timestamp
                $timestamp = null;
                try {
                    $timestamp = Carbon::parse("$dateStr $timeStr");
                } catch (\Exception $e) {
                    $timestamp = now();
                }

                $currentMsg = [
                    'date' => $timestamp,
                    'sender' => $sender,
                    'body' => $body,
                ];
            } else {
                // Continuation of previous message
                if ($currentMsg !== null) {
                    $currentMsg['body'] .= "\n" . $line;
                }
            }
        }

        if ($currentMsg !== null) {
            $rawMessages[] = $currentMsg;
        }

        $createdMessagesCount = 0;
        $customersMap = [];

        foreach ($rawMessages as $item) {
            $sender = $item['sender'];
            $body = trim($item['body']);

            // Ignore system notices
            if (str_contains($body, 'Messages and calls are end-to-end encrypted') ||
                str_contains($body, 'created group') ||
                str_contains($body, 'added you') ||
                str_contains($body, '<Media omitted>')) {
                continue;
            }

            // If ownerName specified and matches sender, skip business's own outgoing replies
            if ($ownerName && strcasecmp($sender, $ownerName) === 0) {
                continue;
            }

            // Find or create customer
            if (!isset($customersMap[$sender])) {
                $isPhone = preg_match('/^[+\d\s\-()]{7,}$/', $sender);
                $customer = Customer::firstOrCreate(
                    [
                        'business_id' => $business->id,
                        'name' => $sender,
                    ],
                    [
                        'phone' => $isPhone ? preg_replace('/[^\d+]/', '', $sender) : null,
                    ]
                );
                $customersMap[$sender] = $customer->id;
            }

            $customerId = $customersMap[$sender];

            // Create message
            Message::create([
                'business_id' => $business->id,
                'customer_id' => $customerId,
                'body' => $body,
                'source' => 'whatsapp_export',
                'category' => 'unclassified',
                'urgency' => 'medium',
                'status' => 'pending',
                'ai_status' => 'pending',
                'message_timestamp' => $item['date'] ?? now(),
            ]);

            $createdMessagesCount++;
        }

        return [
            'total_parsed' => count($rawMessages),
            'messages_created' => $createdMessagesCount,
            'customers_created' => count($customersMap),
        ];
    }
}
