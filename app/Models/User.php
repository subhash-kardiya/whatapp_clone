<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'about',
        'is_online',
        'last_seen',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'last_seen'         => 'datetime',
            'is_online'         => 'boolean',
        ];
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(Status::class);
    }

    public function ownedGroups()
    {
        return $this->hasMany(Group::class, 'admin_id');
    }

    public function memberGroups()
    {
        return $this->belongsToMany(Group::class, 'group_members')->withTimestamps();
    }

    public function ownedChannels()
    {
        return $this->hasMany(Channel::class, 'owner_id');
    }

    public function subscribedChannels()
    {
        return $this->belongsToMany(Channel::class, 'channel_subscribers')->withTimestamps();
    }

    public function ownedCommunities()
    {
        return $this->hasMany(Community::class, 'owner_id');
    }

    public function memberCommunities()
    {
        return $this->belongsToMany(Community::class, 'community_members')->withPivot('role')->withTimestamps();
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Get avatar URL or default initials avatar.
     */
    public function avatarUrl(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=25D366&color=fff&size=128';
    }

    /**
     * Human-readable last seen.
     */
    public function lastSeenText(): string
    {
        if ($this->is_online) {
            return 'online';
        }
        if (!$this->last_seen) {
            return 'last seen recently';
        }
        $diff = now()->diffInMinutes($this->last_seen);
        if ($diff < 1)  return 'last seen just now';
        if ($diff < 60) return 'last seen ' . $diff . 'm ago';
        if ($diff < 1440) return 'last seen ' . now()->diffInHours($this->last_seen) . 'h ago';
        return 'last seen ' . $this->last_seen->format('d M');
    }
}
