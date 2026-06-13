<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return redirect()->route('chat');
        }
        return view('auth.register');
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'phone'                 => 'required|string|min:10|max:15|unique:users,phone',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required',
        ], [
            'name.required'        => 'Name is required.',
            'phone.required'       => 'Mobile number is required.',
            'phone.unique'         => 'This mobile number is already registered.',
            'phone.min'            => 'Please enter a valid mobile number.',
            'email.required'       => 'Email is required.',
            'email.email'          => 'Please enter a valid email.',
            'email.unique'         => 'This email is already registered.',
            'password.required'    => 'Password is required.',
            'password.min'         => 'Password must be at least 6 characters.',
            'password.confirmed'   => 'Password confirmation does not match.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'phone'    => $request->phone,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        // Mark user as online
        $user->update(['is_online' => true, 'last_seen' => now()]);

        return redirect()->route('chat')
            ->with('success', 'Account created successfully! Welcome to WhatsApp! 🎉');
    }
}
