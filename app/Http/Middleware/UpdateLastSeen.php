<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Only update DB once every minute to reduce queries
            if (!$user->last_seen || $user->last_seen->diffInMinutes(now()) >= 1 || !$user->is_online) {
                $user->update([
                    'is_online' => true,
                    'last_seen' => now()
                ]);
            }
        }

        return $next($request);
    }
}
