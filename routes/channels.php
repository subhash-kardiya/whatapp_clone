<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{receiverId}', function ($user, $receiverId) {
    return (int) $user->id === (int) $receiverId
        || \App\Models\Message::where(function ($q) use ($user, $receiverId) {
            $q->where('sender_id', $user->id)->where('receiver_id', $receiverId);
        })->orWhere(function ($q) use ($user, $receiverId) {
            $q->where('sender_id', $receiverId)->where('receiver_id', $user->id);
        })->exists();
});

Broadcast::channel('group.{groupId}', function ($user, $groupId) {
    return \App\Models\Group::where('id', $groupId)
        ->whereHas('members', fn($q) => $q->where('user_id', $user->id))
        ->exists();
});

Broadcast::channel('online', function ($user) {
    // Any authenticated user can join the online presence channel
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});
