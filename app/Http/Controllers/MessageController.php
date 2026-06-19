<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\StarredMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function edit(Request $request, int $messageId)
    {
        $request->validate(['message' => 'required|string|max:5000']);

        $message = Message::findOrFail($messageId);
        $authUser = Auth::user();

        if ($message->sender_id !== $authUser->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($message->created_at->diffInMinutes(now()) > 15) {
            return response()->json(['error' => 'Edit window expired (15 min)'], 422);
        }

        $message->update([
            'message'   => $request->input('message'),
            'edited_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'id'        => $message->id,
                'message'   => $message->message,
                'edited_at' => $message->edited_at->toISOString(),
            ],
        ]);
    }

    public function delete(Request $request, int $messageId)
    {
        $message = Message::findOrFail($messageId);
        $authUser = Auth::user();
        $forEveryone = $request->boolean('for_everyone');

        if ($forEveryone) {
            if ($message->sender_id !== $authUser->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            $message->update([
                'deleted_for_everyone' => true,
                'message'              => 'This message was deleted',
            ]);
        } else {
            if ($message->sender_id !== $authUser->id && $message->receiver_id !== $authUser->id) {
                if (!$message->group_id || !$authUser->memberGroups->contains($message->group_id)) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
            }
            $message->delete();
        }

        return response()->json(['success' => true]);
    }

    public function star(int $messageId)
    {
        $message = Message::findOrFail($messageId);
        $authUser = Auth::user();

        $starred = StarredMessage::where('user_id', $authUser->id)
            ->where('message_id', $messageId)
            ->first();

        if ($starred) {
            $starred->delete();
            return response()->json(['success' => true, 'starred' => false]);
        }

        StarredMessage::create([
            'user_id'    => $authUser->id,
            'message_id' => $messageId,
        ]);

        return response()->json(['success' => true, 'starred' => true]);
    }

    public function starred()
    {
        $authUser = Auth::user();

        $messages = StarredMessage::where('user_id', $authUser->id)
            ->with(['message.sender'])
            ->latest()
            ->get()
            ->map(fn($s) => [
                'id'          => $s->message->id,
                'message'     => $s->message->message,
                'type'        => $s->message->type,
                'sender_name' => $s->message->sender->name,
                'time'        => $s->message->timeFormatted(),
                'file_url'    => $s->message->fileUrl(),
            ]);

        return response()->json(['messages' => $messages]);
    }

    public function react(Request $request, int $messageId)
    {
        $request->validate(['emoji' => 'required|string|max:16']);

        $message = Message::findOrFail($messageId);
        $authUser = Auth::user();
        $emoji = $request->input('emoji');

        $existing = MessageReaction::where('message_id', $messageId)
            ->where('user_id', $authUser->id)
            ->where('emoji', $emoji)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['success' => true, 'added' => false]);
        }

        MessageReaction::where('message_id', $messageId)
            ->where('user_id', $authUser->id)
            ->delete();

        MessageReaction::create([
            'message_id' => $messageId,
            'user_id'    => $authUser->id,
            'emoji'      => $emoji,
        ]);

        return response()->json(['success' => true, 'added' => true, 'emoji' => $emoji]);
    }

    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|min:1|max:100']);

        $authUser = Auth::user();
        $query = $request->input('q');

        $messages = Message::where(function ($q) use ($authUser) {
            $q->where('sender_id', $authUser->id)
                ->orWhere('receiver_id', $authUser->id);
        })
            ->where('message', 'like', "%{$query}%")
            ->whereNull('deleted_at')
            ->with('sender')
            ->latest()
            ->take(50)
            ->get()
            ->map(fn($m) => [
                'id'          => $m->id,
                'message'     => $m->message,
                'sender_name' => $m->sender->name,
                'time'        => $m->timeFormatted(),
            ]);

        return response()->json(['messages' => $messages]);
    }
}
