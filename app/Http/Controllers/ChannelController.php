<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChannelController extends Controller
{
    public function index()
    {
        $authUser = Auth::user();

        $ownedChannels = Channel::where('owner_id', $authUser->id)
            ->withCount('subscribers')
            ->with(['messages' => function ($q) {
                $q->latest()->take(1);
            }])
            ->get()
            ->map(function ($ch) {
                $lastMsg = $ch->messages->first();
                $ch->last_message = $lastMsg ? $ch->messages->first() : null;
                return $ch;
            });

        $subscribedChannels = $authUser->subscribedChannels()
            ->withCount('subscribers')
            ->with(['messages' => function ($q) {
                $q->latest()->take(1);
            }])
            ->get()
            ->map(function ($ch) {
                $lastMsg = $ch->messages->first();
                $ch->last_message = $lastMsg ? $ch->messages->first() : null;
                return $ch;
            });

        return response()->json([
            'owned' => $ownedChannels,
            'subscribed' => $subscribedChannels,
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $authUser = Auth::user();

        $channel = Channel::create([
            'name' => $request->name,
            'description' => $request->description ?? '',
            'owner_id' => $authUser->id,
            'subscribers_count' => 1,
        ]);

        $channel->subscribers()->attach($authUser->id);

        return response()->json([
            'success' => true,
            'channel' => [
                'id' => $channel->id,
                'name' => $channel->name,
                'description' => $channel->description,
                'owner_id' => $channel->owner_id,
                'subscribers_count' => 1,
                'avatar' => $channel->avatar,
            ],
        ]);
    }

    public function subscribe(Request $request, int $channelId)
    {
        $channel = Channel::findOrFail($channelId);
        $authUser = Auth::user();

        if ($channel->subscribers->contains($authUser->id)) {
            $channel->subscribers()->detach($authUser->id);
            $channel->decrement('subscribers_count');
            return response()->json(['success' => true, 'subscribed' => false]);
        }

        $channel->subscribers()->attach($authUser->id);
        $channel->increment('subscribers_count');
        return response()->json(['success' => true, 'subscribed' => true]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'channel_id' => 'required|exists:channels,id',
            'message' => 'nullable|string|max:5000',
        ]);

        $authUser = Auth::user();
        $channel = Channel::findOrFail($request->channel_id);

        if (!$channel->subscribers->contains($authUser->id) && $channel->owner_id !== $authUser->id) {
            return response()->json(['error' => 'Not a subscriber'], 403);
        }

        $message = Message::create([
            'sender_id' => $authUser->id,
            'receiver_id' => $authUser->id,
            'channel_id' => $channel->id,
            'message' => $request->input('message'),
            'type' => 'text',
            'status' => 'seen',
        ]);

        $message->load('sender');

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'channel_id' => $message->channel_id,
                'message' => $message->message,
                'type' => $message->type,
                'time' => $message->timeFormatted(),
                'sender_name' => $message->sender->name,
                'sender_avatar' => $message->sender->avatarUrl(),
            ],
        ]);
    }

    public function loadMessages(int $channelId)
    {
        $authUser = Auth::user();
        $channel = Channel::findOrFail($channelId);

        if (!$channel->subscribers->contains($authUser->id) && $channel->owner_id !== $authUser->id) {
            return response()->json(['error' => 'Not a subscriber'], 403);
        }

        $messages = Message::where('channel_id', $channelId)
            ->with('sender')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values()
            ->map(fn($m) => [
                'id' => $m->id,
                'sender_id' => $m->sender_id,
                'channel_id' => $m->channel_id,
                'message' => $m->message,
                'type' => $m->type,
                'file_url' => $m->fileUrl(),
                'file_name' => $m->file_name,
                'time' => $m->timeFormatted(),
                'sender_name' => $m->sender->name,
                'sender_avatar' => $m->sender->avatarUrl(),
            ]);

        return response()->json(['messages' => $messages]);
    }

    public function discover()
    {
        $authUser = Auth::user();
        $subscribedIds = $authUser->subscribedChannels()->pluck('channels.id');

        $channels = Channel::whereNotIn('id', $subscribedIds)
            ->withCount('subscribers')
            ->orderByDesc('subscribers_count')
            ->take(20)
            ->get();

        return response()->json(['channels' => $channels]);
    }

    public function update(Request $request, int $channelId)
    {
        $channel = Channel::findOrFail($channelId);
        $authUser = Auth::user();

        if ($channel->owner_id !== $authUser->id) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['name', 'description']);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('channel-avatars', 'public');
        }

        $channel->update(array_filter($data, fn($v) => $v !== null));

        return response()->json([
            'success' => true,
            'channel' => [
                'id' => $channel->id,
                'name' => $channel->name,
                'description' => $channel->description,
                'avatar' => $channel->avatar ? asset('storage/' . $channel->avatar) : null,
                'owner_id' => $channel->owner_id,
                'subscribers_count' => $channel->subscribers_count,
            ],
        ]);
    }

    public function delete(int $channelId)
    {
        $channel = Channel::findOrFail($channelId);
        $authUser = Auth::user();

        if ($channel->owner_id !== $authUser->id) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        $channel->delete();

        return response()->json(['success' => true]);
    }
}
