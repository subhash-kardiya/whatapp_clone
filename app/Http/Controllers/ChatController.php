<?php

namespace App\Http\Controllers;

use App\Events\GroupMessageSent;
use App\Events\MessageSent;
use App\Events\MessageStatusUpdated;
use App\Events\TypingIndicator;
use App\Models\ChatPreference;
use App\Models\Message;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            ->where(function ($q) use ($authUser) {
                $q->whereHas('sentMessages', function ($q2) use ($authUser) {
                    $q2->where('receiver_id', $authUser->id);
                })->orWhereHas('receivedMessages', function ($q2) use ($authUser) {
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

            // Fix the unread count in the already-constructed collection
            $activeChatUser = $users->firstWhere('id', $activeUserId);
            if ($activeChatUser) {
                $activeChatUser->unreadCount = 0;
            }

            // Broadcast status update to sender
            broadcast(new MessageStatusUpdated(
                senderId: $activeUserId,
                receiverId: $authUser->id,
                status: 'seen'
            ));
        }

        // Load user's groups with community info
        $groups = Group::whereHas('members', function ($q) use ($authUser) {
            $q->where('user_id', $authUser->id);
        })->with(['members', 'admin', 'lastMessage', 'communities'])->get()
            ->map(function ($group) use ($authUser) {
                $lastMsg = $group->lastMessage;
                $group->last_message_preview = $lastMsg ? ($lastMsg->type === 'text' ? $lastMsg->message : '📎 File') : '';
                $group->last_message_time = $lastMsg ? $lastMsg->timeFormatted() : '';
                $group->last_message_sender_self = $lastMsg && $lastMsg->sender_id === $authUser->id;
                $group->member_ids = $group->members->pluck('id')->toArray();
                $group->community_id = $group->communities->first()?->id;
                return $group;
            });

        // Track the latest message ID to initialize polling offset
        $maxMessageId = Message::max('id') ?? 0;

        $preferences = ChatPreference::where('user_id', $authUser->id)->get();

        $chatInit = $this->buildChatInitData(
            $users,
            $groups,
            $preferences,
            $authUser->id,
            $activeUser?->id,
            $maxMessageId,
            $request->query('panel', 'chats')
        );

        return view('chat.index', compact('users', 'activeUser', 'messages', 'groups', 'maxMessageId', 'preferences', 'chatInit'));
    }

    private function buildChatInitData($users, $groups, $preferences, int $authUserId, ?int $activeUserId, int $maxMessageId, string $initialPanel = 'chats'): array
    {
        $allowedPanels = ['chats', 'channels', 'communities', 'settings', 'profile'];
        if (!in_array($initialPanel, $allowedPanels, true)) {
            $initialPanel = 'chats';
        }

        $prefMap = $preferences->keyBy(fn($p) => $p->target_type . '_' . $p->target_id);
        $previews = [];

        foreach ($users as $user) {
            $up = $prefMap->get('user_' . $user->id);
            $lastMsg = $user->lastMessage;

            $previews[] = [
                'id'                       => $user->id,
                'name'                     => $user->name,
                'avatar'                   => $user->avatarUrl(),
                'email'                    => $user->email,
                'phone'                    => $user->phone ?? '',
                'about'                    => $user->about ?? '',
                'is_online'                => (bool) $user->is_online,
                'status_text'              => $user->lastSeenText(),
                'unreadCount'              => (int) $user->unreadCount,
                'is_typing'                => false,
                'last_message_preview'     => $lastMsg ? Str::limit($lastMsg->message, 30) : '',
                'last_message_time'        => $lastMsg ? $lastMsg->timeFormatted() : '',
                'last_message_sender_self' => $lastMsg && $lastMsg->sender_id === $authUserId,
                'last_message_tick'        => $lastMsg ? $lastMsg->tickHtml() : '',
                'last_message_timestamp'   => $lastMsg ? $lastMsg->created_at->timestamp : 0,
                'is_archived'              => (bool) ($up?->is_archived),
                'is_muted'                 => (bool) ($up?->is_muted),
                'is_pinned'                => (bool) ($up?->is_pinned),
                'is_favorited'             => (bool) ($up?->is_favorited),
                'is_blocked'               => (bool) ($up?->is_blocked),
                'is_group'                 => false,
                'community_id'             => null,
            ];
        }

        foreach ($groups as $group) {
            $gp = $prefMap->get('group_' . $group->id);
            $lastMsg = $group->lastMessage;

            $previews[] = [
                'id'                       => 'group_' . $group->id,
                'name'                     => $group->name,
                'avatar'                   => $group->avatar ?: 'https://ui-avatars.com/api/?name=' . urlencode($group->name) . '&background=00a884&color=fff&size=80',
                'email'                    => '',
                'phone'                    => '',
                'about'                    => 'Group · ' . ($group->description ?? ''),
                'is_online'                => false,
                'status_text'              => $group->members->count() . ' members',
                'unreadCount'              => 0,
                'is_typing'                => false,
                'last_message_preview'     => Str::limit($group->last_message_preview ?? '', 30),
                'last_message_time'        => $group->last_message_time ?? '',
                'last_message_sender_self' => (bool) ($group->last_message_sender_self ?? false),
                'last_message_tick'        => '',
                'last_message_timestamp'   => $lastMsg ? $lastMsg->created_at->timestamp : 0,
                'is_archived'              => (bool) ($gp?->is_archived),
                'is_muted'                 => (bool) ($gp?->is_muted),
                'is_pinned'                => (bool) ($gp?->is_pinned),
                'is_favorited'             => (bool) ($gp?->is_favorited),
                'is_blocked'               => false,
                'is_group'                 => true,
                'group_db_id'              => $group->id,
                'admin_id'                 => $group->admin_id,
                'members'                  => $group->member_ids,
                'group_description'        => $group->description ?? '',
                'community_id'             => $group->community_id,
            ];
        }

        return [
            'currentUserId'    => $authUserId,
            'pollGlobalLastId' => $maxMessageId,
            'activeUserId'     => $activeUserId,
            'initialPanel'     => $initialPanel,
            'myUserName'       => Auth::user()->name,
            'myUserAbout'      => Auth::user()->about ?? 'Hey there! I am using WhatsApp.',
            'chatPreviews'     => $previews,
        ];
    }

    /**
     * Send a new message (AJAX / Form POST).
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id'         => 'required|exists:users,id',
            'message'             => 'nullable|string|max:5000',
            'file'                => 'nullable|file|max:20480',
            'reply_to_id'         => 'nullable|exists:messages,id',
            'forward_message_id'  => 'nullable|exists:messages,id',
            'type'                => 'nullable|string|in:text,image,video,audio,file',
        ]);

        $authUser   = Auth::user();
        $receiverId = $request->integer('receiver_id');
        $type       = $request->input('type', 'text');
        $filePath   = null;
        $fileName   = null;
        $fileSize   = null;

        if ($request->filled('forward_message_id')) {
            $original = Message::find($request->input('forward_message_id'));
            if ($original && $original->file_path) {
                $type     = $original->type;
                $filePath = $original->file_path;
                $fileName = $original->file_name;
                $fileSize = $original->file_size;
            }
        }

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

        if ($type === 'text' && $filePath) {
            $type = 'file';
        }

        $message = Message::create([
            'sender_id'   => $authUser->id,
            'receiver_id' => $receiverId,
            'reply_to_id' => $request->input('reply_to_id'),
            'message'     => $request->input('message'),
            'type'        => $type,
            'status'      => 'sent',
            'file_path'   => $filePath,
            'file_name'   => $fileName,
            'file_size'   => $fileSize,
        ]);

        $message->load('sender', 'receiver', 'replyTo.sender');

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
            'message'   => $this->formatMessageResponse($message),
        ]);
    }

    private function formatMessageResponse(Message $message): array
    {
        $reply = null;
        if ($message->replyTo) {
            $reply = [
                'id'          => $message->replyTo->id,
                'message'     => $message->replyTo->message,
                'sender_name' => $message->replyTo->sender?->name,
                'type'        => $message->replyTo->type,
            ];
        }

        $data = [
            'id'          => $message->id,
            'sender_id'   => $message->sender_id,
            'message'     => $message->message,
            'type'        => $message->type,
            'status'      => $message->status,
            'file_url'    => $message->fileUrl(),
            'file_name'   => $message->file_name,
            'time'        => $message->timeFormatted(),
            'tick_html'   => $message->tickHtml(),
            'reply_to'    => $reply,
            'edited_at'   => $message->edited_at?->toISOString(),
        ];

        if ($message->relationLoaded('sender') && $message->sender) {
            $data['sender_name'] = $message->sender->name;
        }

        return $data;
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
     * Load new messages for a group after a given ID (polling fallback).
     */
    public function loadNewGroupMessages(int $groupId, int $afterId)
    {
        $authUser = Auth::user();

        $messages = Message::where('group_id', $groupId)
            ->where('id', '>', $afterId)
            ->where('sender_id', '!=', $authUser->id)
            ->with('sender')
            ->get();

        return response()->json([
            'messages' => $messages->map(fn($m) => [
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
            ]),
        ]);
    }

    /**
     * Load more (older) messages — infinite scroll up (AJAX).
     */
    public function loadMore(Request $request, int $userId)
    {
        $authUser = Auth::user();
        $before   = $request->query('before'); // message ID to paginate from

        $query = Message::conversation($authUser->id, $userId)
            ->with(['sender', 'receiver', 'replyTo.sender'])
            ->latest();

        if ($before) {
            $query->where('id', '<', $before);
        }

        $messages = $query->take(20)->get()->reverse()->values();

        return response()->json([
            'messages' => $messages->map(fn($m) => $this->formatMessageResponse($m)),
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
            ->with(['sender', 'receiver', 'replyTo.sender'])
            ->get();

        return response()->json([
            'messages' => $messages->map(fn($m) => $this->formatMessageResponse($m)),
        ]);
    }

    /**
     * Load new messages from all users after a given ID (global polling fallback).
     */
    public function loadAllNew(int $afterId)
    {
        $authUser = Auth::user();

        // Get IDs of groups the user belongs to
        $groupIds = $authUser->memberGroups->pluck('id')->toArray();

        $messages = Message::where('id', '>', $afterId)
            ->where(function ($q) use ($authUser, $groupIds) {
                // Direct messages sent to this user
                $q->where(function ($q2) use ($authUser) {
                    $q2->where('receiver_id', $authUser->id)
                        ->whereNull('group_id');
                });

                // Group messages from other members
                if (!empty($groupIds)) {
                    $q->orWhere(function ($q2) use ($authUser, $groupIds) {
                        $q2->whereIn('group_id', $groupIds)
                            ->where('sender_id', '!=', $authUser->id);
                    });
                }
            })
            ->with(['sender'])
            ->get();

        return response()->json([
            'messages' => $messages->map(fn($m) => [
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
     * Update group name.
     */
    public function updateGroupName(Request $request, int $groupId)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $group = Group::findOrFail($groupId);
        $authUser = Auth::user();

        if ($group->admin_id !== $authUser->id) {
            return response()->json(['error' => 'Only admin can rename'], 403);
        }

        $group->update(['name' => $request->input('name')]);

        Message::create([
            'sender_id' => $authUser->id,
            'receiver_id' => $authUser->id,
            'group_id' => $group->id,
            'message' => $authUser->name . ' changed the group name to "' . $group->name . '"',
            'type' => 'system',
            'status' => 'seen',
        ]);

        return response()->json(['success' => true, 'name' => $group->name]);
    }

    /**
     * Update group description.
     */
    public function updateGroupDesc(Request $request, int $groupId)
    {
        $request->validate(['description' => 'nullable|string|max:500']);
        $group = Group::findOrFail($groupId);
        $authUser = Auth::user();

        if ($group->admin_id !== $authUser->id) {
            return response()->json(['error' => 'Only admin can edit description'], 403);
        }

        $group->update(['description' => $request->input('description', '')]);

        return response()->json(['success' => true, 'description' => $group->description]);
    }

    /**
     * Remove a group member.
     */
    public function removeGroupMember(Request $request, int $groupId)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $group = Group::findOrFail($groupId);
        $authUser = Auth::user();

        if ($group->admin_id !== $authUser->id) {
            return response()->json(['error' => 'Only admin can remove members'], 403);
        }

        $group->members()->detach($request->user_id);

        $removedUser = \App\Models\User::find($request->user_id);
        Message::create([
            'sender_id' => $authUser->id,
            'receiver_id' => $authUser->id,
            'group_id' => $group->id,
            'message' => $removedUser->name . ' was removed by ' . $authUser->name,
            'type' => 'system',
            'status' => 'seen',
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Exit group.
     */
    public function exitGroup(int $groupId)
    {
        $group = Group::findOrFail($groupId);
        $authUser = Auth::user();

        $group->members()->detach($authUser->id);

        Message::create([
            'sender_id' => $authUser->id,
            'receiver_id' => $authUser->id,
            'group_id' => $group->id,
            'message' => $authUser->name . ' left the group',
            'type' => 'system',
            'status' => 'seen',
        ]);

        return response()->json(['success' => true]);
    }

    public function updateGroupAvatar(Request $request, int $groupId)
    {
        $request->validate(['avatar' => 'required|image|max:2048']);

        $group = Group::findOrFail($groupId);
        $authUser = Auth::user();

        if ($group->admin_id !== $authUser->id) {
            return response()->json(['error' => 'Only admin can change group icon'], 403);
        }

        $path = $request->file('avatar')->store('group-avatars', 'public');
        $group->update(['avatar' => asset('storage/' . $path)]);

        return response()->json(['success' => true, 'avatar' => $group->avatar]);
    }

    /**
     * Add group member.
     */
    public function addGroupMember(Request $request, int $groupId)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $group = Group::findOrFail($groupId);
        $authUser = Auth::user();

        if (!$group->members->contains($authUser->id)) {
            return response()->json(['error' => 'Not a group member'], 403);
        }

        $group->members()->syncWithoutDetaching([$request->user_id]);

        $newUser = \App\Models\User::find($request->user_id);
        Message::create([
            'sender_id' => $authUser->id,
            'receiver_id' => $authUser->id,
            'group_id' => $group->id,
            'message' => $authUser->name . ' added ' . $newUser->name,
            'type' => 'system',
            'status' => 'seen',
        ]);

        return response()->json(['success' => true]);
    }

    // -----------------------------------------------------------------------

    /**
     * Send a message to a group.
     */
    public function sendGroupMessage(Request $request)
    {
        $request->validate([
            'group_id'    => 'required|exists:groups,id',
            'message'     => 'nullable|string|max:5000',
            'file'        => 'nullable|file|max:20480',
            'reply_to_id' => 'nullable|exists:messages,id',
        ]);

        $authUser = Auth::user();
        $group = Group::findOrFail($request->integer('group_id'));

        if (!$group->members->contains($authUser->id)) {
            return response()->json(['error' => 'Not a group member'], 403);
        }

        $type     = 'text';
        $filePath = null;
        $fileName = null;
        $fileSize = null;

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

        if (!$request->input('message') && !$filePath) {
            return response()->json(['error' => 'Message or file is required'], 422);
        }

        $message = Message::create([
            'sender_id'   => $authUser->id,
            'receiver_id' => $authUser->id,
            'group_id'    => $group->id,
            'reply_to_id' => $request->input('reply_to_id'),
            'message'     => $request->input('message'),
            'type'        => $type,
            'status'      => 'sent',
            'file_path'   => $filePath,
            'file_name'   => $fileName,
            'file_size'   => $fileSize,
        ]);

        $message->load('sender', 'replyTo.sender');

        broadcast(new GroupMessageSent($message));

        $response = $this->formatMessageResponse($message);
        $response['group_id']    = $message->group_id;
        $response['sender_name'] = $message->sender->name;

        return response()->json([
            'success' => true,
            'message' => $response,
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
            ->with(['sender', 'replyTo.sender'])
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'messages' => $messages->map(fn($m) => $this->formatMessageResponse($m)),
        ]);
    }

    // -----------------------------------------------------------------------

    public function myGroups()
    {
        $authUser = Auth::user();
        $ownedGroups = Group::where('admin_id', $authUser->id)->withCount('members')->get();
        $memberGroups = $authUser->memberGroups()->withCount('members')->get();
        $allGroups = $ownedGroups->merge($memberGroups)->unique('id')->values();

        return response()->json(['groups' => $allGroups]);
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
