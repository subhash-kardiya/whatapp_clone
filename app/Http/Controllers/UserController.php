<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Show the user profile settings page.
     */
    public function profile()
    {
        return view('profile.index', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Update the user profile settings.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'about' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => 'Name is required.',
            'avatar.image' => 'Please select a valid image.',
            'avatar.max' => 'The image size must be less than 2MB.',
        ]);

        $data = [
            'name' => $request->input('name'),
            'about' => $request->input('about') ?? 'Hey there! I am using WhatsApp.',
        ];

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully! 🎉');
    }

    /**
     * Search users for a new conversation.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = User::where('id', '!=', Auth::id());

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->take(20)->get()->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatarUrl(),
                'about' => $user->about,
                'is_online' => $user->is_online,
                'last_seen' => $user->lastSeenText(),
            ];
        });

        return response()->json($users);
    }

    /**
     * Ping endpoint to update online status.
     */
    public function updateOnlineStatus(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->update([
                'is_online' => true,
                'last_seen' => now()
            ]);
        }
        return response()->json(['status' => 'online']);
    }
}
