<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{receiverId}', function ($user, $receiverId) {
    // Both sender and receiver can join this private channel to talk
    return (int) $user->id === (int) $receiverId || \App\Models\User::where('id', $receiverId)->exists();
});

Broadcast::channel('online', function ($user) {
    // Any authenticated user can join the online presence channel
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});
