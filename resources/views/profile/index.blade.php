@extends('layouts.app')

@section('title', 'Profile - WhatsApp')

@section('content')
<div class="profile-settings-layout">
    <div class="sidebar-panel">
        <header class="panel-header">
            <div class="header-back-title">
                <a href="{{ route('chat') }}" class="back-link" title="Back to chats">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                    </svg>
                </a>
                <h2>Profile</h2>
            </div>
        </header>

        <div class="profile-scroll-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-update-form">
                @csrf
                
                <!-- Avatar Upload with Preview -->
                <div class="avatar-edit-container" x-data="{ imgPreview: '{{ $user->avatarUrl() }}' }">
                    <div class="avatar-preview-wrapper">
                        <img :src="imgPreview" alt="Profile Avatar" class="profile-avatar-large">
                        
                        <label for="avatarInput" class="avatar-upload-overlay" title="Change Photo">
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                                <path d="M3 4V1h2v3h3v2H5v3H3V6H0V4h3zm3 6V7h3V4h7l1.83 2H21c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H5c-1.1 0-2-.9-2-2V10h3zm7 9c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-3.2-5c0-1.77 1.43-3.2 3.2-3.2s3.2 1.43 3.2 3.2-1.43 3.2-3.2 3.2-3.2-1.43-3.2-3.2z"/>
                            </svg>
                            <span>Change Photo</span>
                        </label>
                        <input type="file" id="avatarInput" name="avatar" accept="image/*" @change="
                            const file = $event.target.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = (e) => { imgPreview = e.target.result; };
                                reader.readAsDataURL(file);
                            }
                        " style="display: none;">
                    </div>
                    @error('avatar')
                        <div class="invalid-feedback">⚠️ {{ $message }}</div>
                    @enderror
                </div>

                <!-- Profile fields -->
                <div class="profile-input-group">
                    <label class="profile-label">Your Name</label>
                    <div class="profile-input-wrapper">
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="profile-input @error('name') is-invalid @enderror" placeholder="Enter name" required>
                    </div>
                    <span class="profile-hint">This is not your username. This name will be visible to your WhatsApp contacts.</span>
                    @error('name')
                        <div class="invalid-feedback">⚠️ {{ $message }}</div>
                    @enderror
                </div>

                <div class="profile-input-group">
                    <label class="profile-label">About</label>
                    <div class="profile-input-wrapper">
                        <input type="text" name="about" value="{{ old('about', $user->about) }}" class="profile-input @error('about') is-invalid @enderror" placeholder="Write about yourself">
                    </div>
                    @error('about')
                        <div class="invalid-feedback">⚠️ {{ $message }}</div>
                    @enderror
                </div>

                <div class="profile-input-group readonly-group">
                    <label class="profile-label">Email Address (Cannot be changed)</label>
                    <div class="profile-input-wrapper">
                        <input type="email" value="{{ $user->email }}" class="profile-input readonly" readonly>
                    </div>
                </div>

                <div class="profile-input-group readonly-group">
                    <label class="profile-label">Mobile Number (Cannot be changed)</label>
                    <div class="profile-input-wrapper">
                        <input type="text" value="{{ $user->phone ?? 'Not added' }}" class="profile-input readonly" readonly>
                    </div>
                </div>

                <button type="submit" class="save-profile-btn">
                    🚀 Save Changes
                </button>
            </form>
        </div>
    </div>

    <!-- Right-hand side illustration placeholder for visual balance -->
    <div class="profile-illustration-panel">
        <div class="welcome-center">
            <div class="profile-illustration-circle">
                <svg viewBox="0 0 24 24" fill="#128C7E" width="80" height="80">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                </svg>
            </div>
            <h2>Your Profile Details</h2>
            <p>Change your photo, name, and about info so your friends can easily recognize you.</p>
        </div>
    </div>
</div>
@endsection
