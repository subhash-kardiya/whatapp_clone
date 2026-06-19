<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatPreference extends Model
{
    protected $fillable = [
        'user_id',
        'target_type',
        'target_id',
        'is_pinned',
        'is_muted',
        'is_archived',
        'is_favorited',
        'is_blocked',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned'    => 'boolean',
            'is_muted'     => 'boolean',
            'is_archived'  => 'boolean',
            'is_favorited' => 'boolean',
            'is_blocked'   => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
