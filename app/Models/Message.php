<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'type',
        'status',
        'file_path',
        'file_name',
        'file_size',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    /**
     * Get conversation between two users.
     */
    public function scopeConversation(Builder $query, int $userA, int $userB): Builder
    {
        return $query->where(function ($q) use ($userA, $userB) {
            $q->where('sender_id', $userA)->where('receiver_id', $userB);
        })->orWhere(function ($q) use ($userA, $userB) {
            $q->where('sender_id', $userB)->where('receiver_id', $userA);
        });
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Get the file URL if this is a file/image/video message.
     */
    public function fileUrl(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    /**
     * Tick HTML for message status.
     */
    public function tickHtml(): string
    {
        return match ($this->status) {
            'sent'      => '<span class="tick tick-sent" title="Sent">✔</span>',
            'delivered' => '<span class="tick tick-delivered" title="Delivered">✔✔</span>',
            'seen'      => '<span class="tick tick-seen" title="Seen">✔✔</span>',
            default     => '',
        };
    }

    /**
     * Short formatted time for chat list.
     */
    public function timeFormatted(): string
    {
        if ($this->created_at->isToday()) {
            return $this->created_at->format('h:i A');
        }
        if ($this->created_at->isYesterday()) {
            return 'Yesterday';
        }
        return $this->created_at->format('d/m/Y');
    }
}
