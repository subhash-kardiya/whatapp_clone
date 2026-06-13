<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle the forgot password request.
     * (Placeholder - shows success message for now)
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Email is required.',
            'email.email'    => 'Please enter a valid email.',
            'email.exists'   => 'This email is not registered.',
        ]);

        // In production: Password::sendResetLink($request->only('email'))
        // For now, log it and show success message
        \Log::info('Password reset requested for: ' . $request->email);

        return back()->with('status', 'Password reset link has been sent to your email! 📧');
    }
}
