<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Message;
use App\Jobs\ClassifyMessageJob;
use App\Jobs\TranscribeAudioJob;
use Illuminate\Support\Facades\Storage;

echo "=== Fauri End-to-End System Integration Test ===" . PHP_EOL;

$user = User::where('email', 'demo@fauri.pk')->first();
if (!$user) {
    echo "ERROR: Demo user not found! Running seed..." . PHP_EOL;
    Artisan::call('db:seed');
    $user = User::where('email', 'demo@fauri.pk')->firstOrFail();
}
$business = $user->currentBusiness();
echo "Business: {$business->name} (ID: {$business->id})" . PHP_EOL;

// ---------------------------------------------------------
// STEP 3.1: Create message by paste via app flow & verify queue classification
// ---------------------------------------------------------
echo PHP_EOL . "--- [TEST 1] Create Message by Paste & Queue Classification ---" . PHP_EOL;

$customer = Customer::firstOrCreate(
    ['business_id' => $business->id, 'phone' => '+923009988771'],
    ['name' => 'Tariq Mehmood']
);

$pasteText = "AOA bhai, mera parcel tracking #4490 abhi tak deliver nahi hua, kal mera event hai bohat emergency hai please rider ko call karein foran!";
echo "Pasting message: \"{$pasteText}\"" . PHP_EOL;

$msg1 = Message::create([
    'business_id' => $business->id,
    'customer_id' => $customer->id,
    'body' => $pasteText,
    'source' => 'paste',
    'category' => 'unclassified',
    'urgency' => 'medium',
    'status' => 'pending',
    'ai_status' => 'pending',
    'message_timestamp' => now(),
]);

echo "Created Message ID: {$msg1->id}. Dispatching ClassifyMessageJob to queue..." . PHP_EOL;
ClassifyMessageJob::dispatch($msg1);

echo "Waiting for queue worker to pick up and classify..." . PHP_EOL;
$start = time();
$classified = false;
while (time() - $start < 30) {
    sleep(1);
    $msg1->refresh();
    if ($msg1->ai_status === 'completed' || $msg1->ai_status === 'failed') {
        $classified = true;
        break;
    }
    echo ".";
}
echo PHP_EOL;

if ($classified && $msg1->ai_status === 'completed') {
    echo "SUCCESS: Message classified by queue worker!" . PHP_EOL;
    echo "  DB ID:       {$msg1->id}" . PHP_EOL;
    echo "  Category:    {$msg1->category}" . PHP_EOL;
    echo "  Urgency:     {$msg1->urgency}" . PHP_EOL;
    echo "  Confidence:  {$msg1->confidence}" . PHP_EOL;
    echo "  Summary:     {$msg1->summary}" . PHP_EOL;
    echo "  AI Status:   {$msg1->ai_status}" . PHP_EOL;
} else {
    echo "FAILED: Message status is {$msg1->ai_status}, error: {$msg1->ai_error}" . PHP_EOL;
}

// ---------------------------------------------------------
// STEP 3.2: Upload audio file & verify TranscribeAudioJob -> ClassifyMessageJob
// ---------------------------------------------------------
echo PHP_EOL . "--- [TEST 2] Upload Audio & Queue Transcribe -> Classify Pipeline ---" . PHP_EOL;

$sampleAudio = realpath(__DIR__ . '/../ai-service/urdu_sample.mp3');
if (!$sampleAudio || !file_exists($sampleAudio)) {
    die("Sample audio file not found at ../ai-service/urdu_sample.mp3" . PHP_EOL);
}

// Place in storage/app/public/voice_notes/
Storage::disk('public')->makeDirectory('voice_notes');
$relPath = 'voice_notes/e2e_test_' . time() . '.mp3';
copy($sampleAudio, Storage::disk('public')->path($relPath));
echo "Copied audio to public storage: {$relPath}" . PHP_EOL;

$msg2 = Message::create([
    'business_id' => $business->id,
    'customer_id' => $customer->id,
    'body' => '[Audio Voice Note uploaded - transcribing...]',
    'source' => 'voice',
    'category' => 'unclassified',
    'urgency' => 'medium',
    'status' => 'pending',
    'audio_path' => $relPath,
    'ai_status' => 'pending',
    'message_timestamp' => now(),
]);

echo "Created Voice Message ID: {$msg2->id}. Dispatching TranscribeAudioJob to queue..." . PHP_EOL;
TranscribeAudioJob::dispatch($msg2);

echo "Waiting for queue worker to transcribe audio and then classify..." . PHP_EOL;
$start = time();
$audioCompleted = false;
while (time() - $start < 60) {
    sleep(2);
    $msg2->refresh();
    echo " [ai_status: {$msg2->ai_status}, cat: {$msg2->category}] ";
    if ($msg2->ai_status === 'completed' || $msg2->ai_status === 'failed') {
        $audioCompleted = true;
        break;
    }
}
echo PHP_EOL;

if ($audioCompleted && $msg2->ai_status === 'completed') {
    echo "SUCCESS: Voice message transcribed and classified by queue worker!" . PHP_EOL;
    echo "  DB ID:              {$msg2->id}" . PHP_EOL;
    echo "  Audio Path:         {$msg2->audio_path}" . PHP_EOL;
    echo "  Transcribed Text:   {$msg2->body}" . PHP_EOL;
    echo "  Detected Language:  {$msg2->detected_language}" . PHP_EOL;
    echo "  Category:           {$msg2->category}" . PHP_EOL;
    echo "  Urgency:            {$msg2->urgency}" . PHP_EOL;
    echo "  Summary:            {$msg2->summary}" . PHP_EOL;
    echo "  AI Status:          {$msg2->ai_status}" . PHP_EOL;
} else {
    echo "FAILED: Voice message status is {$msg2->ai_status}, error: {$msg2->ai_error}" . PHP_EOL;
}

echo PHP_EOL . "=== End of Integration Test ===" . PHP_EOL;
