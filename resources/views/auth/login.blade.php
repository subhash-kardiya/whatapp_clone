@extends('layouts.auth')

@section('title', 'Login — WhatsApp')
@section('subtitle', 'Log in to your account')

@section('content')
<form method="POST" action="{{ route('login.post') }}" id="loginForm">
    @csrf

    {{-- Mobile / Email Field --}}
    <div class="form-group">
        <label class="form-label" for="login">📱 Mobile Number or Email</label>
        <div class="input-wrapper">
            <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.41 2 2 0 0 1 3.6 1.21h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.81a16 16 0 0 0 6.56 6.56l.86-.86a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
            <input
                type="text"
                id="login"
                name="login"
                class="form-input @error('login') is-invalid @enderror"
                placeholder="+91 98765 43210 or email@example.com"
                value="{{ old('login') }}"
                autocomplete="username"
                autofocus
            >
        </div>
        @error('login')
            <div class="invalid-feedback">⚠️ {{ $message }}</div>
        @enderror
    </div>

    {{-- Password Field --}}
    <div class="form-group">
        <label class="form-label" for="password">🔑 Password</label>
        <div class="input-wrapper">
            <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input
                type="password"
                id="password"
                name="password"
                class="form-input @error('password') is-invalid @enderror"
                placeholder="Enter your password"
                autocomplete="current-password"
            >
            <button type="button" class="toggle-password" onclick="togglePassword('password', this)" aria-label="Show/Hide Password">
                <svg id="eye-icon-password" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
        </div>
        @error('password')
            <div class="invalid-feedback">⚠️ {{ $message }}</div>
        @enderror
    </div>

    {{-- Remember Me + Forgot Password --}}
    <div class="form-extras">
        <label class="remember-me">
            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            <span>Remember me</span>
        </label>
        <a href="{{ route('password.request') }}" class="forgot-link">
            Forgot Password? 🔐
        </a>
    </div>

    {{-- Login Button --}}
    <button type="submit" class="btn-primary" id="loginBtn">
        🚀 Login
    </button>

    {{-- Divider --}}
    <div class="divider">
        <span>OR</span>
    </div>

    {{-- Register Link --}}
    <div class="register-prompt">
        Don't have an account?
        <a href="{{ route('register') }}" class="register-link">✨ Create Account</a>
    </div>
</form>
@endsection

@section('scripts')
<script>
    // Toggle password visibility
    function togglePassword(fieldId, btn) {
        const field = document.getElementById(fieldId);
        const icon = btn.querySelector('svg');
        if (field.type === 'password') {
            field.type = 'text';
            icon.innerHTML = `
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
            `;
            btn.style.color = '#25D366';
        } else {
            field.type = 'password';
            icon.innerHTML = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
            `;
            btn.style.color = '';
        }
    }

    // Loading state on submit
    document.getElementById('loginForm').addEventListener('submit', function() {
        const btn = document.getElementById('loginBtn');
        btn.innerHTML = '<span class="spinner"></span> Logging in...';
        btn.classList.add('loading');
    });
</script>
@endsection
