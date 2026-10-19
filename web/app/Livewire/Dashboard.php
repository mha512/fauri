<?php

namespace App\Livewire;

use App\Jobs\ClassifyMessageJob;
use App\Jobs\TranscribeAudioJob;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Message;
use App\Services\WhatsAppExportParser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination, WithFileUploads;

    // Filters and search
    public string $search = '';
    public string $selectedCategory = 'all';
    public string $selectedUrgency = 'all';
    public string $selectedStatus = 'all';
    public string $sortOrder = 'desc';

    // Bulk selection
    public array $selectedMessages = [];
    public bool $selectAll = false;

    // Modal state for New Message
    public bool $showNewMessageModal = false;
    public string $inputTab = 'paste'; // paste, export, voice

    // Paste Form fields
    public string $pasteCustomerName = '';
    public string $pasteCustomerPhone = '';
    public string $pasteBody = '';

    // WhatsApp Export fields
    public $exportFile;
    public string $exportOwnerName = '';

    // Voice Note fields
    public $voiceFile;
    public string $voiceCustomerName = '';
    public string $voiceCustomerPhone = '';

    // Message Detail Modal
    public ?int $selectedMessageId = null;
    public ?Message $activeMessage = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCategory' => ['except' => 'all'],
        'selectedUrgency' => ['except' => 'all'],
        'selectedStatus' => ['except' => 'all'],
    ];

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedMessages = $this->getFilteredQuery()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedMessages = [];
        }
    }

    public function filterByCategory(string $cat): void
    {
        $this->selectedCategory = $this->selectedCategory === $cat ? 'all' : $cat;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->selectedCategory = 'all';
        $this->selectedUrgency = 'all';
        $this->selectedStatus = 'all';
        $this->resetPage();
    }

    public function updateMessageStatus(int $messageId, string $status): void
    {
        $message = Message::where('business_id', $this->getBusiness()->id)->findOrFail($messageId);
        $message->update(['status' => $status]);

        $this->dispatch('toast', message: "Status updated to " . ucfirst(str_replace('_', ' ', $status)), type: 'success');
    }

    public function quickCycleStatus(int $messageId): void
    {
        $message = Message::where('business_id', $this->getBusiness()->id)->findOrFail($messageId);
        $nextStatus = match ($message->status) {
            'pending' => 'in_progress',
            'in_progress' => 'resolved',
            default => 'pending',
        };

        $message->update(['status' => $nextStatus]);
        $this->dispatch('toast', message: "Status changed to " . ucfirst(str_replace('_', ' ', $nextStatus)), type: 'success');
    }

    public function retryAi(int $messageId): void
    {
        $message = Message::where('business_id', $this->getBusiness()->id)->findOrFail($messageId);
        if ($message->source === 'voice' && $message->audio_path && !$message->body) {
            TranscribeAudioJob::dispatch($message);
            $this->dispatch('toast', message: "Audio transcription retrying in background...", type: 'info');
        } else {
            ClassifyMessageJob::dispatch($message);
            $this->dispatch('toast', message: "AI classification queued...", type: 'info');
        }
    }

    public function correctCategory(int $messageId, string $newCategory): void
    {
        $message = Message::where('business_id', $this->getBusiness()->id)->findOrFail($messageId);
        $message->update([
            'category' => $newCategory,
            'confidence' => 1.0, // Human verified
        ]);

        if ($this->activeMessage && $this->activeMessage->id === $messageId) {
            $this->activeMessage->refresh();
        }

        $this->dispatch('toast', message: "Category corrected to " . ucfirst($newCategory), type: 'success');
    }

    public function viewMessage(int $id): void
    {
        $this->selectedMessageId = $id;
        $this->activeMessage = Message::where('business_id', $this->getBusiness()->id)
            ->with('customer')
            ->find($id);
    }

    public function closeMessageModal(): void
    {
        $this->selectedMessageId = null;
        $this->activeMessage = null;
    }

    // Bulk actions
    public function bulkUpdateStatus(string $status): void
    {
        if (empty($this->selectedMessages)) {
            $this->dispatch('toast', message: "No messages selected.", type: 'warning');
            return;
        }

        Message::where('business_id', $this->getBusiness()->id)
            ->whereIn('id', $this->selectedMessages)
            ->update(['status' => $status]);

        $count = count($this->selectedMessages);
        $this->selectedMessages = [];
        $this->selectAll = false;

        $this->dispatch('toast', message: "Updated {$count} messages to " . ucfirst(str_replace('_', ' ', $status)), type: 'success');
    }

    public function bulkDelete(): void
    {
        if (empty($this->selectedMessages)) {
            $this->dispatch('toast', message: "No messages selected.", type: 'warning');
            return;
        }

        $count = count($this->selectedMessages);
        Message::where('business_id', $this->getBusiness()->id)
            ->whereIn('id', $this->selectedMessages)
            ->delete();

        $this->selectedMessages = [];
        $this->selectAll = false;

        $this->dispatch('toast', message: "Deleted {$count} messages.", type: 'info');
    }

    // Submit Paste Form
    public function submitPaste(): void
    {
        $this->validate([
            'pasteBody' => 'required|string|min:2',
            'pasteCustomerName' => 'nullable|string|max:100',
            'pasteCustomerPhone' => 'nullable|string|max:30',
        ]);

        $business = $this->getBusiness();
        $customer = null;

        if ($this->pasteCustomerName || $this->pasteCustomerPhone) {
            $customer = Customer::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $this->pasteCustomerName ?: ($this->pasteCustomerPhone ?: 'WhatsApp Guest'),
                ],
                [
                    'phone' => $this->pasteCustomerPhone,
                ]
            );
        }

        $message = Message::create([
            'business_id' => $business->id,
            'customer_id' => $customer?->id,
            'body' => trim($this->pasteBody),
            'source' => 'paste',
            'category' => 'unclassified',
            'urgency' => 'medium',
            'status' => 'pending',
            'ai_status' => 'pending',
            'message_timestamp' => now(),
        ]);

        // Dispatch background classification job
        ClassifyMessageJob::dispatch($message);

        $this->reset(['pasteBody', 'pasteCustomerName', 'pasteCustomerPhone', 'showNewMessageModal']);
        $this->dispatch('toast', message: "Message added & queued for AI triage!", type: 'success');
    }

    // Submit WhatsApp Export Import
    public function submitExport(): void
    {
        $this->validate([
            'exportFile' => 'required|file|max:10240', // 10MB max
            'exportOwnerName' => 'nullable|string|max:100',
        ]);

        $content = file_get_contents($this->exportFile->getRealPath());
        $parser = new WhatsAppExportParser();
        $result = $parser->parseAndImport($content, $this->getBusiness(), $this->exportOwnerName ?: null);

        // Queue classification for imported pending messages
        $pendingMessages = Message::where('business_id', $this->getBusiness()->id)
            ->where('source', 'whatsapp_export')
            ->where('ai_status', 'pending')
            ->take(50)
            ->get();

        foreach ($pendingMessages as $msg) {
            ClassifyMessageJob::dispatch($msg);
        }

        $this->reset(['exportFile', 'exportOwnerName', 'showNewMessageModal']);
        $this->dispatch('toast', message: "Imported {$result['messages_created']} messages & {$result['customers_created']} customers!", type: 'success');
    }

    // Submit Voice Note Upload
    public function submitVoice(): void
    {
        $this->validate([
            'voiceFile' => 'required|file|mimes:ogg,opus,mp3,m4a,wav,aac,oga|max:25600', // 25MB max
            'voiceCustomerName' => 'nullable|string|max:100',
            'voiceCustomerPhone' => 'nullable|string|max:30',
        ]);

        $path = $this->voiceFile->store('voice_notes', 'public');
        $business = $this->getBusiness();

        $customer = null;
        if ($this->voiceCustomerName || $this->voiceCustomerPhone) {
            $customer = Customer::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $this->voiceCustomerName ?: 'Voice Caller',
                ],
                [
                    'phone' => $this->voiceCustomerPhone,
                ]
            );
        }

        $message = Message::create([
            'business_id' => $business->id,
            'customer_id' => $customer?->id,
            'body' => '[Audio Voice Note uploaded - transcribing...]',
            'source' => 'voice',
            'category' => 'unclassified',
            'urgency' => 'medium',
            'status' => 'pending',
            'audio_path' => $path,
            'ai_status' => 'pending',
            'message_timestamp' => now(),
        ]);

        // Dispatch transcription job
        TranscribeAudioJob::dispatch($message);

        $this->reset(['voiceFile', 'voiceCustomerName', 'voiceCustomerPhone', 'showNewMessageModal']);
        $this->dispatch('toast', message: "Voice note uploaded & transcription queued!", type: 'success');
    }

    protected function getBusiness(): Business
    {
        return auth()->user()->currentBusiness();
    }

    protected function getFilteredQuery()
    {
        $business = $this->getBusiness();

        return Message::where('business_id', $business->id)
            ->with('customer')
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('body', 'like', $term)
                        ->orWhere('summary', 'like', $term)
                        ->orWhereHas('customer', function ($cq) use ($term) {
                            $cq->where('name', 'like', $term)
                               ->orWhere('phone', 'like', $term);
                        });
                });
            })
            ->when($this->selectedCategory !== 'all', fn($q) => $q->where('category', $this->selectedCategory))
            ->when($this->selectedUrgency !== 'all', fn($q) => $q->where('urgency', $this->selectedUrgency))
            ->when($this->selectedStatus !== 'all', fn($q) => $q->where('status', $this->selectedStatus))
            ->orderBy('created_at', $this->sortOrder);
    }

    public function render()
    {
        $business = $this->getBusiness();

        // 1. Stat cards metrics
        $todayStart = Carbon::today();
        $totalToday = Message::where('business_id', $business->id)->where('created_at', '>=', $todayStart)->count();
        $totalUrgent = Message::where('business_id', $business->id)->where('urgency', 'high')->where('status', '!=', 'resolved')->count();
        $totalPending = Message::where('business_id', $business->id)->where('status', 'pending')->count();
        $totalResolved = Message::where('business_id', $business->id)->where('status', 'resolved')->count();

        // 2. Category distribution for SVG chart
        $categoriesCounts = Message::where('business_id', $business->id)
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        $allCategories = ['order', 'complaint', 'refund', 'inquiry', 'urgent', 'other'];
        $chartData = [];
        $totalMessagesCount = max(1, array_sum($categoriesCounts));
        foreach ($allCategories as $cat) {
            $cnt = $categoriesCounts[$cat] ?? 0;
            $chartData[$cat] = [
                'count' => $cnt,
                'percentage' => round(($cnt / $totalMessagesCount) * 100, 1),
            ];
        }

        // 3. Top urgent priority list (unresolved high-urgency messages)
        $urgentList = Message::where('business_id', $business->id)
            ->where('urgency', 'high')
            ->where('status', '!=', 'resolved')
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // 4. Paginated filtered messages
        $messages = $this->getFilteredQuery()->paginate(10);

        return view('livewire.dashboard', [
            'totalToday' => $totalToday,
            'totalUrgent' => $totalUrgent,
            'totalPending' => $totalPending,
            'totalResolved' => $totalResolved,
            'chartData' => $chartData,
            'urgentList' => $urgentList,
            'messages' => $messages,
            'business' => $business,
        ])->layout('layouts.app');
    }
}
