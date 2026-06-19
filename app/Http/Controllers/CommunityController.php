<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Group;
use App\Models\Message;
use App\Services\CommunityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunityController extends Controller
{
    public function __construct(private CommunityService $communityService) {}

    public function index()
    {
        $authUser = Auth::user();

        $ownedCommunities = Community::where('owner_id', $authUser->id)
            ->with(['groups' => fn($q) => $q->withCount('members'), 'members:id,name,avatar'])
            ->get()
            ->map(fn($c) => $this->communityService->formatCommunity($c, $authUser));

        $memberCommunities = $authUser->memberCommunities()
            ->where('owner_id', '!=', $authUser->id)
            ->with(['groups' => fn($q) => $q->withCount('members'), 'members:id,name,avatar', 'owner:id,name,avatar'])
            ->get()
            ->map(fn($c) => $this->communityService->formatCommunity($c, $authUser));

        return response()->json([
            'owned'  => $ownedCommunities,
            'member' => $memberCommunities,
        ]);
    }

    public function show(int $communityId)
    {
        $community = Community::findOrFail($communityId);
        $authUser  = Auth::user();

        if (!$community->isMember($authUser->id)) {
            return response()->json(['error' => 'Not a member'], 403);
        }

        return response()->json([
            'community' => $this->communityService->formatCommunity($community, $authUser),
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'avatar'      => 'nullable|image|max:2048',
        ]);

        $authUser = Auth::user();

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('community-avatars', 'public');
        }

        $community = Community::create([
            'name'        => $request->name,
            'description' => $request->description ?? '',
            'owner_id'    => $authUser->id,
            'avatar'      => $avatarPath,
        ]);

        $community->members()->attach($authUser->id, ['role' => 'admin']);

        $community->load(['groups' => fn($q) => $q->withCount('members'), 'members:id,name,avatar', 'owner:id,name,avatar']);

        return response()->json([
            'success'   => true,
            'community' => $this->communityService->formatCommunity($community, $authUser),
        ]);
    }

    public function addGroup(Request $request, int $communityId)
    {
        $community = Community::findOrFail($communityId);
        $authUser  = Auth::user();

        if ($community->owner_id !== $authUser->id) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        $request->validate(['group_id' => 'required|exists:groups,id']);

        $group = Group::withCount('members')->with('members')->findOrFail($request->group_id);
        if (!$group->members->contains($authUser->id)) {
            return response()->json(['error' => 'Not a member of this group'], 403);
        }

        if ($community->groups()->where('group_id', $group->id)->exists()) {
            return response()->json(['error' => 'Group already in community'], 400);
        }

        $community->groups()->attach($group->id);

        return response()->json([
            'success' => true,
            'group'   => [
                'id'            => $group->id,
                'name'          => $group->name,
                'avatar'        => $group->avatar ?: 'https://ui-avatars.com/api/?name=' . urlencode($group->name) . '&background=00a884&color=fff&size=80',
                'members_count' => $group->members_count,
                'community_id'  => $community->id,
            ],
        ]);
    }

    public function removeGroup(Request $request, int $communityId)
    {
        $community = Community::findOrFail($communityId);
        $authUser  = Auth::user();

        if ($community->owner_id !== $authUser->id) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        $request->validate(['group_id' => 'required|exists:groups,id']);
        $community->groups()->detach($request->group_id);

        return response()->json(['success' => true]);
    }

    public function addMember(Request $request, int $communityId)
    {
        $community = Community::findOrFail($communityId);
        $authUser  = Auth::user();

        if ($community->owner_id !== $authUser->id) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        $request->validate(['user_id' => 'required|exists:users,id']);

        $community->members()->syncWithoutDetaching([
            $request->user_id => ['role' => 'member'],
        ]);

        return response()->json(['success' => true]);
    }

    public function removeMember(Request $request, int $communityId)
    {
        $community = Community::findOrFail($communityId);
        $authUser  = Auth::user();

        if ($community->owner_id !== $authUser->id) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        $request->validate(['user_id' => 'required|exists:users,id']);
        $community->members()->detach($request->user_id);

        return response()->json(['success' => true]);
    }

    public function leave(int $communityId)
    {
        $community = Community::findOrFail($communityId);
        $authUser  = Auth::user();

        if ($community->owner_id === $authUser->id) {
            return response()->json(['error' => 'Owner cannot leave. Delete community instead.'], 400);
        }

        $community->members()->detach($authUser->id);

        return response()->json(['success' => true]);
    }

    public function delete(int $communityId)
    {
        $community = Community::findOrFail($communityId);
        $authUser  = Auth::user();

        if ($community->owner_id !== $authUser->id) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        $community->delete();

        return response()->json(['success' => true]);
    }

    public function join(int $communityId)
    {
        $community = Community::findOrFail($communityId);
        $authUser  = Auth::user();

        if ($community->isMember($authUser->id)) {
            return response()->json(['error' => 'Already a member'], 400);
        }

        $community->members()->attach($authUser->id, ['role' => 'member']);

        return response()->json([
            'success'   => true,
            'community' => $this->communityService->formatCommunity($community->fresh(), $authUser),
        ]);
    }

    public function announce(Request $request, int $communityId)
    {
        $community = Community::findOrFail($communityId);
        $authUser  = Auth::user();

        if ($community->owner_id !== $authUser->id) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        $request->validate(['message' => 'required|string|max:5000']);

        $message = Message::create([
            'sender_id'    => $authUser->id,
            'receiver_id'  => $authUser->id,
            'community_id' => $community->id,
            'message'      => $request->message,
            'type'         => 'text',
            'status'       => 'sent',
        ]);

        $message->load('sender');

        return response()->json([
            'success'      => true,
            'announcement' => [
                'id'          => $message->id,
                'message'     => $message->message,
                'sender_name' => $message->sender->name,
                'time'        => $message->timeFormatted(),
                'created_at'  => $message->created_at->toISOString(),
            ],
        ]);
    }

    public function announcements(int $communityId)
    {
        $community = Community::findOrFail($communityId);
        $authUser  = Auth::user();

        if (!$community->isMember($authUser->id)) {
            return response()->json(['error' => 'Not a member'], 403);
        }

        $messages = Message::where('community_id', $communityId)
            ->with('sender')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values()
            ->map(fn($m) => [
                'id'          => $m->id,
                'message'     => $m->message,
                'sender_name' => $m->sender->name,
                'time'        => $m->timeFormatted(),
                'created_at'  => $m->created_at->toISOString(),
            ]);

        return response()->json(['announcements' => $messages]);
    }

    public function update(Request $request, int $communityId)
    {
        $community = Community::findOrFail($communityId);
        $authUser  = Auth::user();

        if ($community->owner_id !== $authUser->id) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        $request->validate([
            'name'        => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'avatar'      => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['name', 'description']);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('community-avatars', 'public');
        }

        $community->update(array_filter($data, fn($v) => $v !== null));

        return response()->json([
            'success'   => true,
            'community' => $this->communityService->formatCommunity($community->fresh(), $authUser),
        ]);
    }
}
