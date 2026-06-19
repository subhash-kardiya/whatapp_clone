<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'WhatsApp')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Axios (Alpine loads after page scripts in sections) -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Vite compiled CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @yield('styles')
</head>

<body>
    <div class="app-container">
        <!-- Sidebar Menu / Leftmost icons (WhatsApp style) -->
        <aside class="app-sidebar-nav">
            <div class="nav-top">
                <!-- Chats -->
                <a href="{{ route('chat') }}" class="nav-item nav-chat-item {{ Route::is('chat') ? 'active' : '' }}"
                    onclick="if (window.chatAppInstance) { event.preventDefault(); window.chatAppInstance.setLeftPanel('chats'); }" title="Chats">
                    <div class="nav-icon-badge-wrapper">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="nav-svg">
                            <path d="M12 2C6.48 2 2 6.48 2 12c0 1.54.36 2.98 1 4.28L1.05 22l5.88-1.54C8.2 21.09 9.54 21.5 11 21.5c5.52 0 10-4.48 10-10S16.52 2 12 2zm0 18c-1.39 0-2.72-.36-3.89-1l-.28-.16-3.5.92.94-3.4-.18-.3C4.41 15.02 4 13.57 4 12c0-4.41 3.59-8 8-8s8 3.59 8 8-3.59 8-8 8z" />
                        </svg>
                        <span class="nav-green-badge" x-data="{ count: 0 }" x-init="setInterval(() => { count = window.chatAppInstance ? window.chatAppInstance.unreadTotalCount() : 0 }, 1000)" x-show="count > 0" x-text="count"></span>
                    </div>
                </a>

                <!-- Status -->
                <a href="{{ route('status.index') }}" class="nav-item {{ Route::is('status.*') ? 'active' : '' }}" title="Status">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="nav-svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z" />
                    </svg>
                </a>

                <!-- Channels -->
                <a href="{{ route('chat', ['panel' => 'channels']) }}" class="nav-item {{ request('panel') === 'channels' ? 'active' : '' }}" title="Channels"
                    onclick="if (window.chatAppInstance && window.location.pathname.replace(/\/$/, '').endsWith('/chat')) { event.preventDefault(); window.chatAppInstance.setLeftPanel('channels'); }">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="nav-svg">
                        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z" />
                    </svg>
                </a>

                <!-- Communities -->
                <a href="{{ route('chat', ['panel' => 'communities']) }}" class="nav-item {{ request('panel') === 'communities' ? 'active' : '' }}" title="Communities"
                    onclick="if (window.chatAppInstance && window.location.pathname.replace(/\/$/, '').endsWith('/chat')) { event.preventDefault(); window.chatAppInstance.setLeftPanel('communities'); }">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="nav-svg">
                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                    </svg>
                </a>

                <!-- Settings (purple gear) -->
                <a href="#" class="nav-item nav-settings-item" onclick="event.preventDefault(); if (window.chatAppInstance) { window.chatAppInstance.setLeftPanel('settings'); }" title="Settings">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="nav-svg">
                        <path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z" />
                    </svg>
                </a>
            </div>

            <div class="nav-bottom">
                <!-- Theme Toggle -->
                <button type="button" class="nav-item theme-toggle" id="themeToggle" title="Toggle Theme" onclick="toggleTheme()">
                    <svg class="sun-icon nav-svg" viewBox="0 0 24 24" fill="currentColor" style="display: none;">
                        <path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM2 13h2c.55 0 1-.45 1-1s-.45-1-1-1H2c-.55 0-1 .45-1 1s.45 1 1 1zm18 0h2c.55 0 1-.45 1-1s-.45-1-1-1h-2c-.55 0-1 .45-1 1s.45 1 1 1zM11 2v2c0 .55.45 1 1 1s1-.45 1-1V2c0-.55-.45-1-1-1s-1 .45-1 1zm0 18v2c0 .55.45 1 1 1s1-.45 1-1v-2c0-.55-.45-1-1-1s-1 .45-1 1zM5.99 4.58c-.39-.39-1.03-.39-1.41 0s-.39 1.03 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0s.39-1.03 0-1.41L5.99 4.58zm12.37 12.37c-.39-.39-1.03-.39-1.41 0s-.39 1.03 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0s.39-1.03 0-1.41l-1.06-1.06zm1.06-10.96c.39-.39.39-1.03 0-1.41s-1.03-.39-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06zM7.05 18.36c.39-.39.39-1.03 0-1.41s-1.03-.39-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06z" />
                    </svg>
                    <svg class="moon-icon nav-svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.3 22c5.96 0 10.8-4.84 10.8-10.8 0-2.9-1.15-5.54-3.03-7.5-.42-.44-1.12-.22-1.22.38-.64 3.73-3.9 6.53-7.79 6.53-2.14 0-4.1-.79-5.6-2.1-.48-.41-1.22-.05-1.2.58.12 4.97 4.14 9.01 9.11 9.11.43.01.83-.02 1.23-.08.64-.1 1.07.6 1.01 1.25-.13 1.34.8 2.63 2.14 2.8 1.48.18 2.83-.8 2.94-2.28.02-.27-.2-.49-.47-.49h-.03z" />
                    </svg>
                </button>

                <!-- Profile Avatar -->
                @auth
                <a href="{{ route('profile') }}" class="nav-item nav-profile-avatar-item {{ Route::is('profile') ? 'active' : '' }}"
                    onclick="if (window.chatAppInstance) { event.preventDefault(); window.chatAppInstance.setLeftPanel('profile'); }" title="Profile">
                    <img src="{{ Auth::user()->avatarUrl() }}"
                        alt="{{ Auth::user()->name }}"
                        class="nav-avatar-img"
                        onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=128C7E&color=fff&size=80'">
                </a>
                @endauth

                <!-- Logout Form -->
                <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                    @csrf
                    <button type="submit" class="nav-item logout-btn" title="Logout">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="nav-svg">
                            <path d="M10.09 15.59L11.5 17l5-5-5-5-1.41 1.41L12.67 11H3v2h9.67l-2.58 2.59zM19 3H5c-1.11 0-2 .9-2 2v4h2V5h14v14H5v-4H3v4c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z" />
                        </svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="app-main-content">
            @yield('content')
        </main>
    </div>

    <!-- Core App JS -->
    <script>
        // Set CSRF token for Axios
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Theme management
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcons(newTheme);
        }

        function updateThemeIcons(theme) {
            const sunIcon = document.querySelector('.sun-icon');
            const moonIcon = document.querySelector('.moon-icon');
            if (theme === 'dark') {
                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
            } else {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            }
        }

        // Apply saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        updateThemeIcons(savedTheme);

        // Ping online status every 30 seconds
        @auth
        setInterval(() => {
            axios.post('{{ route("online.status") }}').catch(e => console.log('Ping failed', e));
        }, 30000);

        // Mark user offline when tab/window closes
        window.addEventListener('beforeunload', () => {
            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            navigator.sendBeacon('{{ route("offline.status") }}', fd);
        });

        // Periodically clean stale online statuses (every 60 seconds)
        setInterval(() => {
            axios.post('{{ route("online.clean") }}').catch(() => {});
        }, 60000);
        @endauth
    </script>
    @yield('scripts')

    <!-- Alpine.js must load AFTER chatApp() and other page scripts -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
</body>

</html>