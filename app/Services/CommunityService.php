<?php

namespace App\Services;

use App\Models\Community;
use App\Models\User;

class CommunityService
{
    public function formatCommunity(Community $community, ?User $authUser = null): array
    {
        $community->loadMissing([
            'groups' => fn($q) => $q->withCount('members'),
            'members:id,name,avatar',
            'owner:id,name,avatar',
        ]);

        $isMember = $authUser ? $community->isMember($authUser->id) : false;

        return [
            'id'             => $community->id,
            'name'           => $community->name,
            'description'    => $community->description,
            'avatar'         => $community->avatarUrl(),
            'owner_id'       => $community->owner_id,
            'members_count'  => $community->members->count(),
            'groups_count'   => $community->groups->count(),
            'is_member'      => $isMember,
            'is_owner'       => $authUser && $community->owner_id === $authUser->id,
            'owner'          => $community->owner ? [
                'id'     => $community->owner->id,
                'name'   => $community->owner->name,
                'avatar' => $community->owner->avatarUrl(),
            ] : null,
            'members'        => $community->members->map(fn($m) => [
                'id'     => $m->id,
                'name'   => $m->name,
                'avatar' => $m->avatarUrl(),
                'role'   => $m->pivot->role,
            ])->values(),
            'groups'         => $community->groups->map(fn($g) => [
                'id'            => $g->id,
                'name'          => $g->name,
                'avatar'        => $g->avatar ?: 'https://ui-avatars.com/api/?name=' . urlencode($g->name) . '&background=00a884&color=fff&size=80',
                'members_count' => $g->members_count,
                'community_id'  => $community->id,
            ])->values(),
        ];
    }
}
