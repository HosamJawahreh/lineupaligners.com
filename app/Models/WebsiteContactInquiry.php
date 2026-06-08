<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteContactInquiry extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'form_type',
        'locale',
        'ip_address',
        'read_at',
        'read_by',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function readByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function formTypeLabel(): string
    {
        return match ($this->form_type) {
            'appointment' => 'Appointment request',
            'newsletter' => 'Newsletter signup',
            default => 'Contact form',
        };
    }

    public function markRead(?int $userId = null): void
    {
        if ($this->isRead()) {
            return;
        }

        $this->update([
            'read_at' => now(),
            'read_by' => $userId,
        ]);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeContactRequests($query)
    {
        return $query->whereIn('form_type', ['contact', 'appointment']);
    }
}
