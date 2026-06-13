@extends('layouts.auth')

@section('title', 'Forgot Password — WhatsApp')
@section('subtitle', 'Get password reset link')

@section('content')
<form method="POST" action="{{ route('password.email') }}" id="forgotForm">
    @csrf

    <div style="text-align: center; margin-bottom: 24px;">
        <div style="
            width: 64px; height: 64px;
            background: rgba(37, 211, 102, 0.15);
            border: 2px solid rgba(37, 211, 102, 0.3);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        ">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </div>
        <p style="color: rgba(255,255,255,0.75); font-size: 14px; line-height: 1.6;">
            Enter your email address.<br>
            We will send you a password reset link. 📧
        </p>
    </div>

    {{-- Email Field --}}
    <div class="form-group">
        <label class="form-label" for="email">📧 Email Address</label>
        <div class="input-wrapper">
            <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
            <input
                type="email"
                id="email"
                name="email"
                class="form-input @error('email') is-invalid @enderror"
                placeholder="Enter your registered email"
                value="{{ old('email') }}"
                autocomplete="email"
                autofocus
            >
        </div>
        @error('email')
            <div class="invalid-feedback">⚠️ {{ $message }}</div>
        @enderror
    </div>

    {{-- Submit Button --}}
    <button type="submit" class="btn-primary" id="forgotBtn">
        📨 Send Reset Link
    </button>

    {{-- Back to Login --}}
    <div class="divider">
        <span>OR</span>
    </div>

    <div class="register-prompt">
        <a href="{{ route('login') }}" class="register-link">← Go to Login Page</a>
    </div>
</form>
@endsection

@section('scripts')
<script>
    document.getElementById('forgotForm').addEventListener('submit', function() {
        const btn = document.getElementById('forgotBtn');
        btn.innerHTML = '<span class="spinner"></span> Sending...';
        btn.classList.add('loading');
    });
</script>
@endsection
