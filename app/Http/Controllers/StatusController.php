<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\StatusView;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StatusController extends Controller
{
    /**
     * Display the status view with own statuses and other users' active statuses.
     */
    public function index()
    {
        $authUser = Auth::user();

        // Get own active statuses
        $myStatuses = Status::where('user_id', $authUser->id)
            ->active()
            ->latest()
            ->get();

        // Get other users who have active statuses
        $otherUsersWithStatus = User::where('id', '!=', $authUser->id)
            ->whereHas('statuses', function ($query) {
                $query->active();
            })
            ->with(['statuses' => function ($query) {
                $query->active()->latest();
            }])
            ->get()
            ->map(function ($user) use ($authUser) {
                // Determine if all statuses of this user are read by AuthUser
                $allRead = true;
                foreach ($user->statuses as $status) {
                    $hasViewed = StatusView::where('status_id', $status->id)
                        ->where('viewer_id', $authUser->id)
                        ->exists();
                    if (!$hasViewed) {
                        $allRead = false;
                        break;
                    }
                }
                $user->all_statuses_read = $allRead;
                return $user;
            })
            ->sortBy('all_statuses_read') // Unread statuses float to the top
            ->values();

        return view('status.index', compact('myStatuses', 'otherUsersWithStatus'));
    }

    /**
     * Store a new status story (Image, Video or Text).
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:text,image,video',
            'media' => 'nullable|required_if:type,image,video|file|max:20480', // Max 20MB
            'caption' => 'nullable|string|max:255',
            'background_color' => 'nullable|string|max:10', // For text status
            'message' => 'nullable|required_if:type,text|string|max:500', // For text status
        ]);

        $type = $request->input('type');
        $mediaPath = null;
        $caption = $request->input('caption');
        $backgroundColor = $request->input('background_color');

        if ($type === 'text') {
            $caption = $request->input('message');
        } else if ($request->hasFile('media')) {
            $mediaPath = $request->file('media')->store('statuses', 'public');
        }

        Status::create([
            'user_id' => Auth::id(),
            'type' => $type,
            'media_path' => $mediaPath,
            'caption' => $caption,
            'background_color' => $backgroundColor ?? '#075E54',
            'expires_at' => now()->addHours(24),
        ]);

        return redirect()->route('status.index')->with('success', 'Status uploaded successfully! 🟢');
    }

    /**
     * Mark a specific status as viewed (AJAX).
     */
    public function markViewed(Request $request, int $statusId)
    {
        $status = Status::findOrFail($statusId);
        $viewerId = Auth::id();

        if ($status->user_id !== $viewerId) {
            StatusView::firstOrCreate([
                'status_id' => $statusId,
                'viewer_id' => $viewerId,
            ], [
                'viewed_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'view_count' => $status->viewCount()
        ]);
    }

    /**
     * Delete an active status.
     */
    public function destroy(int $id)
    {
        $status = Status::where('user_id', Auth::id())->findOrFail($id);

        if ($status->media_path) {
            Storage::disk('public')->delete($status->media_path);
        }

        $status->delete();

        return redirect()->route('status.index')->with('success', 'Status deleted successfully.');
    }
}
