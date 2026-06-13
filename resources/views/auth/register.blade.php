@extends('layouts.auth')

@section('title', 'Register — WhatsApp')
@section('subtitle', 'Create a new account')

@section('content')
<form method="POST" action="{{ route('register.post') }}" id="registerForm">
    @csrf

    {{-- Full Name --}}
    <div class="form-group">
        <label class="form-label" for="name">👤 Full Name</label>
        <div class="input-wrapper">
            <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            <input
                type="text"
                id="name"
                name="name"
                class="form-input @error('name') is-invalid @enderror"
                placeholder="Your full name"
                value="{{ old('name') }}"
                autocomplete="name"
                autofocus
            >
        </div>
        @error('name')
            <div class="invalid-feedback">⚠️ {{ $message }}</div>
        @enderror
    </div>

    {{-- Phone Number --}}
    <div class="form-group">
        <label class="form-label" for="phone">📱 Mobile Number</label>
        <div class="input-wrapper">
            <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.41 2 2 0 0 1 3.6 1.21h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.81a16 16 0 0 0 6.56 6.56l.86-.86a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
            <input
                type="tel"
                id="phone"
                name="phone"
                class="form-input @error('phone') is-invalid @enderror"
                placeholder="+91 98765 43210"
                value="{{ old('phone') }}"
                autocomplete="tel"
            >
        </div>
        @error('phone')
            <div class="invalid-feedback">⚠️ {{ $message }}</div>
        @enderror
    </div>

    {{-- Email --}}
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
                placeholder="email@example.com"
                value="{{ old('email') }}"
                autocomplete="email"
            >
        </div>
        @error('email')
            <div class="invalid-feedback">⚠️ {{ $message }}</div>
        @enderror
    </div>

    {{-- Password --}}
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
                placeholder="Minimum 6 characters"
                autocomplete="new-password"
            >
            <button type="button" class="toggle-password" onclick="togglePassword('password', this)" aria-label="Show/Hide Password">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
        </div>
        @error('password')
            <div class="invalid-feedback">⚠️ {{ $message }}</div>
        @enderror
    </div>

    {{-- Confirm Password --}}
    <div class="form-group">
        <label class="form-label" for="password_confirmation">🔒 Confirm Password</label>
        <div class="input-wrapper">
            <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                class="form-input @error('password_confirmation') is-invalid @enderror"
                placeholder="Re-enter password"
                autocomplete="new-password"
            >
            <button type="button" class="toggle-password" onclick="togglePassword('password_confirmation', this)" aria-label="Show/Hide Password">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
        </div>
        @error('password_confirmation')
            <div class="invalid-feedback">⚠️ {{ $message }}</div>
        @enderror
    </div>

    {{-- Terms --}}
    <div class="form-group" style="margin-bottom: 22px;">
        <label class="remember-me" style="align-items: flex-start; gap: 10px;">
            <input type="checkbox" name="terms" id="terms" required style="margin-top: 2px; flex-shrink: 0;">
            <span style="color: rgba(255,255,255,0.7); font-size: 12.5px; line-height: 1.5;">
                I agree to the <a href="#" style="color: #25D366; text-decoration: none;">Terms of Service</a> and
                <a href="#" style="color: #25D366; text-decoration: none;">Privacy Policy</a>.
            </span>
        </label>
    </div>

    {{-- Register Button --}}
    <button type="submit" class="btn-primary" id="registerBtn">
        ✨ Create Account
    </button>

    {{-- Divider --}}
    <div class="divider">
        <span>Already have an account?</span>
    </div>

    {{-- Login Link --}}
    <div class="register-prompt">
        <a href="{{ route('login') }}" class="register-link">🔐 Login here</a>
    </div>
</form>
@endsection

@section('scripts')
<script>
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

    // Password strength indicator
    document.getElementById('password').addEventListener('input', function() {
        const val = this.value;
        const strength = val.length >= 8 ? (
            /[A-Z]/.test(val) && /[0-9]/.test(val) ? 'strong' : 'medium'
        ) : val.length >= 6 ? 'weak' : '';

        // Remove old indicator
        const old = document.getElementById('strength-bar');
        if (old) old.remove();

        if (strength) {
            const bar = document.createElement('div');
            bar.id = 'strength-bar';
            bar.style.cssText = `
                height: 3px; border-radius: 2px; margin-top: 6px;
                transition: all 0.3s;
                background: ${strength === 'strong' ? '#25D366' : strength === 'medium' ? '#FFA500' : '#ff6b6b'};
                width: ${strength === 'strong' ? '100%' : strength === 'medium' ? '65%' : '33%'};
            `;
            this.closest('.input-wrapper').after(bar);
        }
    });

    document.getElementById('registerForm').addEventListener('submit', function() {
        const btn = document.getElementById('registerBtn');
        btn.innerHTML = '<span class="spinner"></span> Creating Account...';
        btn.classList.add('loading');
    });
</script>
@endsection
