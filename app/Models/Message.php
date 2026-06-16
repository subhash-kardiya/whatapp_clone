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
        'group_id',
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

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
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
        $singleCheck = '<svg class="tick-svg tick-sent" viewBox="0 0 16 11" width="16" height="11"><path d="M11.071.653a.457.457 0 0 0-.304-.102-.493.493 0 0 0-.381.178l-6.19 7.636-2.011-2.095a.463.463 0 0 0-.353-.145.47.47 0 0 0-.335.136.474.474 0 0 0-.016.678l2.375 2.459a.447.447 0 0 0 .347.156h.014a.472.472 0 0 0 .352-.176l6.544-8.058a.448.448 0 0 0-.042-.665z" fill="currentColor"/></svg>';
        $doubleCheck = '<svg class="tick-svg tick-delivered" viewBox="0 0 16 11" width="16" height="11"><path d="M11.071.653a.457.457 0 0 0-.304-.102-.493.493 0 0 0-.381.178l-6.19 7.636-2.011-2.095a.463.463 0 0 0-.353-.145.47.47 0 0 0-.335.136.474.474 0 0 0-.016.678l2.375 2.459a.447.447 0 0 0 .347.156h.014a.472.472 0 0 0 .352-.176l6.544-8.058a.448.448 0 0 0-.042-.665z" fill="currentColor"/><path d="M15.071.653a.457.457 0 0 0-.304-.102-.493.493 0 0 0-.381.178l-6.19 7.636-1.2-1.251-.352.435 1.202 1.258a.447.447 0 0 0 .347.156h.014a.472.472 0 0 0 .352-.176l6.544-8.058a.448.448 0 0 0-.042-.665l-.088-.412z" fill="currentColor"/>';
        $doubleCheckBlue = '<svg class="tick-svg tick-seen" viewBox="0 0 16 11" width="16" height="11"><path d="M11.071.653a.457.457 0 0 0-.304-.102-.493.493 0 0 0-.381.178l-6.19 7.636-2.011-2.095a.463.463 0 0 0-.353-.145.47.47 0 0 0-.335.136.474.474 0 0 0-.016.678l2.375 2.459a.447.447 0 0 0 .347.156h.014a.472.472 0 0 0 .352-.176l6.544-8.058a.448.448 0 0 0-.042-.665z" fill="currentColor"/><path d="M15.071.653a.457.457 0 0 0-.304-.102-.493.493 0 0 0-.381.178l-6.19 7.636-1.2-1.251-.352.435 1.202 1.258a.447.447 0 0 0 .347.156h.014a.472.472 0 0 0 .352-.176l6.544-8.058a.448.448 0 0 0-.042-.665l-.088-.412z" fill="currentColor"/></svg>';

        return match ($this->status) {
            'sent'      => $singleCheck,
            'delivered' => $doubleCheck,
            'seen'      => $doubleCheckBlue,
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
