<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'customer_id',
        'body',
        'source',
        'category',
        'urgency',
        'summary',
        'confidence',
        'status',
        'audio_path',
        'detected_language',
        'ai_status',
        'ai_error',
        'message_timestamp',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'float',
            'message_timestamp' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // Scopes
    public function scopeUrgent(Builder $query): Builder
    {
        return $query->where('urgency', 'high');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', 'resolved');
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    // Direction and script detection
    public function isUrdu(): bool
    {
        return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $this->body);
    }

    public function direction(): string
    {
        return $this->isUrdu() ? 'rtl' : 'ltr';
    }

    // Badge styling helpers
    public function getCategoryColorAttribute(): string
    {
        return match ($this->category) {
            'order' => 'emerald',
            'complaint' => 'rose',
            'refund' => 'amber',
            'urgent' => 'red',
            'inquiry' => 'blue',
            default => 'slate',
        };
    }

    public function getUrgencyColorAttribute(): string
    {
        return match ($this->urgency) {
            'high' => 'red',
            'medium' => 'amber',
            default => 'emerald',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'resolved' => 'emerald',
            'in_progress' => 'blue',
            default => 'amber',
        };
    }
}
