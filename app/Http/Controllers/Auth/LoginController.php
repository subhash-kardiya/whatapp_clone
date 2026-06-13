<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('chat');
        }
        return view('auth.login');
    }

    /**
     * Handle a login request — supports email or phone number.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string|min:6',
        ], [
            'login.required'    => 'Mobile number or email is required.',
            'password.required' => 'Password is required.',
            'password.min'      => 'Password must be at least 6 characters.',
        ]);

        $loginValue = $request->input('login');
        $password   = $request->input('password');

        // Determine if input is email or phone
        $fieldType = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $credentials = [
            $fieldType => $loginValue,
            'password' => $password,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            // Mark user as online
            Auth::user()->update(['is_online' => true, 'last_seen' => now()]);
            return redirect()->intended(route('chat'))
                ->with('success', 'Welcome back to WhatsApp! 🎉');
        }

        return back()->withErrors([
            'login' => 'Incorrect mobile number/email or password.',
        ])->withInput($request->only('login'));
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        // Mark offline before logout
        if (Auth::check()) {
            Auth::user()->update(['is_online' => false, 'last_seen' => now()]);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')
            ->with('success', 'Logged out successfully!');
    }
}
