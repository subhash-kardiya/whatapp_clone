@extends('layouts.app')

@section('title', 'Chats — WhatsApp')

@section('content')
<div class="chat-layout" x-data="chatApp()" x-init="initChat()">
       <!-- LEFT PANEL: CHAT LIST -->
    <div class="sidebar-panel" x-show="activeLeftPanel === 'chats'">
        <!-- Sidebar Header -->
        <header class="panel-header chats-header">
            <h1 class="whatsapp-sidebar-title">WhatsApp</h1>
            
            <div class="header-actions">
                <button type="button" @click="showNewChatModal = true" class="action-btn" title="Start New Chat">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                    </svg>
                </button>
                <div style="position: relative;">
                    <button type="button" class="action-btn" @click.stop="activeChatHeaderMenu = false; chatContextMenuItem = null; msgContextMenuItem = null; showChatsMenu = !showChatsMenu" title="Menu">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                            <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                        </svg>
                    </button>
                    <!-- Chats Header Dropdown Menu -->
                    <div class="wa-dropdown-menu" x-show="showChatsMenu" @click.away="showChatsMenu = false" style="display: none; position: absolute; right: 0; top: 100%; z-index: 1000;">
                        <a href="#" @click.prevent="showChatsMenu = false; alert('New Group creation coming soon!')"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>New group</a>
                        <a href="#" @click.prevent="showChatsMenu = false; alert('Starred messages clicked')"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>Starred messages</a>
                        <a href="#" @click.prevent="showChatsMenu = false; alert('Select chats clicked')"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM17.99 9l-1.41-1.42-6.59 6.59-2.58-2.57-1.42 1.41 4 3.99z"/></svg>Select chats</a>
                        <a href="#" @click.prevent="showChatsMenu = false; markAllAsRead()"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 7l-1.41-1.41-6.34 6.34 1.41 1.41L18 7zm4.24-1.41L11.66 16.17 7.48 12l-1.41 1.41L11.66 19l12-12-1.42-1.41zM.41 13.41L6 19l1.41-1.41L1.83 12 .41 13.41z"/></svg>Mark all as read</a>
                        <a href="#" @click.prevent="showChatsMenu = false; alert('App lock clicked')"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>App lock</a>
                        <a href="#" @click.prevent="showChatsMenu = false; document.getElementById('logoutForm').submit()"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M10.09 15.59L11.5 17l5-5-5-5-1.41 1.41L12.67 11H3v2h9.67l-2.58 2.59zM19 3H5c-1.11 0-2 .9-2 2v4h2V5h14v14H5v-4H3v4c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>Log out</a>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Search/Filter -->
        <div class="search-box-container">
            <div class="search-box-wrapper">
                <svg class="search-icon" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
                <input type="text" placeholder="Search or start a new chat" x-model="searchQuery" @input="filterChats()" class="search-input">
            </div>
        </div>

        <!-- Filter Chips Row -->
        <div class="filter-chips-container">
            <button class="filter-chip" :class="{'active': activeFilter === 'all'}" @click="setFilter('all')">All</button>
            <button class="filter-chip" :class="{'active': activeFilter === 'unread'}" @click="setFilter('unread')">
                Unread <span class="filter-badge" x-show="unreadTotalCount() > 0" x-text="unreadTotalCount()"></span>
            </button>
            <button class="filter-chip" :class="{'active': activeFilter === 'favorites'}" @click="setFilter('favorites')">Favorites</button>
            <button class="filter-chip" :class="{'active': activeFilter === 'groups'}" @click="setFilter('groups')">Groups</button>
            <button class="filter-chip" :class="{'active': activeFilter === 'communities'}" @click="setFilter('communities')">Communities</button>
            <button class="filter-chip plus-chip">+</button>
        </div>
        
        <!-- Chat List Items -->
        <div class="chats-scroll-list">
            <template x-for="chat in filteredChats" :key="chat.id">
                <div class="chat-list-item" 
                     :class="{'active': activeUser && activeUser.id === chat.id, 'unread': chat.unreadCount > 0}"
                     @click="selectChat(chat.id)"
                     @contextmenu.prevent="showChatContextMenu($event, chat)">
                    
                    <div class="chat-avatar-wrapper">
                        <img :src="chat.avatar" 
                             :alt="chat.name" 
                             class="chat-item-avatar"
                             @@error="$event.target.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(chat.name) + '&background=128C7E&color=fff&size=80'">
                        <span class="online-indicator-dot" x-show="chat.is_online"></span>
                    </div>
                    
                    <div class="chat-item-details">
                        <div class="chat-item-row-1">
                            <span class="chat-item-name" x-text="chat.name"></span>
                            <div class="chat-item-time-menu-wrapper">
                                <svg x-show="chat.is_pinned" viewBox="0 0 24 24" width="14" height="14" fill="var(--text-muted)" style="flex-shrink:0;"><path d="M14 4v5c0 1.12.37 2.16 1 3H9c.65-.86 1-1.9 1-3V4h4m3-2H7c-.55 0-1 .45-1 1s.45 1 1 1h1v5c0 1.66-1.34 3-3 3v2h5.97v7l1 1 1-1v-7H19v-2c-1.66 0-3-1.34-3-3V4h1c.55 0 1-.45 1-1s-.45-1-1-1z"/></svg>
                                <span class="chat-item-time" x-text="chat.last_message_time"></span>
                                <button type="button" class="chat-item-menu-btn" @click.stop="showChatContextMenu($event, chat)" x-ref="menuBtn">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                        <path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="chat-item-row-2">
                            <span class="chat-item-msg" x-show="!chat.is_typing">
                                <span class="last-msg-status" x-html="chat.last_message_tick" x-show="chat.last_message_sender_self"></span>
                                <span x-text="chat.last_message_preview"></span>
                            </span>
                            <span class="chat-item-typing" x-show="chat.is_typing">typing...</span>
                            
                            <span class="unread-badge" x-show="chat.unreadCount > 0" x-text="chat.unreadCount"></span>
                        </div>
                    </div>
                </div>
            </template>
            
            <div class="empty-list-placeholder" x-show="filteredChats.length === 0">
                No chats found.
            </div>
        </div>

        <!-- Get WhatsApp for Windows banner -->
        <a href="https://www.whatsapp.com/download" target="_blank" class="whatsapp-windows-banner">
            <div class="banner-icon-circle">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                    <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2ZM12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67ZM8.53 7.33C8.37 7.33 8.1 7.39 7.87 7.64C7.65 7.89 7 8.5 7 9.71C7 10.93 7.89 12.1 8 12.27C8.14 12.44 9.76 14.94 12.25 16C12.84 16.27 13.3 16.42 13.66 16.53C14.25 16.72 14.79 16.69 15.22 16.63C15.7 16.56 16.68 16.03 16.89 15.45C17.1 14.87 17.1 14.38 17.04 14.27C16.97 14.17 16.81 14.11 16.56 14C16.31 13.86 15.09 13.26 14.87 13.18C14.64 13.1 14.5 13.06 14.31 13.3C14.15 13.55 13.67 14.11 13.53 14.27C13.38 14.44 13.24 14.46 13 14.34C12.74 14.21 11.94 13.95 11 13.11C10.26 12.45 9.77 11.64 9.62 11.39C9.5 11.15 9.61 11 9.73 10.89C9.84 10.78 10 10.6 10.1 10.45C10.23 10.31 10.27 10.2 10.35 10.04C10.43 9.87 10.39 9.73 10.33 9.61C10.27 9.5 9.77 8.26 9.56 7.77C9.36 7.29 9.16 7.35 9 7.34C8.86 7.34 8.7 7.33 8.53 7.33Z"/>
                </svg>
            </div>
            <span class="banner-text">Get WhatsApp for Windows</span>
        </a>
    </div>

    <!-- LEFT PANEL: CHANNELS VIEW -->
    <div class="sidebar-panel settings-sidebar-panel" x-show="activeLeftPanel === 'channels'" style="display: none;">
        <header class="panel-header">
            <div class="header-back-title">
                <button type="button" class="back-link" @click="setLeftPanel('chats')">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                    </svg>
                </button>
                <h2>Channels</h2>
            </div>
        </header>
        <div class="settings-list-scroll">
            <div class="channels-welcome-box" style="padding: 24px; text-align: center; color: var(--text-secondary);">
                <div style="font-size: 48px; margin-bottom: 16px;">📢</div>
                <h3>Stay updated on your favorite topics</h3>
                <p style="font-size: 14px; margin-top: 8px;">Find channels to follow or click below to discover channels.</p>
                <button type="button" class="filter-chip active" style="margin-top: 16px; width: 100%; py: 8px;" @click="alert('Discovering Channels')">Explore Channels</button>
            </div>
        </div>
    </div>

    <!-- LEFT PANEL: COMMUNITIES VIEW -->
    <div class="sidebar-panel settings-sidebar-panel" x-show="activeLeftPanel === 'communities'" style="display: none;">
        <header class="panel-header">
            <div class="header-back-title">
                <button type="button" class="back-link" @click="setLeftPanel('chats')">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                    </svg>
                </button>
                <h2>Communities</h2>
            </div>
        </header>
        <div class="settings-list-scroll">
            <div class="channels-welcome-box" style="padding: 24px; text-align: center; color: var(--text-secondary);">
                <div style="font-size: 48px; margin-bottom: 16px;">👥</div>
                <h3>Introduce communities</h3>
                <p style="font-size: 14px; margin-top: 8px;">Easily organize your related groups and send announcements. Now your communities will appear here.</p>
                <button type="button" class="filter-chip active" style="margin-top: 16px; width: 100%;" @click="alert('New Community')">New Community</button>
            </div>
        </div>
    </div>

    <!-- LEFT PANEL: SETTINGS VIEW -->
    <div class="sidebar-panel settings-sidebar-panel" x-show="activeLeftPanel === 'settings'" style="display: none;">
        <header class="panel-header">
            <div class="header-back-title">
                <button type="button" class="back-link" @click="setLeftPanel('chats')">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                    </svg>
                </button>
                <h2>Settings</h2>
            </div>
        </header>
        
        <div class="search-box-container">
            <div class="search-box-wrapper">
                <svg class="search-icon" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
                <input type="text" placeholder="Search settings" class="search-input">
            </div>
        </div>

        <div class="settings-list-scroll">
            <!-- Settings Profile Header link -->
            <div class="settings-profile-teaser-card" @click="setLeftPanel('profile')">
                <img src="{{ Auth::user()->avatarUrl() }}" alt="" class="settings-avatar-img"
                     @@error="$event.target.src = 'https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=128C7E&color=fff&size=80'">
                <div class="profile-info-column">
                    <h3 x-text="myUserName"></h3>
                    <p x-text="myUserAbout" style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap; max-width: 250px;"></p>
                </div>
            </div>

            <!-- Settings Options -->
            <div class="settings-options-list">
                <div class="setting-option-item" @click="setLeftPanel('profile')">
                    <span class="setting-icon">👤</span>
                    <div class="setting-text-details">
                        <h4>Profile</h4>
                        <p>Name, status update, profile photo</p>
                    </div>
                </div>

                <div class="setting-option-item" @click="alert('Account settings clicked')">
                    <span class="setting-icon">🔑</span>
                    <div class="setting-text-details">
                        <h4>Account</h4>
                        <p>Security notifications, account info request</p>
                    </div>
                </div>

                <div class="setting-option-item" @click="alert('Privacy settings clicked')">
                    <span class="setting-icon">🔒</span>
                    <div class="setting-text-details">
                        <h4>Privacy</h4>
                        <p>Blocked contacts, disappearing messages</p>
                    </div>
                </div>

                <div class="setting-option-item" @click="toggleTheme(); alert('Theme switched!')">
                    <span class="setting-icon">💬</span>
                    <div class="setting-text-details">
                        <h4>Chats</h4>
                        <p>Theme, chat wallpaper, history</p>
                    </div>
                </div>

                <div class="setting-option-item" @click="alert('Notifications settings clicked')">
                    <span class="setting-icon">🔔</span>
                    <div class="setting-text-details">
                        <h4>Notifications</h4>
                        <p>Message tones, group tones, sounds</p>
                    </div>
                </div>

                <div class="setting-option-item" @click="alert('Keyboard shortcuts config coming soon!')">
                    <span class="setting-icon">⌨️</span>
                    <div class="setting-text-details">
                        <h4>Keyboard shortcuts</h4>
                        <p>Quick actions and navigation keys</p>
                    </div>
                </div>

                <div class="setting-option-item" @click="alert('Help center & details')">
                    <span class="setting-icon">❓</span>
                    <div class="setting-text-details">
                        <h4>Help and feedback</h4>
                        <p>Help center, contact us, terms and privacy policy</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LEFT PANEL: PROFILE VIEW -->
    <div class="sidebar-panel settings-sidebar-panel" x-show="activeLeftPanel === 'profile'" style="display: none;">
        <header class="panel-header">
            <div class="header-back-title">
                <button type="button" class="back-link" @click="setLeftPanel('settings')">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                    </svg>
                </button>
                <h2>Profile</h2>
            </div>
        </header>

        <div class="settings-list-scroll">
            <div class="profile-avatar-large-container">
                <div class="profile-avatar-hover-overlay">
                    <img src="{{ Auth::user()->avatarUrl() }}" alt="" class="profile-avatar-large"
                         @@error="$event.target.src = 'https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=128C7E&color=fff&size=200'">
                    <div class="avatar-hover-text" @click="alert('Change Profile Picture coming soon!')">
                        <span>📷 CHANGE PROFILE PHOTO</span>
                    </div>
                </div>
            </div>

            <!-- Profile Fields -->
            <div class="profile-edit-field-group">
                <label>Your name</label>
                <div class="edit-value-row">
                    <input type="text" x-model="myUserName" :disabled="!isEditingName" class="profile-edit-input" :class="{'editing-active': isEditingName}">
                    <button type="button" class="edit-action-btn" @click="isEditingName = !isEditingName; if(!isEditingName) updateProfileInfo();">
                        <span x-text="isEditingName ? '💾' : '✏️'"></span>
                    </button>
                </div>
                <span class="profile-field-tip">This is not your username or pin. This name will be visible to your WhatsApp contacts.</span>
            </div>

            <div class="profile-edit-field-group">
                <label>About</label>
                <div class="edit-value-row">
                    <input type="text" x-model="myUserAbout" :disabled="!isEditingAbout" class="profile-edit-input" :class="{'editing-active': isEditingAbout}">
                    <button type="button" class="edit-action-btn" @click="isEditingAbout = !isEditingAbout; if(!isEditingAbout) updateProfileInfo();">
                        <span x-text="isEditingAbout ? '💾' : '✏️'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- RIGHT PANEL: CHAT WINDOW -->
    <div class="chat-window-panel">
        
        <!-- Welcome Screen (No chat selected) -->
        <div class="welcome-screen" x-show="!activeUser">
            <div class="welcome-center">
                <div class="welcome-icon-wrapper">
                    <svg viewBox="0 0 24 24" fill="#25D366" width="100" height="100">
                        <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2ZM12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67ZM8.53 7.33C8.37 7.33 8.1 7.39 7.87 7.64C7.65 7.89 7 8.5 7 9.71C7 10.93 7.89 12.1 8 12.27C8.14 12.44 9.76 14.94 12.25 16C12.84 16.27 13.3 16.42 13.66 16.53C14.25 16.72 14.79 16.69 15.22 16.63C15.7 16.56 16.68 16.03 16.89 15.45C17.1 14.87 17.1 14.38 17.04 14.27C16.97 14.17 16.81 14.11 16.56 14C16.31 13.86 15.09 13.26 14.87 13.18C14.64 13.1 14.5 13.06 14.31 13.3C14.15 13.55 13.67 14.11 13.53 14.27C13.38 14.44 13.24 14.46 13 14.34C12.74 14.21 11.94 13.95 11 13.11C10.26 12.45 9.77 11.64 9.62 11.39C9.5 11.15 9.61 11 9.73 10.89C9.84 10.78 10 10.6 10.1 10.45C10.23 10.31 10.27 10.2 10.35 10.04C10.43 9.87 10.39 9.73 10.33 9.61C10.27 9.5 9.77 8.26 9.56 7.77C9.36 7.29 9.16 7.35 9 7.34C8.86 7.34 8.7 7.33 8.53 7.33Z"/>
                    </svg>
                </div>
                <h2>WhatsApp Web</h2>
                <p>Send and receive messages without keeping your phone online.<br>Use WhatsApp on up to 4 linked devices at the same time.</p>
                <div class="encryption-note">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor">
                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                    </svg>
                    End-to-end encrypted
                </div>
            </div>
        </div>
        
        <!-- Active Chat Pane -->
        <div class="active-chat-pane" x-show="activeUser" style="display: none;">
            <!-- Active Chat Header -->
            <header class="panel-header active-header">
                <div class="chat-info-block">
                    <img :src="activeUser ? activeUser.avatar : ''"
                         :alt="activeUser ? activeUser.name : ''"
                         class="avatar-img"
                          @@error="$event.target.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(activeUser ? activeUser.name : 'U') + '&background=128C7E&color=fff&size=80'">
                    <div class="name-status-block">
                        <span class="active-user-name" x-text="activeUser ? activeUser.name : ''"></span>
                        <span class="active-user-status" 
                              :class="{'online-text': activeUser && activeUser.is_online}" 
                              x-text="activeUser ? activeUser.status_text : ''"></span>
                    </div>
                </div>
                
                <div class="header-actions">
                    <button type="button" class="action-btn" @click="alert('Search inside chat')" title="Search inside chat">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        </svg>
                    </button>
                    <div style="position: relative;">
                        <button type="button" class="action-btn" @click.stop="showChatsMenu = false; chatContextMenuItem = null; msgContextMenuItem = null; activeChatHeaderMenu = !activeChatHeaderMenu" title="Menu">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                                <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                            </svg>
                        </button>
                        <!-- Active Chat Header Dropdown Menu -->
                        <div class="wa-dropdown-menu" x-show="activeChatHeaderMenu" @click.away="activeChatHeaderMenu = false" style="display: none; position: absolute; right: 0; top: 100%; z-index: 1000;">
                            <a href="#" @click.prevent="activeChatHeaderMenu = false; toggleUserProfileDetail()"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>Contact info</a>
                            <a href="#" @click.prevent="activeChatHeaderMenu = false; alert('Search clicked')"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>Search</a>
                            <a href="#" @click.prevent="activeChatHeaderMenu = false; alert('Select messages clicked')"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM17.99 9l-1.41-1.42-6.59 6.59-2.58-2.57-1.42 1.41 4 3.99z"/></svg>Select messages</a>
                            <a href="#" @click.prevent="activeChatHeaderMenu = false; alert('Disappearing messages clicked')"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>Disappearing messages</a>
                            <a href="#" @click.prevent="activeChatHeaderMenu = false; alert('Added to Favorites')"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>Add to Favorites</a>
                            <a href="#" @click.prevent="activeChatHeaderMenu = false; alert('Added to list')"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>Add to list</a>
                            <a href="#" @click.prevent="activeChatHeaderMenu = false; activeUser = null"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>Close chat</a>
                            <a href="#" @click.prevent="activeChatHeaderMenu = false; alert('Call link: https://meet.whatsapp.com/' + Math.random().toString(36).substring(7))"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>Send call link</a>
                            <a href="#" @click.prevent="activeChatHeaderMenu = false; if(confirm('Clear all messages?')) { messages = []; }"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>Clear chat</a>
                            <a href="#" @click.prevent="activeChatHeaderMenu = false; if(confirm('Delete conversation?')) { chatPreviews = chatPreviews.filter(c => c.id !== activeUser.id); activeUser = null; filterChats(); }"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>Delete chat</a>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Messages Container -->
            <div class="messages-area-container" id="messagesContainer" @scroll="handleScroll($event)">
                <!-- Load More Spinner -->
                <div class="load-more-spinner-wrapper" x-show="loadingOlder">
                    <span class="small-spinner"></span> older messages...
                </div>
                
                <template x-for="msg in messages" :key="msg.id">
                    <div class="message-row" :class="msg.sender_id === currentUserId ? 'msg-outgoing' : 'msg-incoming'">
                        <div class="msg-bubble-container">
                            
                            <!-- Outgoing forward button (shows on hover) -->
                            <template x-if="msg.sender_id === currentUserId">
                                <button type="button" class="bubble-action-btn forward-btn" title="Forward Message">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                        <path d="M10 9V5l7 7-7 7v-4.1C5 14.9 2.5 18 2.5 18s1-5.1 7.5-9z"/>
                                    </svg>
                                </button>
                            </template>

                            <div class="msg-bubble-wrapper" style="position: relative;">
                                <!-- Message dropdown trigger -->
                                <button type="button" class="msg-dropdown-trigger-btn" @click.stop="showMessageContextMenu($event, msg)">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                        <path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z"/>
                                    </svg>
                                </button>

                                <!-- Message Reaction Badge -->
                                <div class="msg-reaction-badge" x-show="msg.reaction" x-text="msg.reaction"></div>

                                <!-- Link Preview Card -->
                                <template x-if="msg.type === 'link_preview'">
                                    <div class="link-preview-box">
                                        <a :href="msg.message" target="_blank" class="link-preview-card">
                                            <div class="link-preview-info">
                                                <span class="link-preview-title" x-text="msg.link_title"></span>
                                                <span class="link-preview-desc" x-text="msg.link_desc"></span>
                                                <span class="link-preview-domain" x-text="msg.link_domain"></span>
                                            </div>
                                        </a>
                                        <!-- Actual URL text below -->
                                        <div class="link-preview-url-text">
                                            <a :href="msg.message" target="_blank" x-text="msg.message"></a>
                                        </div>
                                    </div>
                                </template>

                                <!-- File attachments -->
                                <div class="msg-attachment-box" x-show="msg.type !== 'text' && msg.type !== 'link_preview'">
                                    <!-- Image Preview -->
                                    <template x-if="msg.type === 'image'">
                                        <div class="attachment-media">
                                            <img :src="msg.file_url" alt="" @click="openLightbox(msg.file_url)" class="img-preview-clickable">
                                        </div>
                                    </template>
                                    
                                    <!-- Video Preview -->
                                    <template x-if="msg.type === 'video'">
                                        <div class="attachment-media">
                                            <video :src="msg.file_url" controls class="video-preview"></video>
                                        </div>
                                    </template>
                                    
                                    <!-- Audio Preview -->
                                    <template x-if="msg.type === 'audio'">
                                        <div class="attachment-audio">
                                            <audio :src="msg.file_url" controls class="audio-control"></audio>
                                        </div>
                                    </template>
                                    
                                    <!-- General File / PDF Specific Design -->
                                    <template x-if="msg.type === 'file'">
                                        <div class="file-bubble-wrapper">
                                            <template x-if="msg.file_name && msg.file_name.toLowerCase().endsWith('.pdf')">
                                                <a :href="msg.file_url" download class="pdf-attachment-card">
                                                    <div class="pdf-icon-square">
                                                        <span class="pdf-badge-label">PDF</span>
                                                    </div>
                                                    <div class="pdf-text-details">
                                                        <span class="pdf-name-txt" x-text="msg.file_name"></span>
                                                        <span class="pdf-size-txt" x-text="msg.file_size || 'PDF • 184 kB'"></span>
                                                    </div>
                                                    <div class="pdf-action-icon">
                                                        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                                                            <path d="M12 16.5l4-4h-3v-5h-2v5H8l4 4zm9-13H3c-1.1 0-2 .9-2 2v13c0 1.1.9 2 2 2h18c1.1 0 2-.9 2-2V5.5c0-1.1-.9-2-2-2zm0 15H3V5.5h18V18.5z"/>
                                                        </svg>
                                                    </div>
                                                </a>
                                            </template>
                                            <template x-if="!msg.file_name || !msg.file_name.toLowerCase().endsWith('.pdf')">
                                                <a :href="msg.file_url" download class="file-download-box">
                                                    <div class="file-icon-square">
                                                        <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                                                            <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                                        </svg>
                                                    </div>
                                                    <div class="file-text-details">
                                                        <span class="file-name-txt" x-text="msg.file_name"></span>
                                                        <span class="file-size-txt" x-text="msg.file_size"></span>
                                                    </div>
                                                </a>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                                
                                <!-- Text Message Content -->
                                <div class="msg-content-text" x-show="msg.message && msg.type !== 'link_preview'" x-text="msg.message"></div>
                                
                                <!-- Bubble Metadata -->
                                <div class="msg-bubble-meta">
                                    <span class="msg-timestamp" x-text="msg.time"></span>
                                    <span class="msg-ticks" x-show="msg.sender_id === currentUserId" x-html="msg.tick_html"></span>
                                </div>
                            </div>
 
                            <!-- Incoming forward button (shows on hover) -->
                            <template x-if="msg.sender_id !== currentUserId">
                                <button type="button" class="bubble-action-btn forward-btn" title="Forward Message">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                        <path d="M10 9V5l7 7-7 7v-4.1C5 14.9 2.5 18 2.5 18s1-5.1 7.5-9z"/>
                                    </svg>
                                </button>
                            </template>
 
                        </div>
                    </div>
                </template>
                
                <!-- Typing status indicator bubble -->
                <div class="message-row msg-incoming" x-show="activeUser && activeUser.is_typing">
                    <div class="msg-bubble-wrapper typing-bubble">
                        <div class="typing-animation-dots">
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reply Bar -->
            <div class="reply-bar" x-show="replyToMessage" style="display: none;">
                <div class="reply-bar-content">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="var(--wa-teal)"><path d="M10 9V5l-7 7 7 7v-4.1c5 0 8.5 1.6 11 5.1-1-5-4-10-11-11z"/></svg>
                    <div class="reply-bar-text">
                        <span class="reply-bar-name" x-text="replyToMessage ? (replyToMessage.sender_id === currentUserId ? 'You' : (activeUser ? activeUser.name : '')) : ''"></span>
                        <span class="reply-bar-msg" x-text="replyToMessage ? replyToMessage.message : ''"></span>
                    </div>
                </div>
                <button type="button" class="reply-bar-close" @click="cancelReply()">×</button>
            </div>

            <!-- Chat Footer Input Area -->
            <footer class="chat-footer">
                <!-- File Preview Panel -->
                <div class="selected-file-preview-strip" x-show="selectedFile" style="display: none;">
                    <div class="preview-filename-badge">
                        <span class="preview-filename" x-text="selectedFile ? selectedFile.name : ''"></span>
                        <button type="button" class="remove-preview-btn" @click="cancelFileSelect()">×</button>
                    </div>
                </div>

                <!-- Attachment Button (+) -->
                <button type="button" class="footer-icon-btn attachment-trigger-btn" @click="$refs.fileInput.click()" title="Attach File">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                    </svg>
                </button>
                <input type="file" x-ref="fileInput" @change="handleFileSelect($event)" style="display: none;">
                
                <!-- Emoji trigger -->
                <button type="button" class="footer-icon-btn emoji-trigger-btn" @click="toggleEmojiTray()" title="Emoji">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 4h2v2h-2V6zm-4 0h2v2H9V6zm3 12c-2.67 0-4.8-1.55-5.92-3h11.84c-1.12 1.45-3.25 3-5.92 3z"/>
                    </svg>
                </button>
                
                <!-- Input field -->
                <form @submit.prevent="submitMessage()" class="message-input-form">
                    <input type="text" 
                           placeholder="Type a message" 
                           x-model="newMessageText"
                           @input="handleInput()"
                           class="footer-input-field">
                    
                    <!-- Send Button -->
                    <button type="submit" class="footer-icon-btn send-msg-btn" x-show="newMessageText.trim() || selectedFile" title="Send">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>

                    <!-- Microphone Button -->
                    <button type="button" class="footer-icon-btn mic-btn" x-show="!newMessageText.trim() && !selectedFile" title="Voice Message">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                            <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5.3-3c0 3-2.54 5.1-5.3 5.1S6.7 14 6.7 11H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c3.28-.48 6-3.3 6-6.72h-1.7z"/>
                        </svg>
                    </button>
                </form>
                
                <!-- Emojis list panel -->
                <div class="emojis-tray-panel" x-show="showEmojis" @click.away="showEmojis = false" style="display: none;">
                    <template x-for="emoji in emojis" :key="emoji">
                        <span class="single-emoji-item" @click="insertEmoji(emoji)" x-text="emoji"></span>
                    </template>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- USER DETAIL SIDEBAR -->
    <div class="user-detail-overlay-panel" x-show="showUserDetail" style="display: none;">
        <header class="detail-header">
            <button class="close-detail-btn" @click="showUserDetail = false">×</button>
            <h3>Contact Info</h3>
        </header>
        <div class="detail-body-content">
            <div class="detail-avatar-container">
                <img :src="activeUser ? activeUser.avatar : ''" alt="" class="detail-avatar-large">
                <h2 x-text="activeUser ? activeUser.name : ''"></h2>
                <p x-text="activeUser ? activeUser.phone : ''"></p>
            </div>
            
            <hr class="detail-divider">
            
            <div class="detail-section">
                <label>About</label>
                <div class="detail-about-txt" x-text="activeUser ? activeUser.about : ''"></div>
            </div>
            
            <div class="detail-section">
                <label>Email Address</label>
                <div class="detail-email-txt" x-text="activeUser ? activeUser.email : ''"></div>
            </div>
        </div>
    </div>
    
    <!-- NEW CHAT MODAL -->
    <div class="modal-backdrop" x-show="showNewChatModal" style="display: none;">
        <div class="modal-card-container" @click.away="showNewChatModal = false">
            <header class="modal-card-header">
                <h2>Start New Chat</h2>
                <button class="close-modal-btn" @click="showNewChatModal = false">×</button>
            </header>
            
            <!-- Contact Search -->
            <div class="modal-search-wrapper">
                <input type="text" 
                       placeholder="Search by name, email or mobile number..." 
                       x-model="contactSearchQuery" 
                       @input="searchContacts()"
                       class="modal-search-input">
            </div>
            
            <!-- Contacts List -->
            <div class="modal-contacts-list">
                <template x-for="contact in contactList" :key="contact.id">
                    <div class="contact-list-item" @click="startConversationWith(contact)">
                        <img :src="contact.avatar" alt="" class="contact-avatar">
                        <div class="contact-info">
                            <span class="contact-name" x-text="contact.name"></span>
                            <span class="contact-about" x-text="contact.about"></span>
                        </div>
                    </div>
                </template>
                <div class="empty-list-placeholder" x-show="contactList.length === 0">
                    No contacts found.
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lightbox for Images -->
    <div class="lightbox-overlay" x-show="lightboxImage" @click="lightboxImage = null" style="display: none;">
        <img :src="lightboxImage" alt="" class="lightbox-img">
        <button class="lightbox-close">×</button>
    </div>

    <!-- Chat Item Options Dropdown Context Menu (Floating) -->
    <div class="wa-dropdown-menu floating-context-menu" 
         x-show="chatContextMenuItem" 
         @click.away="chatContextMenuItem = null" 
         :style="chatContextMenuStyle">
        <a href="#" @click.prevent="archiveChat(chatContextMenuItem)"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z"/></svg>Archive chat</a>
        <a href="#" @click.prevent="toggleMuteChat(chatContextMenuItem)"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/></svg><span x-text="chatContextMenuItem && chatContextMenuItem.is_muted ? 'Unmute' : 'Mute'">Mute notifications</span></a>
        <a href="#" @click.prevent="togglePinChat(chatContextMenuItem)"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 4v5c0 1.12.37 2.16 1 3H9c.65-.86 1-1.9 1-3V4h4m3-2H7c-.55 0-1 .45-1 1s.45 1 1 1h1v5c0 1.66-1.34 3-3 3v2h5.97v7l1 1 1-1v-7H19v-2c-1.66 0-3-1.34-3-3V4h1c.55 0 1-.45 1-1s-.45-1-1-1z"/></svg><span x-text="chatContextMenuItem && chatContextMenuItem.is_pinned ? 'Unpin' : 'Pin'">Pin chat</span></a>
        <a href="#" @click.prevent="markAsRead(chatContextMenuItem)"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 7l-1.41-1.41-6.34 6.34 1.41 1.41L18 7zm4.24-1.41L11.66 16.17 7.48 12l-1.41 1.41L11.66 19l12-12-1.42-1.41zM.41 13.41L6 19l1.41-1.41L1.83 12 .41 13.41z"/></svg>Mark as read</a>
        <a href="#" @click.prevent="toggleFavoriteChat(chatContextMenuItem)"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span x-text="chatContextMenuItem && chatContextMenuItem.is_favorited ? 'Remove from Favorites' : 'Add to Favorites'">Add to Favorites</span></a>
        <a href="#" @click.prevent="clearChatMessages(chatContextMenuItem)"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>Clear chat</a>
        <a href="#" @click.prevent="deleteChat(chatContextMenuItem)"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>Delete chat</a>
    </div>

    <!-- Message Options & Reactions Context Menu (Floating) -->
    <div class="message-context-menu-wrapper"
         x-show="msgContextMenuItem"
         @click.away="msgContextMenuItem = null"
         :style="msgContextMenuStyle">
        
        <!-- Reactions drawer bar -->
        <div class="wa-reactions-bar">
            <button type="button" @click="addReaction('👍')">👍</button>
            <button type="button" @click="addReaction('❤️')">❤️</button>
            <button type="button" @click="addReaction('😂')">😂</button>
            <button type="button" @click="addReaction('😮')">😮</button>
            <button type="button" @click="addReaction('😢')">😢</button>
            <button type="button" @click="addReaction('🙏')">🙏</button>
            <button type="button" @click="alert('Show reaction picker')">＋</button>
        </div>

        <!-- Options list -->
        <div class="wa-dropdown-menu no-margin">
            <a href="#" @click.prevent="replyTo(msgContextMenuItem)"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 9V5l-7 7 7 7v-4.1c5 0 8.5 1.6 11 5.1-1-5-4-10-11-11z"/></svg>Reply</a>
            <a href="#" @click.prevent="copyMessage(msgContextMenuItem)"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>Copy</a>
            <a href="#" @click.prevent="forwardMsg(msgContextMenuItem)"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 9V5l7 7-7 7v-4.1c-5 0-8.5 1.6-11 5.1 1-5 4-10 11-11z"/></svg>Forward</a>
            <a href="#" @click.prevent="starMessage(msgContextMenuItem)"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg><span x-text="msgContextMenuItem && msgContextMenuItem.is_starred ? 'Unstar' : 'Star'">Star</span></a>
            <a href="#" @click.prevent="deleteMessage(msgContextMenuItem)"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>Delete</a>
        </div>
    </div>

    <!-- Forward Modal -->
    <div class="modal-backdrop" x-show="showForwardModal" style="display: none;" @click.self="showForwardModal = false">
        <div class="modal-card-container">
            <header class="modal-card-header">
                <h2>Forward message to</h2>
                <button class="close-modal-btn" @click="showForwardModal = false">×</button>
            </header>
            <div class="modal-contacts-list">
                <template x-for="chat in chatPreviews" :key="chat.id">
                    <div class="contact-list-item" @click="forwardToChat(chat)">
                        <img :src="chat.avatar" alt="" class="contact-avatar">
                        <div class="contact-info">
                            <span class="contact-name" x-text="chat.name"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function chatApp() {
        return {
            currentUserId: {{ Auth::id() }},
            activeUser: null,
            newMessageText: '',
            searchQuery: '',
            contactSearchQuery: '',
            showNewChatModal: false,
            showUserDetail: false,
            showEmojis: false,
            selectedFile: null,
            lightboxImage: null,
            loadingOlder: false,
            hasMoreMessages: true,
            activeFilter: 'all',
            
            // New UI and Sub-Menu States
            activeLeftPanel: 'chats',
            showChatsMenu: false,
            activeChatHeaderMenu: false,
            chatContextMenuItem: null,
            chatContextMenuStyle: {},
            msgContextMenuItem: null,
            msgContextMenuStyle: {},
            myUserName: '{{ addslashes(Auth::user()->name) }}',
            myUserAbout: '{{ addslashes(Auth::user()->about ?? "Peace, love & Ganpati Bappa ❤️") }}',
            isEditingName: false,
            isEditingAbout: false,
            
            setLeftPanel(panel) {
                this.closeAllMenus();
                this.activeLeftPanel = panel;
            },

            closeAllMenus() {
                this.showChatsMenu = false;
                this.activeChatHeaderMenu = false;
                this.chatContextMenuItem = null;
                this.msgContextMenuItem = null;
                this.showNewChatModal = false;
                this.showUserDetail = false;
                this.showEmojis = false;
                this.showForwardModal = false;
            },

            // Message actions
            replyTo(msg) {
                this.replyToMessage = msg;
                this.msgContextMenuItem = null;
                this.$nextTick(() => document.querySelector('.footer-input-field')?.focus());
            },

            cancelReply() {
                this.replyToMessage = null;
            },

            copyMessage(msg) {
                navigator.clipboard.writeText(msg.message).then(() => {
                    this.msgContextMenuItem = null;
                });
            },

            starMessage(msg) {
                msg.is_starred = !msg.is_starred;
                this.msgContextMenuItem = null;
            },

            deleteMessage(msg) {
                this.messages = this.messages.filter(m => m.id !== msg.id);
                this.msgContextMenuItem = null;
            },

            forwardMsg(msg) {
                this.forwardMessage = msg;
                this.msgContextMenuItem = null;
                this.showForwardModal = true;
            },

            forwardToChat(chat) {
                this.showForwardModal = false;
                const fwd = this.forwardMessage;
                this.forwardMessage = null;
                const formData = new FormData();
                formData.append('receiver_id', chat.id);
                if (fwd.message) formData.append('message', fwd.message);
                if (fwd.file_url) formData.append('file_url', fwd.file_url);
                if (fwd.type) formData.append('type', fwd.type);
                axios.post('/chat/send', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                }).then(res => {
                    if (res.data.success) {
                        const chatItem = this.chatPreviews.find(c => c.id === chat.id);
                        if (chatItem) {
                            chatItem.last_message_preview = fwd.message || '📎 File';
                            chatItem.last_message_time = res.data.message.time;
                            chatItem.last_message_sender_self = true;
                            chatItem.last_message_tick = res.data.message.tick_html;
                            chatItem.last_message_timestamp = Math.floor(Date.now() / 1000);
                            this.reorderChatPreviews();
                        }
                    }
                }).catch(err => console.error(err));
            },

            // Chat actions
            markAsRead(chat) {
                chat.unreadCount = 0;
                this.chatContextMenuItem = null;
            },

            markAllAsRead() {
                this.chatPreviews.forEach(c => c.unreadCount = 0);
                this.showChatsMenu = false;
            },

            togglePinChat(chat) {
                chat.is_pinned = !chat.is_pinned;
                this.chatContextMenuItem = null;
                this.reorderChatPreviews();
            },

            toggleMuteChat(chat) {
                chat.is_muted = !chat.is_muted;
                this.chatContextMenuItem = null;
            },

            toggleFavoriteChat(chat) {
                chat.is_favorited = !chat.is_favorited;
                this.chatContextMenuItem = null;
            },

            archiveChat(chat) {
                chat.is_archived = !chat.is_archived;
                this.chatContextMenuItem = null;
                this.filterChats();
            },

            blockChat(chat) {
                chat.is_blocked = true;
                this.chatContextMenuItem = null;
            },

            deleteChat(chat) {
                this.chatPreviews = this.chatPreviews.filter(c => c.id !== chat.id);
                if (this.activeUser && this.activeUser.id === chat.id) this.activeUser = null;
                this.chatContextMenuItem = null;
                this.filterChats();
            },

            clearChatMessages(chat) {
                this.messages = [];
                this.chatContextMenuItem = null;
            },
            
            showChatContextMenu(e, chat) {
                e.stopPropagation();
                this.showChatsMenu = false;
                this.activeChatHeaderMenu = false;
                this.msgContextMenuItem = null;
                const clientX = e.clientX;
                const clientY = e.clientY;
                const target = e.currentTarget;
                const btnRect = target.getBoundingClientRect();
                const isChevron = target.classList.contains('chat-item-menu-btn');

                const menuHeight = 360;
                const menuWidth = 220;
                let top, left;
                if (isChevron) {
                    top = btnRect.bottom + 4;
                    left = btnRect.left;
                } else {
                    top = clientY;
                    left = clientX;
                }
                if (left + menuWidth > window.innerWidth) left = window.innerWidth - menuWidth - 10;
                if (top + menuHeight > window.innerHeight) top = window.innerHeight - menuHeight - 10;
                if (left < 0) left = 4;
                if (top < 0) top = 4;
                this.chatContextMenuStyle = { top: top + 'px', left: left + 'px' };
                this.chatContextMenuItem = chat;
            },
            
            showMessageContextMenu(e, msg) {
                e.stopPropagation();
                this.showChatsMenu = false;
                this.activeChatHeaderMenu = false;
                this.chatContextMenuItem = null;
                const target = e.currentTarget;
                const rect = target.getBoundingClientRect();

                const menuHeight = 380;
                const menuWidth = 280;
                let top = rect.bottom + 4;
                let left = rect.right - menuWidth;
                if (rect.right < window.innerWidth / 2) left = rect.left;
                if (left + menuWidth > window.innerWidth) left = window.innerWidth - menuWidth - 10;
                if (top + menuHeight > window.innerHeight) top = rect.top - menuHeight - 4;
                if (left < 0) left = 4;
                if (top < 0) top = 4;
                this.msgContextMenuStyle = { top: top + 'px', left: left + 'px' };
                this.msgContextMenuItem = msg;
            },
            
            addReaction(emoji) {
                if (this.msgContextMenuItem) {
                    this.msgContextMenuItem.reaction = emoji;
                    this.msgContextMenuItem = null;
                }
            },

            updateProfileInfo() {
                axios.post('{{ route("profile.update") }}', {
                    name: this.myUserName,
                    about: this.myUserAbout
                }).then(res => {
                    // Update client UI
                }).catch(err => console.error(err));
            },
            
            // Raw list of chat previews loaded from backend
            chatPreviews: [
                @foreach($users as $user)
                {
                    id: {{ $user->id }},
                    name: '{{ addslashes($user->name) }}',
                    avatar: '{{ $user->avatarUrl() }}',
                    email: '{{ $user->email }}',
                    phone: '{{ $user->phone ?? "" }}',
                    about: '{{ addslashes($user->about) }}',
                    is_online: {{ $user->is_online ? 'true' : 'false' }},
                    status_text: '{{ $user->lastSeenText() }}',
                    unreadCount: {{ $user->unreadCount }},
                    is_typing: false,
                    last_message_preview: '{{ $user->lastMessage ? addslashes(Str::limit($user->lastMessage->message, 30)) : "" }}',
                    last_message_time: '{{ $user->lastMessage ? $user->lastMessage->timeFormatted() : "" }}',
                    last_message_sender_self: {{ ($user->lastMessage && $user->lastMessage->sender_id === Auth::id()) ? 'true' : 'false' }},
                    last_message_tick: '{!! $user->lastMessage ? $user->lastMessage->tickHtml() : "" !!}',
                    last_message_timestamp: {{ $user->lastMessage ? $user->lastMessage->created_at->timestamp : 0 }}
                },
                @endforeach
            ],
            
            filteredChats: [],
            contactList: [],
            messages: [],
            emojis: ['😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😣', '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠', '😡', '🤬', '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗', '🤔', '🤭', '🤫', '🤥', '😶', '😐', '😑', '😬', '🙄', '😯', '😦', '😧', '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐', '🥴', '🤢', '🤮', '🤧', '😷', '🤒', '🤕', '🤑', '🤠', '😈', '👿', '👹', '👺', '🤡', '💩', '👻', '💀', '☠️', '👽', '👾', '🤖', '🎃', '😺', '😸', '😹', '😻', '😼', '😽', '🙀', '😿', '😾', '👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤏', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💅', '🤳', '💪', '🦾', '🦿', '🦵', '🦶', '👂', '🦻', '👃', '🧠', '🦷', '🦴', '👀', '👁️', '👅', '👄', '💋', '🩸', '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟'],
            
            typingTimeout: null,
            lastTypingEventTime: 0,
            replyToMessage: null,
            forwardMessage: null,
            showForwardModal: false,

            initChat() {
                window.chatAppInstance = this;
                // Sort previews by last message timestamp
                this.reorderChatPreviews();
                this.filterChats();

                this.searchContacts();
                
                // Select active user if ?user= is in the query string
                @if(isset($activeUser))
                    const targetUser = this.chatPreviews.find(u => u.id === {{ $activeUser->id }});
                    if (targetUser) {
                        this.activeUser = targetUser;
                        this.loadInitialMessages();
                    }
                @endif

                // Listen to real-time events
                this.setupWebSocketListeners();
            },

            setFilter(filterName) {
                this.activeFilter = filterName;
                this.filterChats();
            },

            unreadTotalCount() {
                return this.chatPreviews.reduce((sum, chat) => sum + (chat.unreadCount || 0), 0);
            },

            filterChats() {
                let chats = [...this.chatPreviews].filter(c => !c.is_archived);

                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    chats = chats.filter(chat => 
                        chat.name.toLowerCase().includes(q) || 
                        (chat.phone && chat.phone.includes(q)) || 
                        (chat.email && chat.email.toLowerCase().includes(q))
                    );
                }

                if (this.activeFilter === 'unread') {
                    chats = chats.filter(chat => chat.unreadCount > 0);
                } else if (this.activeFilter === 'favorites') {
                    chats = chats.filter(chat => chat.is_favorited);
                } else if (this.activeFilter === 'groups') {
                    chats = [];
                } else if (this.activeFilter === 'communities') {
                    chats = [];
                }

                chats.sort((a, b) => (b.is_pinned ? 1 : 0) - (a.is_pinned ? 1 : 0));

                this.filteredChats = chats;
            },

            selectChat(userId) {
                // Update URL parameter without reload
                const url = new URL(window.location);
                url.searchParams.set('user', userId);
                window.history.pushState({}, '', url);

                const targetUser = this.chatPreviews.find(u => u.id === userId);
                if (targetUser) {
                    this.activeUser = targetUser;
                    this.activeUser.unreadCount = 0;
                    this.showUserDetail = false;
                    this.newMessageText = '';
                    this.selectedFile = null;
                    this.hasMoreMessages = true;
                    this.loadInitialMessages();
                    this.markMessagesAsSeen(userId);
                }
            },

            loadInitialMessages() {
                axios.get(`/chat/more/${this.activeUser.id}`)
                    .then(response => {
                        this.messages = response.data.messages;
                        this.hasMoreMessages = response.data.has_more;
                        this.scrollToBottom();
                    })
                    .catch(error => {
                        console.error('Error loading initial messages:', error);
                    });
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    const container = document.getElementById('messagesContainer');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                });
            },

            handleScroll(e) {
                const container = e.target;
                
                // If scrolled to top and there are more messages to load
                if (container.scrollTop === 0 && this.hasMoreMessages && !this.loadingOlder && this.messages.length > 0) {
                    this.loadingOlder = true;
                    const topMsgId = this.messages[0].id;
                    const previousHeight = container.scrollHeight;

                    axios.get(`/chat/more/${this.activeUser.id}?before=${topMsgId}`)
                        .then(response => {
                            const older = response.data.messages;
                            this.messages = [...older, ...this.messages];
                            this.hasMoreMessages = response.data.has_more;
                            this.loadingOlder = false;
                            
                            // Adjust scroll so the view doesn't jump
                            this.$nextTick(() => {
                                container.scrollTop = container.scrollHeight - previousHeight;
                            });
                        })
                        .catch(err => {
                            console.error('Error loading older messages', err);
                            this.loadingOlder = false;
                        });
                }
            },

            markMessagesAsSeen(userId) {
                axios.post(`/chat/seen/${userId}`).catch(e => console.error(e));
            },

            searchContacts() {
                axios.get(`/users?search=${encodeURIComponent(this.contactSearchQuery)}`)
                    .then(res => {
                        this.contactList = res.data;
                    })
                    .catch(err => console.error(err));
            },

            startConversationWith(contact) {
                this.showNewChatModal = false;
                // Check if contact already exists in preview list
                let chatItem = this.chatPreviews.find(c => c.id === contact.id);
                if (!chatItem) {
                    chatItem = {
                        id: contact.id,
                        name: contact.name,
                        avatar: contact.avatar,
                        about: contact.about,
                        is_online: contact.is_online,
                        status_text: contact.last_seen,
                        unreadCount: 0,
                        is_typing: false,
                        last_message_preview: '',
                        last_message_time: '',
                        last_message_sender_self: false,
                        last_message_tick: '',
                        last_message_timestamp: 0
                    };
                    this.chatPreviews.unshift(chatItem);
                    this.filterChats();
                }
                this.selectChat(contact.id);
            },

            handleInput() {
                // Throttle typing events
                const now = Date.now();
                if (now - this.lastTypingEventTime > 3000) {
                    this.lastTypingEventTime = now;
                    axios.post('/chat/typing', {
                        receiver_id: this.activeUser.id,
                        is_typing: true
                    }).catch(e => {});
                }

                // Clear existing timeout
                clearTimeout(this.typingTimeout);

                // Set timeout to send false typing state
                this.typingTimeout = setTimeout(() => {
                    axios.post('/chat/typing', {
                        receiver_id: this.activeUser.id,
                        is_typing: false
                    }).catch(e => {});
                }, 2500);
            },

            handleFileSelect(e) {
                if (e.target.files.length > 0) {
                    this.selectedFile = e.target.files[0];
                }
            },

            cancelFileSelect() {
                this.selectedFile = null;
                this.$refs.fileInput.value = '';
            },

            toggleEmojiTray() {
                this.showEmojis = !this.showEmojis;
            },

            insertEmoji(emoji) {
                this.newMessageText += emoji;
            },

            toggleUserProfileDetail() {
                this.showUserDetail = !this.showUserDetail;
            },

            openLightbox(url) {
                this.lightboxImage = url;
            },

            formatFileSize(bytes) {
                if (bytes >= 1048576) return round(bytes / 1048576, 1) + ' MB';
                if (bytes >= 1024)    return round(bytes / 1024, 1) + ' KB';
                return bytes + ' B';
            },

            submitMessage() {
                if (!this.newMessageText.trim() && !this.selectedFile) return;

                const formData = new FormData();
                formData.append('receiver_id', this.activeUser.id);
                
                if (this.newMessageText.trim()) {
                    formData.append('message', this.newMessageText.trim());
                }
                if (this.selectedFile) {
                    formData.append('file', this.selectedFile);
                }
                if (this.replyToMessage) {
                    formData.append('reply_to_id', this.replyToMessage.id);
                }

                const tempText = this.newMessageText;
                this.newMessageText = '';
                const fileSent = this.selectedFile;
                this.cancelFileSelect();
                this.replyToMessage = null;

                axios.post('/chat/send', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
                .then(res => {
                    if (res.data.success) {
                        const newMsgObj = res.data.message;
                        newMsgObj.sender_id = this.currentUserId;
                        this.messages.push(newMsgObj);
                        this.scrollToBottom();
                        
                        // Update chat item preview
                        const chatItem = this.chatPreviews.find(c => c.id === this.activeUser.id);
                        if (chatItem) {
                            chatItem.last_message_preview = fileSent ? `📎 ${fileSent.name}` : tempText;
                            chatItem.last_message_time = newMsgObj.time;
                            chatItem.last_message_sender_self = true;
                            chatItem.last_message_tick = newMsgObj.tick_html;
                            chatItem.last_message_timestamp = Math.floor(Date.now() / 1000);
                            
                            // Reorder chat list
                            this.reorderChatPreviews();
                        }
                    }
                })
                .catch(err => {
                    console.error('Error sending message:', err);
                    alert('Failed to send message. Please try again.');
                });
            },

            reorderChatPreviews() {
                this.chatPreviews.sort((a, b) => b.last_message_timestamp - a.last_message_timestamp);
                this.filterChats();
            },

            setupWebSocketListeners() {
                if (typeof window.Echo === 'undefined') {
                    setTimeout(() => this.setupWebSocketListeners(), 1000);
                    return;
                }

                // Private user channel for receiving chats/notifications
                window.Echo.private(`chat.${this.currentUserId}`)
                    .listen('.MessageSent', (data) => {
                        // If we are currently chatting with the sender
                        if (this.activeUser && this.activeUser.id === data.sender_id) {
                            this.messages.push({
                                id: data.id,
                                sender_id: data.sender_id,
                                message: data.message,
                                type: data.type,
                                status: 'seen',
                                file_url: data.file_path,
                                file_name: data.file_name,
                                time: data.time,
                                tick_html: '' // Received msg has no ticks shown for incoming
                            });
                            this.scrollToBottom();
                            this.markMessagesAsSeen(data.sender_id);
                        } else {
                            // Increment unread count in previews
                            const chatItem = this.chatPreviews.find(c => c.id === data.sender_id);
                            if (chatItem) {
                                chatItem.unreadCount++;
                            }
                        }

                        // Update list preview
                        const chatItem = this.chatPreviews.find(c => c.id === data.sender_id);
                        if (chatItem) {
                            chatItem.last_message_preview = data.type !== 'text' ? '📎 File received' : data.message;
                            chatItem.last_message_time = data.time;
                            chatItem.last_message_sender_self = false;
                            chatItem.last_message_timestamp = Math.floor(Date.now() / 1000);
                            this.reorderChatPreviews();
                        }
                    })
                    .listen('.TypingIndicator', (data) => {
                        const chatItem = this.chatPreviews.find(c => c.id === data.sender_id);
                        if (chatItem) {
                            chatItem.is_typing = data.is_typing;
                        }
                        if (this.activeUser && this.activeUser.id === data.sender_id) {
                            this.activeUser.is_typing = data.is_typing;
                        }
                    })
                    .listen('.MessageStatusUpdated', (data) => {
                        // Update status of outgoing messages in active chat
                        if (this.activeUser && this.activeUser.id === data.receiver_id) {
                            this.messages.forEach(msg => {
                                if (msg.sender_id === this.currentUserId) {
                                    if (data.status === 'seen') {
                                        msg.status = 'seen';
                                        msg.tick_html = '<span class="tick tick-seen" title="Seen">✔✔</span>';
                                    } else if (data.status === 'delivered' && msg.status === 'sent') {
                                        msg.status = 'delivered';
                                        msg.tick_html = '<span class="tick tick-delivered" title="Delivered">✔✔</span>';
                                    }
                                }
                            });
                        }

                        // Update status ticks in preview list
                        const chatItem = this.chatPreviews.find(c => c.id === data.receiver_id);
                        if (chatItem) {
                            if (data.status === 'seen') {
                                chatItem.last_message_tick = '<span class="tick tick-seen" title="Seen">✔✔</span>';
                            } else if (data.status === 'delivered') {
                                chatItem.last_message_tick = '<span class="tick tick-delivered" title="Delivered">✔✔</span>';
                            }
                        }
                    });

                // Presence channel for online/offline statuses
                window.Echo.join('online')
                    .here((users) => {
                        users.forEach(u => {
                            const item = this.chatPreviews.find(c => c.id === u.id);
                            if (item) {
                                item.is_online = true;
                                item.status_text = 'online';
                            }
                            if (this.activeUser && this.activeUser.id === u.id) {
                                this.activeUser.is_online = true;
                                this.activeUser.status_text = 'online';
                            }
                        });
                    })
                    .joining((user) => {
                        const item = this.chatPreviews.find(c => c.id === user.id);
                        if (item) {
                            item.is_online = true;
                            item.status_text = 'online';
                        }
                        if (this.activeUser && this.activeUser.id === user.id) {
                            this.activeUser.is_online = true;
                            this.activeUser.status_text = 'online';
                        }
                    })
                    .leaving((user) => {
                        const item = this.chatPreviews.find(c => c.id === user.id);
                        if (item) {
                            item.is_online = false;
                            item.status_text = 'last seen recently';
                        }
                        if (this.activeUser && this.activeUser.id === user.id) {
                            this.activeUser.is_online = false;
                            this.activeUser.status_text = 'last seen recently';
                        }
                    });
            }
        };
    }
</script>
@endsection
