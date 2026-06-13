@extends('layouts.app')

@section('title', 'Status - WhatsApp')

@section('content')
<div class="status-layout" x-data="statusApp()" x-init="init()">
    
    <!-- LEFT PANEL: STATUS LIST -->
    <div class="sidebar-panel">
        <header class="panel-header">
            <div class="header-back-title">
                <a href="{{ route('chat') }}" class="back-link" title="Back to chats">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                    </svg>
                </a>
                <h2>Status</h2>
            </div>
        </header>

        <div class="status-scroll-list">
            <!-- Own Status Section -->
            <div class="status-section-title">My Status</div>
            
            <div class="status-list-item-wrapper">
                <div class="status-list-item">
                    <div class="status-avatar-ring" :class="myStatuses.length > 0 ? 'has-stories read' : ''" @click="myStatuses.length > 0 ? playUserStatuses(myUserId) : null">
                        <img src="{{ Auth::user()->avatarUrl() }}" alt="" class="status-item-avatar">
                    </div>
                    
                    <div class="status-item-details">
                        <div class="status-item-info">
                            <span class="status-item-name">My Status</span>
                            <span class="status-item-time" x-text="myStatuses.length > 0 ? 'Tap to view' : 'No updates'"></span>
                        </div>
                        
                        <div class="status-actions-buttons">
                            <!-- Text Status Button -->
                            <button type="button" @click="openTextStatusModal()" class="status-action-btn write-btn" title="Add text status">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                </svg>
                            </button>
                            
                            <!-- Media Status Button -->
                            <button type="button" @click="$refs.mediaInput.click()" class="status-action-btn media-btn" title="Add photo/video status">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                                    <path d="M9 2L7.17 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2h-3.17L15 2H9zm3 15c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5z"/>
                                </svg>
                            </button>
                            <input type="file" x-ref="mediaInput" accept="image/*,video/*" @change="uploadMediaStatus($event)" style="display: none;">
                        </div>
                    </div>
                </div>

                <!-- List of my own active statuses with delete button -->
                <template x-show="myStatuses.length > 0">
                    <div class="my-active-statuses-list">
                        <template x-for="st in myStatuses" :key="st.id">
                            <div class="my-status-subitem">
                                <span class="subitem-type" x-text="st.type === 'text' ? '📝 Text Update' : '📷 Media Update'"></span>
                                <span class="subitem-time" x-text="st.time_ago"></span>
                                <form :action="'/status/' + st.id" method="POST" onsubmit="return confirm('Are you sure you want to delete this status?')" class="delete-status-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-status-btn" title="Delete Status">×</button>
                                </form>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Contacts Status Updates -->
            <div class="status-section-title">Recent Updates</div>
            <div class="other-statuses-scroll">
                <template x-for="user in otherUsers" :key="user.id">
                    <div class="status-list-item clickable" @click="playUserStatuses(user.id)">
                        <div class="status-avatar-ring" :class="user.all_statuses_read ? 'has-stories read' : 'has-stories unread'">
                            <img :src="user.avatar" alt="" class="status-item-avatar">
                        </div>
                        
                        <div class="status-item-details">
                            <div class="status-item-info">
                                <span class="status-item-name" x-text="user.name"></span>
                                <span class="status-item-time" x-text="user.latest_status_time"></span>
                            </div>
                        </div>
                    </div>
                </template>
                <div class="empty-list-placeholder" x-show="otherUsers.length === 0">
                    No status updates yet.
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL: MAIN ILLUSTRATION / WELCOME OR STORIES PLAYER -->
    <div class="status-window-panel">
        
        <!-- Welcome Screen (No status playing) -->
        <div class="welcome-screen" x-show="!activePlaying">
            <div class="welcome-center">
                <div class="status-welcome-circle">
                    <svg viewBox="0 0 24 24" fill="#075E54" width="80" height="80">
                        <path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                    </svg>
                </div>
                <h2>Select a contact to view status</h2>
                <p>Statuses will be available for 24 hours after upload.</p>
            </div>
        </div>

        <!-- Fullscreen Story Viewer Panel -->
        <div class="stories-viewer-container" x-show="activePlaying" style="display: none;" :style="activeStory && activeStory.type === 'text' ? 'background-color: ' + activeStory.background_color : ''">
            <!-- Progress bars -->
            <div class="story-progress-container">
                <template x-for="(st, idx) in activeUserStories" :key="st.id">
                    <div class="story-progress-bar-bg">
                        <div class="story-progress-bar-fill" 
                             :style="idx < activeStoryIndex ? 'width: 100%' : (idx === activeStoryIndex ? 'width: ' + progressPercent + '%' : 'width: 0%')"></div>
                    </div>
                </template>
            </div>

            <!-- Header info -->
            <header class="story-player-header">
                <div class="story-user-details">
                    <img :src="playingUser ? playingUser.avatar : ''" alt="" class="player-avatar">
                    <div class="player-meta">
                        <span class="player-name" x-text="playingUser ? playingUser.name : ''"></span>
                        <span class="player-time" x-text="activeStory ? activeStory.time_ago : ''"></span>
                    </div>
                </div>
                <button type="button" @click="stopPlaying()" class="close-player-btn">×</button>
            </header>

            <!-- Navigation zones -->
            <div class="story-nav-zone left-zone" @click="prevStory()"></div>
            <div class="story-nav-zone right-zone" @click="nextStory()"></div>

            <!-- Content Area -->
            <div class="story-content-body">
                <!-- Text Status -->
                <template x-if="activeStory && activeStory.type === 'text'">
                    <div class="story-text-content" x-text="activeStory.caption"></div>
                </template>

                <!-- Image Status -->
                <template x-if="activeStory && activeStory.type === 'image'">
                    <div class="story-media-wrapper">
                        <img :src="activeStory.media_url" alt="" class="story-image">
                        <div class="story-caption" x-show="activeStory.caption" x-text="activeStory.caption"></div>
                    </div>
                </template>

                <!-- Video Status -->
                <template x-if="activeStory && activeStory.type === 'video'">
                    <div class="story-media-wrapper">
                        <video :src="activeStory.media_url" autoplay playsinline id="storyVideo" class="story-video" @ended="nextStory()"></video>
                        <div class="story-caption" x-show="activeStory.caption" x-text="activeStory.caption"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- TEXT STATUS MODAL -->
    <div class="modal-backdrop" x-show="showTextStatusModal" style="display: none;">
        <div class="modal-card-container text-status-composer" :style="'background-color: ' + composerBg" @click.away="showTextStatusModal = false">
            <header class="modal-card-header no-border">
                <h2 style="color: white;">Write Status</h2>
                <button class="close-modal-btn" style="color: white;" @click="showTextStatusModal = false">×</button>
            </header>
            
            <form action="{{ route('status.store') }}" method="POST" class="text-status-form">
                @csrf
                <input type="hidden" name="type" value="text">
                <input type="hidden" name="background_color" :value="composerBg">
                
                <textarea name="message" placeholder="Type something..." required class="text-status-textarea" maxlength="500"></textarea>
                
                <div class="composer-controls">
                    <!-- Background changer button -->
                    <button type="button" @click="cycleComposerBg()" class="bg-cycle-btn" title="Change color">🎨</button>
                    <button type="submit" class="send-status-btn">Post 🚀</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function statusApp() {
        return {
            myUserId: {{ Auth::id() }},
            myStatuses: [
                @foreach($myStatuses as $st)
                {
                    id: {{ $st->id }},
                    type: '{{ $st->type }}',
                    media_url: '{{ $st->mediaUrl() }}',
                    caption: '{{ addslashes($st->caption) }}',
                    background_color: '{{ $st->background_color }}',
                    time_ago: '{{ $st->created_at->diffForHumans() }}'
                },
                @endforeach
            ],
            otherUsers: [
                @foreach($otherUsersWithStatus as $user)
                {
                    id: {{ $user->id }},
                    name: '{{ addslashes($user->name) }}',
                    avatar: '{{ $user->avatarUrl() }}',
                    all_statuses_read: {{ $user->all_statuses_read ? 'true' : 'false' }},
                    latest_status_time: '{{ $user->statuses->first()->created_at->diffForHumans() }}',
                    stories: [
                        @foreach($user->statuses as $st)
                        {
                            id: {{ $st->id }},
                            type: '{{ $st->type }}',
                            media_url: '{{ $st->mediaUrl() }}',
                            caption: '{{ addslashes($st->caption) }}',
                            background_color: '{{ $st->background_color }}',
                            time_ago: '{{ $st->created_at->diffForHumans() }}'
                        },
                        @endforeach
                    ]
                },
                @endforeach
            ],

            activePlaying: false,
            playingUser: null,
            activeUserStories: [],
            activeStoryIndex: 0,
            activeStory: null,
            progressPercent: 0,
            progressInterval: null,
            storyDuration: 5000, // 5 seconds per slide
            
            showTextStatusModal: false,
            composerBg: '#075E54',
            bgColorsList: ['#075E54', '#128C7E', '#25D366', '#3f51b5', '#e91e63', '#9c27b0', '#ff5722', '#673ab7', '#333333'],
            composerBgIndex: 0,

            init() {
                // Prepare own stories structure
                if (this.myStatuses.length > 0) {
                    this.myUserObj = {
                        id: this.myUserId,
                        name: 'My Status',
                        avatar: '{{ Auth::user()->avatarUrl() }}',
                        stories: this.myStatuses
                    };
                }
            },

            openTextStatusModal() {
                this.showTextStatusModal = true;
                this.composerBg = '#075E54';
                this.composerBgIndex = 0;
            },

            cycleComposerBg() {
                this.composerBgIndex = (this.composerBgIndex + 1) % this.bgColorsList.length;
                this.composerBg = this.bgColorsList[this.composerBgIndex];
            },

            uploadMediaStatus(e) {
                if (e.target.files.length === 0) return;
                const file = e.target.files[0];
                const caption = prompt('Add a caption for this status? (Optional)');
                
                const formData = new FormData();
                formData.append('type', file.type.startsWith('video/') ? 'video' : 'image');
                formData.append('media', file);
                if (caption) {
                    formData.append('caption', caption);
                }

                axios.post('{{ route("status.store") }}', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
                .then(() => {
                    window.location.reload();
                })
                .catch(err => {
                    console.error(err);
                    alert('Failed to upload status.');
                });
            },

            playUserStatuses(userId) {
                let userObj = null;
                if (userId === this.myUserId) {
                    userObj = this.myUserObj;
                } else {
                    userObj = this.otherUsers.find(u => u.id === userId);
                }

                if (!userObj || userObj.stories.length === 0) return;

                this.playingUser = userObj;
                this.activeUserStories = userObj.stories;
                this.activeStoryIndex = 0;
                this.activePlaying = true;
                this.showStory(0);
            },

            showStory(idx) {
                clearInterval(this.progressInterval);
                this.activeStoryIndex = idx;
                this.activeStory = this.activeUserStories[idx];
                this.progressPercent = 0;
                
                // Mark as viewed
                axios.post(`/status/view/${this.activeStory.id}`).catch(err => console.error(err));

                // If media is video, wait for video duration or 5s
                let duration = this.storyDuration;
                
                this.$nextTick(() => {
                    const video = document.getElementById('storyVideo');
                    if (video) {
                        video.load();
                        video.play().catch(e => console.log('Autoplay blocked', e));
                    }
                });

                // Start progress countdown
                const steps = 100;
                const intervalTime = duration / steps;
                
                this.progressInterval = setInterval(() => {
                    this.progressPercent += 1;
                    if (this.progressPercent >= 100) {
                        clearInterval(this.progressInterval);
                        this.nextStory();
                    }
                }, intervalTime);
            },

            nextStory() {
                if (this.activeStoryIndex < this.activeUserStories.length - 1) {
                    this.showStory(this.activeStoryIndex + 1);
                } else {
                    this.stopPlaying();
                    
                    // Mark user as read in UI
                    if (this.playingUser && this.playingUser.id !== this.myUserId) {
                        this.playingUser.all_statuses_read = true;
                    }
                }
            },

            prevStory() {
                if (this.activeStoryIndex > 0) {
                    this.showStory(this.activeStoryIndex - 1);
                } else {
                    this.stopPlaying();
                }
            },

            stopPlaying() {
                clearInterval(this.progressInterval);
                this.activePlaying = false;
                this.playingUser = null;
                this.activeUserStories = [];
                this.activeStory = null;
            }
        };
    }
</script>
@endsection
