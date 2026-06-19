<?php

namespace App\Http\Controllers;

use App\Models\ChatPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatPreferenceController extends Controller
{
    public function index()
    {
        $prefs = ChatPreference::where('user_id', Auth::id())->get();

        return response()->json(['preferences' => $prefs]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'target_type'  => 'required|in:user,group',
            'target_id'    => 'required|integer',
            'is_pinned'    => 'sometimes|boolean',
            'is_muted'     => 'sometimes|boolean',
            'is_archived'  => 'sometimes|boolean',
            'is_favorited' => 'sometimes|boolean',
            'is_blocked'   => 'sometimes|boolean',
        ]);

        $pref = ChatPreference::updateOrCreate(
            [
                'user_id'     => Auth::id(),
                'target_type' => $request->input('target_type'),
                'target_id'   => $request->integer('target_id'),
            ],
            $request->only(['is_pinned', 'is_muted', 'is_archived', 'is_favorited', 'is_blocked'])
        );

        return response()->json(['success' => true, 'preference' => $pref]);
    }
}
