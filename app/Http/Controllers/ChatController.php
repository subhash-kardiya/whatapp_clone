<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\MessageStatusUpdated;
use App\Events\TypingIndicator;
use App\Models\Message;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    /**
     * Show main chat screen — chat list + optional active chat.
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();

        // Only users who have had at least one message with the current user
        $users = User::where('id', '!=', $authUser->id)
            ->whereHas('sentMessages', function ($q) use ($authUser) {
                $q->where('receiver_id', $authUser->id);
            })
            ->orWhere(function ($q) use ($authUser) {
                $q->where('id', '!=', $authUser->id)
                  ->whereHas('receivedMessages', function ($q2) use ($authUser) {
                      $q2->where('sender_id', $authUser->id);
                  });
            })
            ->get()
            ->map(function ($user) use ($authUser) {
                $lastMessage = Message::conversation($authUser->id, $user->id)
                    ->latest()
                    ->first();

                $user->lastMessage    = $lastMessage;
                $user->unreadCount    = Message::where('sender_id', $user->id)
                    ->where('receiver_id', $authUser->id)
                    ->where('status', '!=', 'seen')
                    ->count();
                return $user;
            })
            ->sortByDesc(fn($u) => optional($u->lastMessage)->created_at)
            ->values();

        $activeUserId = $request->query('user');
        $activeUser   = null;
        $messages     = collect();

        if ($activeUserId) {
            $activeUser = User::find($activeUserId);
            
            if (!$activeUser) {
                return redirect()->route('chat');
            }

            // Load last 30 messages
            $messages = Message::conversation($authUser->id, $activeUserId)
                ->with(['sender', 'receiver'])
                ->latest()
                ->take(30)
                ->get()
                ->reverse()
                ->values();

            // Mark received messages as delivered/seen
            Message::where('sender_id', $activeUserId)
                ->where('receiver_id', $authUser->id)
                ->where('status', '!=', 'seen')
                ->update(['status' => 'seen']);

            // Broadcast status update to sender
            broadcast(new MessageStatusUpdated(
                senderId: $activeUserId,
                receiverId: $authUser->id,
                status: 'seen'
            ));
        }

        // Load user's groups
        $groups = Group::whereHas('members', function ($q) use ($authUser) {
            $q->where('user_id', $authUser->id);
        })->with(['members', 'admin', 'lastMessage'])->get()
          ->map(function ($group) use ($authUser) {
              $lastMsg = $group->lastMessage;
              $group->last_message_preview = $lastMsg ? ($lastMsg->type === 'text' ? $lastMsg->message : '📎 File') : '';
              $group->last_message_time = $lastMsg ? $lastMsg->timeFormatted() : '';
              $group->last_message_sender_self = $lastMsg && $lastMsg->sender_id === $authUser->id;
              $group->member_ids = $group->members->pluck('id')->toArray();
              return $group;
          });

        return view('chat.index', compact('users', 'activeUser', 'messages', 'groups'));
    }

    /**
     * Send a new message (AJAX / Form POST).
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message'     => 'nullable|string|max:5000',
            'file'        => 'nullable|file|max:20480', // 20MB
        ]);

        $authUser   = Auth::user();
        $receiverId = $request->integer('receiver_id');
        $type       = 'text';
        $filePath   = null;
        $fileName   = null;
        $fileSize   = null;

        // Handle file upload
        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $mime     = $file->getMimeType();
            $fileName = $file->getClientOriginalName();
            $fileSize = $this->formatFileSize($file->getSize());

            if (str_starts_with($mime, 'image/'))      $type = 'image';
            elseif (str_starts_with($mime, 'video/'))  $type = 'video';
            elseif (str_starts_with($mime, 'audio/'))  $type = 'audio';
            else                                        $type = 'file';

            $filePath = $file->store("messages/{$authUser->id}", 'public');
        }

        // Must have message or file
        if (!$request->input('message') && !$filePath) {
            return response()->json(['error' => 'Message or file is required'], 422);
        }

        $message = Message::create([
            'sender_id'   => $authUser->id,
            'receiver_id' => $receiverId,
            'message'     => $request->input('message'),
            'type'        => $type,
            'status'      => 'sent',
            'file_path'   => $filePath,
            'file_name'   => $fileName,
            'file_size'   => $fileSize,
        ]);

        $message->load('sender', 'receiver');

        // Broadcast to receiver instantly
        broadcast(new MessageSent($message));

        // If receiver is online → delivered immediately
        $receiver = User::find($receiverId);
        if ($receiver && $receiver->is_online) {
            $message->update(['status' => 'delivered']);
            broadcast(new MessageStatusUpdated(
                senderId: $authUser->id,
                receiverId: $receiverId,
                status: 'delivered',
                messageId: $message->id
            ));
        }

        return response()->json([
            'success'   => true,
            'message'   => [
                'id'        => $message->id,
                'message'   => $message->message,
                'type'      => $message->type,
                'status'    => $message->status,
                'file_url'  => $message->fileUrl(),
                'file_name' => $message->file_name,
                'time'      => $message->timeFormatted(),
                'tick_html' => $message->tickHtml(),
            ],
        ]);
    }

    /**
     * Mark all messages from a user as seen (AJAX).
     */
    public function markSeen(Request $request, int $senderId)
    {
        $authUser = Auth::user();

        $updated = Message::where('sender_id', $senderId)
            ->where('receiver_id', $authUser->id)
            ->whereIn('status', ['sent', 'delivered'])
            ->update(['status' => 'seen']);

        if ($updated > 0) {
            broadcast(new MessageStatusUpdated(
                senderId: $senderId,
                receiverId: $authUser->id,
                status: 'seen'
            ));
        }

        return response()->json(['success' => true]);
    }

    /**
     * Load more (older) messages — infinite scroll up (AJAX).
     */
    public function loadMore(Request $request, int $userId)
    {
        $authUser = Auth::user();
        $before   = $request->query('before'); // message ID to paginate from

        $query = Message::conversation($authUser->id, $userId)
            ->with(['sender', 'receiver'])
            ->latest();

        if ($before) {
            $query->where('id', '<', $before);
        }

        $messages = $query->take(20)->get()->reverse()->values();

        return response()->json([
            'messages' => $messages->map(fn($m) => [
                'id'        => $m->id,
                'sender_id' => $m->sender_id,
                'message'   => $m->message,
                'type'      => $m->type,
                'status'    => $m->status,
                'file_url'  => $m->fileUrl(),
                'file_name' => $m->file_name,
                'time'      => $m->timeFormatted(),
                'tick_html' => $m->tickHtml(),
            ]),
            'has_more'  => $messages->count() === 20,
        ]);
    }

    /**
     * Load new messages after a given ID (polling fallback).
     */
    public function loadNew(int $userId, int $afterId)
    {
        $authUser = Auth::user();

        $messages = Message::where(function ($q) use ($authUser, $userId) {
                $q->where('sender_id', $userId)->where('receiver_id', $authUser->id);
            })
            ->where('id', '>', $afterId)
            ->with(['sender', 'receiver'])
            ->get();

        return response()->json([
            'messages' => $messages->map(fn($m) => [
                'id'          => $m->id,
                'sender_id'   => $m->sender_id,
                'message'     => $m->message,
                'type'        => $m->type,
                'status'      => $m->status,
                'file_url'    => $m->fileUrl(),
                'file_name'   => $m->file_name,
                'time'        => $m->timeFormatted(),
                'tick_html'   => $m->tickHtml(),
                'sender_name' => $m->sender->name,
            ]),
        ]);
    }

    /**
     * Load new messages from all users after a given ID (global polling fallback).
     */
    public function loadAllNew(int $afterId)
    {
        $authUser = Auth::user();

        $messages = Message::where('receiver_id', $authUser->id)
            ->where('id', '>', $afterId)
            ->where('sender_id', '!=', $authUser->id)
            ->with(['sender'])
            ->get();

        return response()->json([
            'messages' => $messages->map(fn($m) => [
                'id'          => $m->id,
                'sender_id'   => $m->sender_id,
                'message'     => $m->message,
                'type'        => $m->type,
                'status'      => $m->status,
                'file_url'    => $m->fileUrl(),
                'file_name'   => $m->file_name,
                'time'        => $m->timeFormatted(),
                'tick_html'   => $m->tickHtml(),
                'sender_name' => $m->sender->name,
            ]),
        ]);
    }

    /**
     * Broadcast typing indicator (AJAX).
     */
    public function typing(Request $request)
    {
        $request->validate(['receiver_id' => 'required|integer', 'is_typing' => 'required|boolean']);

        broadcast(new TypingIndicator(
            senderId: Auth::id(),
            receiverId: $request->integer('receiver_id'),
            isTyping: $request->boolean('is_typing')
        ));

        return response()->json(['success' => true]);
    }

    /**
     * Create a new group chat.
     */
    public function createGroup(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'members' => 'required|string',
        ]);

        $authUser = Auth::user();
        $memberIds = array_map('intval', explode(',', $request->input('members')));
        $memberIds = array_unique(array_filter($memberIds, fn($id) => $id > 0));

        $group = Group::create([
            'name'        => $request->input('name'),
            'admin_id'    => $authUser->id,
            'description' => null,
        ]);

        // Add creator + selected members
        $allMembers = array_unique(array_merge([$authUser->id], $memberIds));
        $group->members()->sync($allMembers);

        // System message: group created
        Message::create([
            'sender_id'   => $authUser->id,
            'receiver_id' => $authUser->id,
            'group_id'    => $group->id,
            'message'     => $authUser->name . ' created group "' . $group->name . '"',
            'type'        => 'system',
            'status'      => 'seen',
        ]);

        return response()->json([
            'success' => true,
            'group'   => [
                'id'          => $group->id,
                'name'        => $group->name,
                'admin_id'    => $group->admin_id,
                'avatar'      => $group->avatar,
                'description' => $group->description,
                'member_ids'  => $allMembers,
            ],
        ]);
    }

    // -----------------------------------------------------------------------

    /**
     * Send a message to a group.
     */
    public function sendGroupMessage(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'message'  => 'nullable|string|max:5000',
        ]);

        $authUser = Auth::user();
        $group = Group::findOrFail($request->integer('group_id'));

        // Check membership
        if (!$group->members->contains($authUser->id)) {
            return response()->json(['error' => 'Not a group member'], 403);
        }

        $message = Message::create([
            'sender_id'   => $authUser->id,
            'receiver_id' => $authUser->id,
            'group_id'    => $group->id,
            'message'     => $request->input('message'),
            'type'        => 'text',
            'status'      => 'sent',
        ]);

        $message->load('sender');

        return response()->json([
            'success' => true,
            'message' => [
                'id'          => $message->id,
                'sender_id'   => $message->sender_id,
                'group_id'    => $message->group_id,
                'message'     => $message->message,
                'type'        => $message->type,
                'time'        => $message->timeFormatted(),
                'tick_html'   => $message->tickHtml(),
                'sender_name' => $message->sender->name,
            ],
        ]);
    }

    /**
     * Load messages for a group.
     */
    public function loadGroupMessages(int $groupId)
    {
        $authUser = Auth::user();
        $group = Group::findOrFail($groupId);

        if (!$group->members->contains($authUser->id)) {
            return response()->json(['error' => 'Not a group member'], 403);
        }

        $messages = Message::where('group_id', $groupId)
            ->with('sender')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values()
            ->map(fn($m) => [
                'id'          => $m->id,
                'sender_id'   => $m->sender_id,
                'group_id'    => $m->group_id,
                'message'     => $m->message,
                'type'        => $m->type,
                'status'      => $m->status,
                'file_url'    => $m->fileUrl(),
                'file_name'   => $m->file_name,
                'time'        => $m->timeFormatted(),
                'tick_html'   => $m->tickHtml(),
                'sender_name' => $m->sender->name,
            ]);

        return response()->json(['messages' => $messages]);
    }

    // -----------------------------------------------------------------------

    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
