<script>
    window.__CHAT_INIT__ = {"currentUserId":1,"pollGlobalLastId":77,"activeUserId":null,"chatPreviews":[{"id":5,"name":"subhash kardiya","avatar":"http:\/\/localhost\/storage\/avatars\/nedt3xtJTjY8cKBLr3DHDamxM7MeD4jLb5giJ2cy.jpg","email":"karadiyashubhash@gmail.com","phone":"9023772127","about":"Hey there! I am using WhatsApp.","is_online":true,"status_text":"online","unreadCount":2,"is_typing":false,"last_message_preview":"dhggh","last_message_time":"13\/06\/2026","last_message_sender_self":false,"last_message_tick":"\u003Csvg class=\u0022tick-svg tick-sent\u0022 viewBox=\u00220 0 16 11\u0022 width=\u002216\u0022 height=\u002211\u0022\u003E\u003Cpath d=\u0022M11.071.653a.457.457 0 0 0-.304-.102-.493.493 0 0 0-.381.178l-6.19 7.636-2.011-2.095a.463.463 0 0 0-.353-.145.47.47 0 0 0-.335.136.474.474 0 0 0-.016.678l2.375 2.459a.447.447 0 0 0 .347.156h.014a.472.472 0 0 0 .352-.176l6.544-8.058a.448.448 0 0 0-.042-.665z\u0022 fill=\u0022currentColor\u0022\/\u003E\u003C\/svg\u003E","last_message_timestamp":1781336707,"is_archived":false,"is_muted":false,"is_pinned":false,"is_favorited":false,"is_blocked":false,"is_group":false,"community_id":null},{"id":"group_1","name":"new Group","avatar":"https:\/\/ui-avatars.com\/api\/?name=new+Group\u0026background=00a884\u0026color=fff\u0026size=80","email":"","phone":"","about":"Group \u00b7 ","is_online":false,"status_text":"5 members","unreadCount":0,"is_typing":false,"last_message_preview":"hi","last_message_time":"12:00 PM","last_message_sender_self":false,"last_message_tick":"","last_message_timestamp":1781784007,"is_archived":false,"is_muted":false,"is_pinned":false,"is_favorited":false,"is_blocked":false,"is_group":true,"group_db_id":1,"admin_id":5,"members":[5,1,3,6,2],"group_description":"","community_id":2}]};

    function chatApp() {
        return {
            currentUserId: window.__CHAT_INIT__.currentUserId,
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
            myUserName: 'Subhash Kardiya',
            myUserAbout: 'How is everything? ☕',
            isEditingName: false,
            isEditingAbout: false,

            setLeftPanel(panel) {
                this.closeAllMenus();
                this.activeLeftPanel = panel;
                if (panel === 'channels' || panel === 'settings' || panel === 'profile') {
                    this.activeUser = null;
                    this.activeChannel = null;
                    this.activeCommunity = null;
                    this.communityRightView = 'none';
                }
                if (panel === 'communities') {
                    this.activeUser = null;
                    this.activeChannel = null;
                    this.communityRightView = 'none';
                }
                if (panel === 'chats') {
                    this.activeCommunity = null;
                    this.communityRightView = 'none';
                }
                if (panel === 'channels') this.loadChannels();
                if (panel === 'communities') {
                    this.communityStep = 'list';
                    this.loadCommunities();
                }
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
                this.showNewGroupModal = false;
                this.showStarredModal = false;
                this.showAppLockModal = false;
                this.appLockPin = '';
                this.appLockConfirmPin = '';
                this.appLockError = '';
                this.newGroupName = '';
                this.newGroupSelectedUsers = [];
                // Don't exit selectMode here - user may want to keep selecting
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
                axios.post(`/messages/${msg.id}/star`)
                    .then(res => {
                        msg.is_starred = res.data.starred;
                        if (res.data.starred) {
                            if (!this.starredMessages.find(m => m.id === msg.id)) {
                                this.starredMessages.push({
                                    ...msg
                                });
                            }
                        } else {
                            this.starredMessages = this.starredMessages.filter(m => m.id !== msg.id);
                        }
                    })
                    .catch(err => console.error(err));
                this.msgContextMenuItem = null;
            },

            deleteMessage(msg) {
                const forEveryone = msg.sender_id === this.currentUserId && confirm('Delete for everyone?');
                axios.delete(`/messages/${msg.id}`, {
                        data: {
                            for_everyone: forEveryone
                        }
                    })
                    .then(() => {
                        if (forEveryone) {
                            msg.message = 'This message was deleted';
                            msg.type = 'text';
                        } else {
                            this.messages = this.messages.filter(m => m.id !== msg.id);
                        }
                    })
                    .catch(err => console.error(err));
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
                const receiverId = chat.is_group ? null : chat.id;
                if (!receiverId) return;
                formData.append('receiver_id', receiverId);
                if (fwd.message) formData.append('message', fwd.message);
                if (fwd.id) formData.append('forward_message_id', fwd.id);
                if (fwd.type && fwd.type !== 'text') formData.append('type', fwd.type);
                axios.post('/chat/send', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
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
                if (!chat.is_group && chat.id) {
                    axios.post(`/chat/seen/${chat.id}`).catch(err => console.error(err));
                }
                chat.unreadCount = 0;
                this.chatContextMenuItem = null;
                this.updateDocumentTitle();
            },

            markAllAsRead() {
                this.chatPreviews.forEach(c => c.unreadCount = 0);
                this.showChatsMenu = false;
                this.updateDocumentTitle();
            },

            togglePinChat(chat) {
                chat.is_pinned = !chat.is_pinned;
                this.saveChatPreference(chat, {
                    is_pinned: chat.is_pinned
                });
                this.chatContextMenuItem = null;
                this.reorderChatPreviews();
            },

            toggleMuteChat(chat) {
                chat.is_muted = !chat.is_muted;
                this.saveChatPreference(chat, {
                    is_muted: chat.is_muted
                });
                this.chatContextMenuItem = null;
            },

            toggleFavoriteChat(chat) {
                chat.is_favorited = !chat.is_favorited;
                this.saveChatPreference(chat, {
                    is_favorited: chat.is_favorited
                });
                this.chatContextMenuItem = null;
            },

            archiveChat(chat) {
                chat.is_archived = !chat.is_archived;
                this.saveChatPreference(chat, {
                    is_archived: chat.is_archived
                });
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

            // ============ SELECT MODE ============
            toggleSelectMode() {
                this.selectMode = !this.selectMode;
                if (!this.selectMode) {
                    this.selectedChats = [];
                }
            },

            exitSelectMode() {
                this.selectMode = false;
                this.selectedChats = [];
            },

            toggleChatSelection(chat) {
                if (!this.selectMode) return;
                const idx = this.selectedChats.findIndex(c => c.id === chat.id);
                if (idx > -1) {
                    this.selectedChats.splice(idx, 1);
                } else {
                    this.selectedChats.push(chat);
                }
            },

            deleteSelectedChats() {
                if (!confirm('Delete ' + this.selectedChats.length + ' chat(s)?')) return;
                const ids = this.selectedChats.map(c => c.id);
                this.chatPreviews = this.chatPreviews.filter(c => !ids.includes(c.id));
                if (this.activeUser && ids.includes(this.activeUser.id)) this.activeUser = null;
                this.exitSelectMode();
                this.filterChats();
            },

            archiveSelectedChats() {
                this.selectedChats.forEach(chat => {
                    chat.is_archived = true;
                });
                this.exitSelectMode();
                this.filterChats();
            },

            // ============ NEW GROUP ============
            toggleGroupUser(contact) {
                const idx = this.newGroupSelectedUsers.findIndex(u => u.id === contact.id);
                if (idx > -1) {
                    this.newGroupSelectedUsers.splice(idx, 1);
                } else {
                    this.newGroupSelectedUsers.push(contact);
                }
            },

            createGroup() {
                if (this.newGroupSelectedUsers.length === 0) return;
                const name = this.newGroupName.trim() || this.newGroupSelectedUsers.map(u => u.name).join(', ');
                const ids = this.newGroupSelectedUsers.map(u => u.id).join(',');
                const memberNames = this.newGroupSelectedUsers.map(u => u.name).join(', ');
                const groupAvatar = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(name) + '&background=00a884&color=fff&size=80';

                this.showNewGroupModal = false;
                this.newGroupName = '';
                this.newGroupSelectedUsers = [];
                this.contactSearchQuery = '';

                // Save to DB first
                axios.post('/chat/group/create', {
                        name: name,
                        members: ids
                    })
                    .then(res => {
                        if (res.data.success) {
                            const g = res.data.group;
                            const groupId = 'group_' + g.id;
                            const groupItem = {
                                id: groupId,
                                name: g.name,
                                avatar: groupAvatar,
                                about: 'Group · ' + memberNames,
                                is_online: false,
                                is_group: true,
                                group_db_id: g.id,
                                admin_id: g.admin_id,
                                members: g.member_ids,
                                group_description: g.description || '',
                                status_text: g.member_ids.length + ' members',
                                unreadCount: 0,
                                is_typing: false,
                                last_message_preview: '',
                                last_message_time: '',
                                last_message_sender_self: false,
                                last_message_tick: '',
                                last_message_timestamp: 0,
                                is_archived: false,
                                is_muted: false,
                                is_pinned: false,
                                is_favorited: false,
                                is_blocked: false,
                            };
                            this.chatPreviews.unshift(groupItem);
                            this.filterChats();
                            this.selectChat(groupId);
                            this.$nextTick(() => {
                                this.loadInitialMessages();
                            });
                        }
                    })
                    .catch(() => {});
            },

            // ============ GROUP MANAGEMENT ============
            getMemberName(memberId) {
                if (memberId === this.currentUserId) return this.myUserName;
                const chat = this.chatPreviews.find(c => c.id === memberId);
                if (chat) return chat.name;
                const contact = this.contactList.find(c => c.id === memberId);
                if (contact) return contact.name;
                return 'User ' + memberId;
            },

            getMemberAvatar(memberId) {
                if (memberId === this.currentUserId) {
                    return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(this.myUserName) + '&background=128C7E&color=fff&size=80';
                }
                const chat = this.chatPreviews.find(c => c.id === memberId);
                if (chat) return chat.avatar;
                return 'https://ui-avatars.com/api/?name=U&background=128C7E&color=fff&size=80';
            },

            triggerGroupIconUpload() {
                document.getElementById('groupIconInput').click();
            },

            uploadGroupIcon(event) {
                const file = event.target.files[0];
                if (!file || !this.activeUser?.group_db_id) return;
                const formData = new FormData();
                formData.append('avatar', file);
                axios.post('/chat/group/' + this.activeUser.group_db_id + '/avatar', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(res => {
                    if (res.data.success) {
                        this.activeUser.avatar = res.data.avatar;
                        const chatItem = this.chatPreviews.find(c => c.id === this.activeUser.id);
                        if (chatItem) chatItem.avatar = res.data.avatar;
                    }
                }).catch(err => console.error(err));
            },

            saveGroupName() {
                if (!this.editingGroupNameValue.trim()) return;
                const newName = this.editingGroupNameValue.trim();
                const groupId = this.activeUser.group_db_id;
                this.editingGroupName = false;
                axios.post('/chat/group/' + groupId + '/update-name', {
                        name: newName
                    })
                    .then(res => {
                        if (res.data.success) {
                            this.activeUser.name = newName;
                            const chatItem = this.chatPreviews.find(c => c.id === this.activeUser.id);
                            if (chatItem) chatItem.name = newName;
                            this.reorderChatPreviews();
                        }
                    })
                    .catch(err => console.error('Error renaming group:', err));
            },

            saveGroupDesc() {
                const newDesc = this.editingGroupDescValue.trim();
                const groupId = this.activeUser.group_db_id;
                this.editingGroupDesc = false;
                axios.post('/chat/group/' + groupId + '/update-desc', {
                        description: newDesc
                    })
                    .then(res => {
                        if (res.data.success) {
                            this.activeUser.group_description = newDesc;
                        }
                    })
                    .catch(err => console.error('Error updating description:', err));
            },

            addGroupMember(contact) {
                if (!this.activeUser || !this.activeUser.is_group) return;
                if (!this.activeUser.members) this.activeUser.members = [];
                if (this.activeUser.members.includes(contact.id)) return;
                const groupId = this.activeUser.group_db_id;
                axios.post('/chat/group/' + groupId + '/add-member', {
                        user_id: contact.id
                    })
                    .then(res => {
                        if (res.data.success) {
                            this.activeUser.members.push(contact.id);
                            this.showAddMembersModal = false;
                            this.showCreateChannelModal = false;
                            this.showDiscoverChannelsModal = false;
                            this.showCreateCommunityModal = false;
                            this.showAddGroupToCommunityModal = false;
                        }
                    })
                    .catch(err => console.error('Error adding member:', err));
            },

            removeGroupMember(memberId) {
                if (!this.activeUser || !this.activeUser.is_group) return;
                if (this.activeUser.admin_id !== this.currentUserId) return;
                if (!confirm('Remove ' + this.getMemberName(memberId) + ' from the group?')) return;
                const groupId = this.activeUser.group_db_id;
                axios.post('/chat/group/' + groupId + '/remove-member', {
                        user_id: memberId
                    })
                    .then(res => {
                        if (res.data.success) {
                            this.activeUser.members = this.activeUser.members.filter(id => id !== memberId);
                            this.activeUser.status_text = this.activeUser.members.length + ' members';
                            const chatItem = this.chatPreviews.find(c => c.id === this.activeUser.id);
                            if (chatItem) {
                                chatItem.status_text = this.activeUser.members.length + ' members';
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Error removing member:', err);
                        alert('Failed to remove member. ' + (err.response?.data?.error || ''));
                    });
            },

            exitGroup() {
                if (!this.activeUser || !this.activeUser.is_group) return;
                if (!confirm('Exit this group?')) return;
                const groupId = this.activeUser.group_db_id;
                axios.post('/chat/group/' + groupId + '/exit')
                    .then(res => {
                        if (res.data.success) {
                            this.chatPreviews = this.chatPreviews.filter(c => c.id !== this.activeUser.id);
                            this.activeUser = null;
                            this.showUserDetail = false;
                            this.filterChats();
                        }
                    })
                    .catch(err => console.error('Error exiting group:', err));
            },

            addSystemMessage(text) {
                this.messages.push({
                    id: 'sys_' + Date.now(),
                    sender_id: this.currentUserId,
                    message: text,
                    type: 'system',
                    time: new Date().toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    }),
                    tick_html: '',
                });
                this.$nextTick(() => this.scrollToBottom());
            },
            getAllStarredMessages() {
                const starred = [];
                // From dedicated starred array
                this.starredMessages.forEach(msg => {
                    const chat = this.chatPreviews.find(c => c.id === msg.sender_id || c.id === msg.receiver_id);
                    if (chat && !starred.find(s => s.id === msg.id)) {
                        starred.push({
                            ...msg,
                            chatName: chat.name,
                            chatAvatar: chat.avatar,
                            chatId: chat.id
                        });
                    }
                });
                // Also check current loaded messages for starred ones
                this.messages.forEach(msg => {
                    if (msg.is_starred && !starred.find(s => s.id === msg.id)) {
                        const chat = this.chatPreviews.find(c => c.id === msg.sender_id || c.id === msg.receiver_id);
                        if (chat) {
                            starred.push({
                                ...msg,
                                chatName: chat.name,
                                chatAvatar: chat.avatar,
                                chatId: chat.id
                            });
                        }
                    }
                });
                return starred;
            },

            // ============ CHANNELS ============
            loadChannels() {
                axios.get('/channels')
                    .then(res => {
                        this.myChannels = res.data.owned || [];
                        this.subscribedChannels = res.data.subscribed || [];
                    })
                    .catch(err => console.error('Error loading channels:', err));
            },

            loadDiscoverChannels() {
                axios.get('/channels/discover')
                    .then(res => {
                        this.discoverChannelsList = res.data.channels || [];
                    })
                    .catch(err => console.error(err));
            },

            createChannel() {
                if (!this.newChannelName.trim()) return;
                axios.post('/channels/create', {
                        name: this.newChannelName.trim(),
                        description: this.newChannelDesc.trim()
                    })
                    .then(res => {
                        if (res.data.success) {
                            this.myChannels.unshift(res.data.channel);
                            this.showCreateChannelModal = false;
                            this.newChannelName = '';
                            this.newChannelDesc = '';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Error creating channel');
                    });
            },

            subscribeChannel(ch) {
                axios.post('/channels/' + ch.id + '/subscribe')
                    .then(res => {
                        if (res.data.success) {
                            if (res.data.subscribed) {
                                this.subscribedChannels.push(ch);
                            } else {
                                this.subscribedChannels = this.subscribedChannels.filter(c => c.id !== ch.id);
                            }
                            this.discoverChannelsList = this.discoverChannelsList.filter(c => c.id !== ch.id);
                        }
                    })
                    .catch(err => console.error(err));
            },

            openChannelChat(ch) {
                this.activeChannel = ch;
                this.channelMessages = [];
                this.activeUser = null;
                axios.get('/channels/' + ch.id + '/messages')
                    .then(res => {
                        this.channelMessages = res.data.messages || [];
                    })
                    .catch(err => console.error(err));
            },

            sendChannelMessage() {
                if (!this.newMessageText.trim() || !this.activeChannel) return;
                const text = this.newMessageText;
                this.newMessageText = '';
                axios.post('/channels/send', {
                        channel_id: this.activeChannel.id,
                        message: text
                    })
                    .then(res => {
                        if (res.data.success) {
                            this.channelMessages.push(res.data.message);
                            this.$nextTick(() => {
                                const c = document.getElementById('messagesContainer');
                                if (c) c.scrollTop = c.scrollHeight;
                            });
                        }
                    })
                    .catch(err => console.error(err));
            },

            filterChannels() {
                if (!this.channelSearchQuery) return;
                const q = this.channelSearchQuery.toLowerCase();
                this.myChannels = this.myChannels.filter(c => c.name.toLowerCase().includes(q));
                this.subscribedChannels = this.subscribedChannels.filter(c => c.name.toLowerCase().includes(q));
            },

            // ============ COMMUNITIES ============
            loadCommunities() {
                axios.get('/communities')
                    .then(res => {
                        this.myCommunities = res.data.owned || [];
                        this.memberCommunities = res.data.member || [];
                    })
                    .catch(err => console.error('Error loading communities:', err));
            },

            createCommunity() {
                if (!this.newCommunityName.trim()) return;
                const formData = new FormData();
                formData.append('name', this.newCommunityName.trim());
                formData.append('description', this.newCommunityDesc.trim());
                if (this.communityAvatar) formData.append('avatar', this.communityAvatar);
                axios.post('/communities/create', formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    })
                    .then(res => {
                        if (res.data.success) {
                            this.myCommunities.unshift(res.data.community);
                            this.communityStep = 'list';
                            this.newCommunityName = '';
                            this.newCommunityDesc = '';
                            this.communityAvatar = null;
                            this.communityAvatarPreview = null;
                            this.openCommunityDetail(res.data.community);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Error creating community');
                    });
            },

            openCommunityDetail(comm) {
                axios.get('/communities/' + comm.id)
                    .then(res => {
                        this.activeCommunity = res.data.community;
                        this.activeChannel = null;
                        this.activeUser = null;
                        this.communityRightView = 'none';
                        this.communityAnnouncements = [];
                        this.loadAnnouncements();
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Could not load community');
                    });
            },

            closeCommunityDetail() {
                this.activeCommunity = null;
                this.activeUser = null;
                this.communityRightView = 'none';
                this.communityAnnouncements = [];
            },

            openCommunityAnnouncements() {
                if (!this.activeCommunity?.is_member) return;
                this.communityRightView = 'announcements';
                this.activeUser = null;
                this.loadAnnouncements();
            },

            openCommunityGroup(grp) {
                if (!this.activeCommunity?.is_member) return;
                this.communityRightView = 'group';
                let chatItem = this.chatPreviews.find(c => c.is_group && c.group_db_id === grp.id);
                if (!chatItem) {
                    chatItem = {
                        id: 'group_' + grp.id,
                        name: grp.name,
                        avatar: grp.avatar,
                        email: '',
                        phone: '',
                        about: 'Group · Community',
                        is_online: false,
                        status_text: grp.members_count + ' members',
                        unreadCount: 0,
                        is_typing: false,
                        last_message_preview: '',
                        last_message_time: '',
                        last_message_sender_self: false,
                        last_message_tick: '',
                        last_message_timestamp: 0,
                        is_archived: false,
                        is_muted: false,
                        is_pinned: false,
                        is_favorited: false,
                        is_blocked: false,
                        is_group: true,
                        group_db_id: grp.id,
                        admin_id: null,
                        members: [],
                        group_description: '',
                        community_id: this.activeCommunity.id,
                    };
                    this.chatPreviews.unshift(chatItem);
                    this.filterChats();
                } else {
                    chatItem.community_id = this.activeCommunity.id;
                }
                this.activeUser = chatItem;
                this.messages = [];
                this.hasMoreMessages = true;
                this.loadInitialMessages();
            },

            loadMyGroupsForCommunity() {
                axios.get('/my-groups')
                    .then(res => {
                        const ids = (this.activeCommunity?.groups || []).map(g => g.id);
                        this.myGroupsList = (res.data.groups || []).filter(g => !ids.includes(g.id));
                    })
                    .catch(err => console.error(err));
            },

            addGroupToCommunity(grp) {
                if (!this.activeCommunity) return;
                axios.post('/communities/' + this.activeCommunity.id + '/add-group', {
                        group_id: grp.id
                    })
                    .then(res => {
                        if (res.data.success) {
                            if (!this.activeCommunity.groups) this.activeCommunity.groups = [];
                            this.activeCommunity.groups.push(res.data.group);
                            this.activeCommunity.groups_count = this.activeCommunity.groups.length;
                            const chatItem = this.chatPreviews.find(c => c.group_db_id === grp.id);
                            if (chatItem) chatItem.community_id = this.activeCommunity.id;
                            this.showAddGroupToCommunityModal = false;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert(err.response?.data?.error || 'Could not add group');
                    });
            },

            removeGroupFromCommunity(groupId) {
                if (!this.activeCommunity || !confirm('Remove this group from community?')) return;
                axios.post('/communities/' + this.activeCommunity.id + '/remove-group', {
                        group_id: groupId
                    })
                    .then(res => {
                        if (res.data.success) {
                            this.activeCommunity.groups = this.activeCommunity.groups.filter(g => g.id !== groupId);
                            this.activeCommunity.groups_count = this.activeCommunity.groups.length;
                            const chatItem = this.chatPreviews.find(c => c.group_db_id === groupId);
                            if (chatItem) chatItem.community_id = null;
                            if (this.activeUser?.group_db_id === groupId) {
                                this.activeUser = null;
                                this.communityRightView = 'none';
                            }
                        }
                    })
                    .catch(err => console.error(err));
            },

            openExitCommunityModal(comm) {
                this.exitCommunityTarget = comm;
                this.showExitCommunityModal = true;
            },

            exitCommunity() {
                if (!this.exitCommunityTarget) return;
                const isOwner = this.exitCommunityTarget.is_owner;
                const url = isOwner ? '/communities/' + this.exitCommunityTarget.id : '/communities/' + this.exitCommunityTarget.id + '/leave';
                axios({
                        method: isOwner ? 'delete' : 'post',
                        url
                    })
                    .then(res => {
                        if (res.data.success) {
                            this.myCommunities = this.myCommunities.filter(c => c.id !== this.exitCommunityTarget.id);
                            this.memberCommunities = this.memberCommunities.filter(c => c.id !== this.exitCommunityTarget.id);
                            this.showExitCommunityModal = false;
                            this.exitCommunityTarget = null;
                            this.closeCommunityDetail();
                        }
                    })
                    .catch(err => console.error(err));
            },

            joinCommunity() {
                if (!this.activeCommunity) return;
                axios.post('/communities/' + this.activeCommunity.id + '/join')
                    .then(res => {
                        if (res.data.success) {
                            this.activeCommunity = res.data.community;
                            this.memberCommunities.push(res.data.community);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert(err.response?.data?.error || 'Could not join');
                    });
            },

            sendAnnouncement() {
                if (!this.activeCommunity || !this.communityAnnounceMessage.trim()) return;
                axios.post('/communities/' + this.activeCommunity.id + '/announce', {
                        message: this.communityAnnounceMessage.trim()
                    })
                    .then(res => {
                        if (res.data.success) {
                            this.communityAnnouncements.push(res.data.announcement);
                            this.communityAnnounceMessage = '';
                            this.$nextTick(() => {
                                const el = document.querySelector('.community-announcements-feed');
                                if (el) el.scrollTop = el.scrollHeight;
                            });
                        }
                    })
                    .catch(err => console.error(err));
            },

            loadAnnouncements() {
                if (!this.activeCommunity) return;
                axios.get('/communities/' + this.activeCommunity.id + '/announcements')
                    .then(res => {
                        this.communityAnnouncements = res.data.announcements || [];
                    })
                    .catch(err => console.error(err));
            },

            // ============ APP LOCK ============
            enableAppLock() {
                this.appLockError = '';
                if (this.appLockPin.length !== 4 || !/^\d{4}$/.test(this.appLockPin)) {
                    this.appLockError = 'PIN must be 4 digits';
                    return;
                }
                if (this.appLockPin !== this.appLockConfirmPin) {
                    this.appLockError = 'PINs do not match';
                    return;
                }
                localStorage.setItem('appLockPin', this.appLockPin);
                this.appLockEnabled = true;
                this.appLockPin = '';
                this.appLockConfirmPin = '';
                alert('App lock enabled!');
            },

            disableAppLock() {
                this.appLockError = '';
                const storedPin = localStorage.getItem('appLockPin');
                if (this.appLockPin !== storedPin) {
                    this.appLockError = 'Incorrect PIN';
                    return;
                }
                localStorage.removeItem('appLockPin');
                this.appLockEnabled = false;
                this.appLockPin = '';
                alert('App lock disabled!');
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
                this.chatContextMenuStyle = {
                    top: top + 'px',
                    left: left + 'px'
                };
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
                this.msgContextMenuStyle = {
                    top: top + 'px',
                    left: left + 'px'
                };
                this.msgContextMenuItem = msg;
            },

            // Message actions
            quickReact(msg) {
                msg._quickReact = true;
                setTimeout(() => {
                    msg._quickReact = false;
                }, 3000);
            },

            addReaction(emoji, msg = null) {
                const target = msg || this.msgContextMenuItem;
                if (!target) return;
                axios.post(`/messages/${target.id}/react`, { emoji })
                    .then(res => {
                        target.reaction = res.data.added ? emoji : null;
                    })
                    .catch(err => console.error(err));
                if (msg) msg._quickReact = false;
                this.msgContextMenuItem = null;
            },

            updateProfileInfo() {
                axios.post('http://localhost/profile', {
                    name: this.myUserName,
                    about: this.myUserAbout
                }).then(res => {
                    // Update client UI
                }).catch(err => console.error(err));
            },

            // Raw list of chat previews loaded from backend (JSON — avoids Blade/JS corruption)
            chatPreviews: window.__CHAT_INIT__.chatPreviews,

            filteredChats: [],
            contactList: [],
            messages: [],
            emojis: ['😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😣', '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠', '😡', '🤬', '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗', '🤔', '🤭', '🤫', '🤥', '😶', '😐', '😑', '😬', '🙄', '😯', '😦', '😧', '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐', '🥴', '🤢', '🤮', '🤧', '😷', '🤒', '🤕', '🤑', '🤠', '😈', '👿', '👹', '👺', '🤡', '💩', '👻', '💀', '☠️', '👽', '👾', '🤖', '🎃', '😺', '😸', '😹', '😻', '😼', '😽', '🙀', '😿', '😾', '👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤏', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💅', '🤳', '💪', '🦾', '🦿', '🦵', '🦶', '👂', '🦻', '👃', '🧠', '🦷', '🦴', '👀', '👁️', '👅', '👄', '💋', '🩸', '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟'],

            // Polling fallback for new messages
            lastPollMessageId: 0,
            pollGlobalLastId: window.__CHAT_INIT__.pollGlobalLastId,
            pollInterval: null,

            typingTimeout: null,
            lastTypingEventTime: 0,
            replyToMessage: null,
            forwardMessage: null,
            showForwardModal: false,
            showNewGroupModal: false,
            showStarredModal: false,
            showAppLockModal: false,
            selectMode: false,
            selectedChats: [],
            starredMessages: [],
            newGroupName: '',
            newGroupSelectedUsers: [],
            appLockPin: '',
            appLockConfirmPin: '',
            appLockError: '',
            appLockEnabled: false,
            editingGroupName: false,
            editingGroupNameValue: '',
            editingGroupDesc: false,
            editingGroupDescValue: '',
            showAddMembersModal: false,

            // Channels
            showCreateChannelModal: false,
            showDiscoverChannelsModal: false,
            newChannelName: '',
            newChannelDesc: '',
            myChannels: [],
            subscribedChannels: [],
            discoverChannelsList: [],
            channelSearchQuery: '',
            activeChannel: null,
            channelMessages: [],

            // Communities
            showCreateCommunityModal: false,
            showAddGroupToCommunityModal: false,
            communityStep: 'list',
            communityAvatar: null,
            communityAvatarPreview: null,
            newCommunityName: '',
            newCommunityDesc: '',
            myCommunities: [],
            memberCommunities: [],
            activeCommunity: null,
            communityRightView: 'none',
            myGroupsList: [],
            showExitCommunityModal: false,
            exitCommunityTarget: null,
            showCommunityMemberModal: false,
            communityAnnounceMessage: '',
            communityAnnouncements: [],

            initChat() {
                window.chatAppInstance = this;
                // Sort previews by last message timestamp
                this.reorderChatPreviews();
                this.filterChats();

                this.searchContacts();

                // Select active user if ?user= is in the query string
                const activeUserId = window.__CHAT_INIT__.activeUserId;
                if (activeUserId) {
                    const targetUser = this.chatPreviews.find(u => u.id === activeUserId);
                    if (targetUser) {
                        this.activeUser = targetUser;
                        this.loadInitialMessages();
                    }
                }

                // Listen to real-time events
                this.setupWebSocketListeners();

                // Start polling fallback for new messages (every 3 seconds)
                this.startPolling();

                // Update title with unread count
                this.updateDocumentTitle();

                // Init app lock state from localStorage
                this.appLockEnabled = !!localStorage.getItem('appLockPin');

                // Load starred messages from server
                axios.get('/messages/starred').then(res => {
                    this.starredMessages = res.data.messages || [];
                }).catch(() => {});

                // Subscribe to group channels for real-time
                this.chatPreviews.filter(c => c.is_group).forEach(c => {
                    this.subscribeToGroup(c.group_db_id);
                });

                // Load channels and communities
                this.loadChannels();
                this.loadCommunities();

                // Init audio context and request notification permission on first user gesture
                const firstClick = () => {
                    document.removeEventListener('click', firstClick);
                    document.removeEventListener('touchstart', firstClick);
                    this.initAudio();
                    this.requestNotificationPermission();
                };
                document.addEventListener('click', firstClick);
                document.addEventListener('touchstart', firstClick);
            },

            setFilter(filterName) {
                this.activeFilter = filterName;
                this.filterChats();
            },

            unreadTotalCount() {
                return this.chatPreviews.reduce((sum, chat) => sum + (chat.unreadCount || 0), 0);
            },

            // ============ NOTIFICATIONS ============

            // Shared AudioContext that resumes on user gesture
            audioCtx: null,

            initAudio() {
                try {
                    this.audioCtx = new(window.AudioContext || window.webkitAudioContext)();
                    if (this.audioCtx.state === 'suspended') {
                        this.audioCtx.resume();
                    }
                } catch (e) {}
            },

            requestNotificationPermission() {
                if ('Notification' in window && Notification.permission === 'default') {
                    Notification.requestPermission();
                }
            },

            playNotificationSound() {
                try {
                    if (!this.audioCtx) {
                        this.initAudio();
                    }
                    if (!this.audioCtx || this.audioCtx.state === 'closed') {
                        this.audioCtx = new(window.AudioContext || window.webkitAudioContext)();
                    }
                    if (this.audioCtx.state === 'suspended') {
                        this.audioCtx.resume();
                    }
                    const ctx = this.audioCtx;

                    const osc1 = ctx.createOscillator();
                    const gain1 = ctx.createGain();
                    osc1.type = 'sine';
                    osc1.frequency.setValueAtTime(880, ctx.currentTime);
                    osc1.frequency.setValueAtTime(660, ctx.currentTime + 0.08);
                    gain1.gain.setValueAtTime(0.3, ctx.currentTime);
                    gain1.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.25);
                    osc1.connect(gain1);
                    gain1.connect(ctx.destination);
                    osc1.start(ctx.currentTime);
                    osc1.stop(ctx.currentTime + 0.25);

                    const osc2 = ctx.createOscillator();
                    const gain2 = ctx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(1100, ctx.currentTime + 0.12);
                    osc2.frequency.setValueAtTime(880, ctx.currentTime + 0.2);
                    gain2.gain.setValueAtTime(0, ctx.currentTime);
                    gain2.gain.setValueAtTime(0.25, ctx.currentTime + 0.12);
                    gain2.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.38);
                    osc2.connect(gain2);
                    gain2.connect(ctx.destination);
                    osc2.start(ctx.currentTime + 0.12);
                    osc2.stop(ctx.currentTime + 0.38);
                } catch (e) {}
            },

            showBrowserNotification(senderId, senderName, messageText) {
                if ('Notification' in window && Notification.permission === 'granted') {
                    // Don't notify if user is actively chatting with THIS sender
                    if (document.hasFocus() && this.activeUser && this.activeUser.id === senderId) return;

                    const notif = new Notification(senderName, {
                        body: messageText,
                        icon: 'https://ui-avatars.com/api/?name=' + encodeURIComponent(senderName) + '&background=25D366&color=fff&size=80',
                        badge: 'https://ui-avatars.com/api/?name=W&background=25D366&color=fff&size=80',
                        tag: 'whatsapp-msg',
                        renotify: true,
                        requireInteraction: false,
                    });

                    notif.onclick = function() {
                        window.focus();
                        notif.close();
                    };

                    setTimeout(() => notif.close(), 5000);
                }
            },

            updateDocumentTitle() {
                const count = this.unreadTotalCount();
                document.title = count > 0 ? `(${count}) WhatsApp` : 'WhatsApp — Chats';
            },

            // ============ POLLING FALLBACK ============
            startPolling() {
                this.pollInterval = setInterval(() => {
                    this.pollNewMessages();
                }, 3000);
            },

            pollNewMessages() {
                try {
                    if (this.activeUser) {
                        this.pollActiveUserMessages();
                    } else {
                        this.pollGlobalMessages();
                    }
                } catch (e) {
                    console.error('Polling error:', e);
                }
            },

            pollActiveUserMessages() {
                if (!this.activeUser) return;

                if (this.activeUser.is_group) {
                    this.pollGroupMessages();
                    return;
                }

                axios.get(`/chat/new/${this.activeUser.id}/${this.lastPollMessageId}`)
                    .then(res => {
                        const newMsgs = res.data.messages;
                        if (!newMsgs || newMsgs.length === 0) return;

                        newMsgs.forEach(msg => {
                            if (msg.sender_id === this.currentUserId) return;
                            const exists = this.messages.find(m => m.id === msg.id);
                            if (exists) return;

                            msg.tick_html = '';
                            this.messages.push(msg);

                            if (msg.id > this.lastPollMessageId) {
                                this.lastPollMessageId = msg.id;
                            }

                            // Don't notify when actively viewing the sender's chat
                            if (!document.hasFocus() || this.activeUser.id !== msg.sender_id) {
                                this.playNotificationSound();
                                const senderName = this.chatPreviews.find(c => c.id === msg.sender_id)?.name || msg.sender_name || 'New Message';
                                this.showBrowserNotification(msg.sender_id, senderName, msg.message || '📎 File');
                            }
                            this.updateDocumentTitle();
                        });

                        this.scrollToBottom();

                        // Mark all messages from this sender as seen
                        this.markMessagesAsSeen(this.activeUser.id);

                        const lastMsg = newMsgs[newMsgs.length - 1];
                        const chatItem = this.chatPreviews.find(c => c.id === lastMsg.sender_id);
                        if (chatItem) {
                            chatItem.last_message_preview = lastMsg.type !== 'text' ? '📎 File' : lastMsg.message;
                            chatItem.last_message_time = lastMsg.time;
                            chatItem.last_message_sender_self = false;
                            chatItem.last_message_timestamp = Math.floor(Date.now() / 1000);
                            if (this.activeUser.id !== lastMsg.sender_id) {
                                chatItem.unreadCount = (chatItem.unreadCount || 0) + 1;
                            }
                            this.reorderChatPreviews();
                        }
                    })
                    .catch(() => {});
            },

            pollGroupMessages() {
                if (!this.activeUser || !this.activeUser.is_group) return;
                const groupId = this.activeUser.group_db_id;
                if (!groupId) return;

                axios.get(`/chat/group/${groupId}/new/${this.lastPollMessageId}`)
                    .then(res => {
                        const newMsgs = res.data.messages;
                        if (!newMsgs || newMsgs.length === 0) return;

                        newMsgs.forEach(msg => {
                            if (msg.sender_id === this.currentUserId) return;
                            const exists = this.messages.find(m => m.id === msg.id);
                            if (exists) return;

                            this.messages.push(msg);

                            if (msg.id > this.lastPollMessageId) {
                                this.lastPollMessageId = msg.id;
                            }
                            if (msg.id > this.pollGlobalLastId) {
                                this.pollGlobalLastId = msg.id;
                            }

                            // Don't notify when actively viewing the group chat
                            if (!document.hasFocus()) {
                                this.playNotificationSound();
                                const senderName = msg.sender_name || 'Someone';
                                this.showBrowserNotification(this.activeUser.id, this.activeUser.name, senderName + ': ' + (msg.message || '📎 File'));
                            }
                            this.updateDocumentTitle();
                        });

                        this.scrollToBottom();

                        if (newMsgs.length > 0) {
                            const lastMsg = newMsgs[newMsgs.length - 1];
                            const chatItem = this.chatPreviews.find(c => c.id === this.activeUser.id);
                            if (chatItem) {
                                chatItem.last_message_preview = lastMsg.sender_name + ': ' + (lastMsg.type !== 'text' ? '📎 File' : lastMsg.message);
                                chatItem.last_message_time = lastMsg.time;
                                chatItem.last_message_sender_self = false;
                                chatItem.last_message_timestamp = Math.floor(Date.now() / 1000);
                                this.reorderChatPreviews();
                            }
                        }
                    })
                    .catch(() => {});
            },

            pollGlobalMessages() {
                axios.get(`/chat/global-new/${this.pollGlobalLastId}`)
                    .then(res => {
                        const newMsgs = res.data.messages;
                        if (!newMsgs || newMsgs.length === 0) return;

                        const prevCount = this.unreadTotalCount();
                        let hasNewIncoming = false;
                        let notifCount = 0;

                        newMsgs.forEach(msg => {
                            if (msg.sender_id === this.currentUserId) return;
                            if (msg.id > this.pollGlobalLastId) {
                                this.pollGlobalLastId = msg.id;
                            }

                            hasNewIncoming = true;

                            if (msg.group_id) {
                                const groupId = 'group_' + msg.group_id;
                                const chatItem = this.chatPreviews.find(c => c.id === groupId);
                                const senderName = msg.sender_name || 'Someone';
                                if (chatItem) {
                                    chatItem.last_message_preview = senderName + ': ' + (msg.type !== 'text' ? '📎 File' : msg.message);
                                    chatItem.last_message_time = msg.time;
                                    chatItem.last_message_sender_self = false;
                                    chatItem.last_message_timestamp = Math.floor(Date.now() / 1000);
                                    chatItem.unreadCount = (chatItem.unreadCount || 0) + 1;
                                    if (notifCount < 3) {
                                        this.showBrowserNotification(groupId, chatItem.name, senderName + ': ' + (msg.message || '📎 File'));
                                        notifCount++;
                                    }
                                }
                            } else {
                                const chatItem = this.chatPreviews.find(c => c.id === msg.sender_id);
                                const senderName = chatItem ? chatItem.name : msg.sender_name || 'New Message';
                                if (notifCount < 3) {
                                    this.showBrowserNotification(msg.sender_id, senderName, msg.message || '📎 File');
                                    notifCount++;
                                }

                                if (chatItem) {
                                    chatItem.last_message_preview = msg.type !== 'text' ? '📎 File' : msg.message;
                                    chatItem.last_message_time = msg.time;
                                    chatItem.last_message_sender_self = false;
                                    chatItem.last_message_timestamp = Math.floor(Date.now() / 1000);
                                    chatItem.unreadCount = (chatItem.unreadCount || 0) + 1;
                                }
                            }
                        });

                        // Play sound only once if there are new incoming messages
                        if (hasNewIncoming) {
                            this.playNotificationSound();
                        }
                        this.updateDocumentTitle();

                        if (this.unreadTotalCount() !== prevCount) {
                            this.reorderChatPreviews();
                        }
                    })
                    .catch(() => {});
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
                    chats = chats.filter(chat => chat.is_group);
                } else if (this.activeFilter === 'communities') {
                    chats = chats.filter(chat => chat.is_group && chat.community_id);
                }

                chats.sort((a, b) => (b.is_pinned ? 1 : 0) - (a.is_pinned ? 1 : 0));

                this.filteredChats = chats;
            },

            selectChat(userId) {
                const targetUser = this.chatPreviews.find(u => u.id === userId);

                // Select mode: toggle selection instead of opening chat
                if (this.selectMode && targetUser) {
                    this.toggleChatSelection(targetUser);
                    return;
                }

                // Update URL parameter without reload
                const url = new URL(window.location);
                url.searchParams.set('user', userId);
                window.history.pushState({}, '', url);

                if (targetUser) {
                    this.activeUser = targetUser;
                    if (!targetUser.is_group) {
                        axios.post(`/chat/seen/${targetUser.id}`).catch(err => console.error(err));
                    }
                    this.activeUser.unreadCount = 0;
                    this.showUserDetail = false;
                    this.newMessageText = '';
                    this.selectedFile = null;
                    this.hasMoreMessages = true;
                    this.messages = [];
                    this.loadInitialMessages();
                    this.updateDocumentTitle();
                    this.requestNotificationPermission();
                }
            },

            loadInitialMessages() {
                if (!this.activeUser) return;

                // Group chat: load from group endpoint
                if (this.activeUser.is_group) {
                    const groupId = this.activeUser.group_db_id;
                    if (!groupId) return;
                    axios.get(`/chat/group/${groupId}/messages`)
                        .then(response => {
                            this.messages = response.data.messages;
                            this.scrollToBottom();
                            if (this.messages.length > 0) {
                                const maxId = Math.max(...this.messages.map(m => m.id));
                                this.lastPollMessageId = maxId;
                                if (maxId > this.pollGlobalLastId) this.pollGlobalLastId = maxId;
                            }
                        })
                        .catch(error => console.error('Error loading group messages:', error));
                    return;
                }

                // Normal chat
                axios.get(`/chat/more/${this.activeUser.id}`)
                    .then(response => {
                        this.messages = response.data.messages;
                        this.hasMoreMessages = response.data.has_more;
                        this.scrollToBottom();
                        if (this.messages.length > 0) {
                            const maxId = Math.max(...this.messages.map(m => m.id));
                            this.lastPollMessageId = maxId;
                            if (maxId > this.pollGlobalLastId) {
                                this.pollGlobalLastId = maxId;
                            }
                        }
                        this.markMessagesAsSeen(this.activeUser.id);
                    })
                    .catch(error => {
                        console.error('Error loading initial messages:', error);
                    });
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    const container = document.getElementById('messagesContainer');
                    if (container && container.offsetParent !== null) {
                        container.scrollTop = container.scrollHeight;
                    }
                });
            },

            scrollToMessage(messageId) {
                if (!messageId) return;
                this.$nextTick(() => {
                    const el = document.querySelector(`[data-msg-id="${messageId}"]`);
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        el.classList.add('msg-highlight');
                        setTimeout(() => el.classList.remove('msg-highlight'), 1500);
                    }
                });
            },

            handleScroll(e) {
                const container = e.target;

                if (container.scrollTop === 0 && this.hasMoreMessages && !this.loadingOlder && this.messages.length > 0 && this.activeUser && !this.activeUser.is_group) {
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
                let chatItem = this.chatPreviews.find(c => c.id === contact.id);
                if (!chatItem) {
                    chatItem = {
                        id: contact.id,
                        name: contact.name,
                        avatar: contact.avatar,
                        email: contact.email || '',
                        phone: contact.phone || '',
                        about: contact.about || '',
                        is_online: contact.is_online,
                        status_text: contact.last_seen || 'last seen recently',
                        unreadCount: 0,
                        is_typing: false,
                        last_message_preview: '',
                        last_message_time: '',
                        last_message_sender_self: false,
                        last_message_tick: '',
                        last_message_timestamp: 0,
                        is_archived: false,
                        is_muted: false,
                        is_pinned: false,
                        is_favorited: false,
                        is_blocked: false,
                        is_group: false,
                    };
                    this.chatPreviews.unshift(chatItem);
                    this.filterChats();
                }
                this.selectChat(contact.id);
            },

            handleInput() {
                if (!this.activeUser || this.activeUser.is_group) return;
                const now = Date.now();
                if (now - this.lastTypingEventTime > 3000) {
                    this.lastTypingEventTime = now;
                    axios.post('/chat/typing', {
                        receiver_id: this.activeUser.id,
                        is_typing: true
                    }).catch(e => {});
                }
                clearTimeout(this.typingTimeout);
                this.typingTimeout = setTimeout(() => {
                    if (this.activeUser && !this.activeUser.is_group) {
                        axios.post('/chat/typing', {
                            receiver_id: this.activeUser.id,
                            is_typing: false
                        }).catch(e => {});
                    }
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
                if (this.showUserDetail && this.activeUser && this.activeUser.is_group) {
                    this.editingGroupName = false;
                    this.editingGroupNameValue = this.activeUser.name;
                    this.editingGroupDesc = false;
                    this.editingGroupDescValue = this.activeUser.group_description || '';
                }
            },

            openLightbox(url) {
                this.lightboxImage = url;
            },

            formatFileSize(bytes) {
                if (bytes >= 1048576) return Math.round(bytes / 1048576 * 10) / 10 + ' MB';
                if (bytes >= 1024) return Math.round(bytes / 1024 * 10) / 10 + ' KB';
                return bytes + ' B';
            },

            saveChatPreference(chat, fields) {
                const targetType = chat.is_group ? 'group' : 'user';
                const targetId = chat.is_group ? chat.group_db_id : chat.id;
                axios.post('/chat-preferences', {
                    target_type: targetType,
                    target_id: targetId,
                    ...fields,
                }).catch(err => console.error('Preference save failed:', err));
            },

            submitMessage() {
                if (!this.newMessageText.trim() && !this.selectedFile) return;

                const tempText = this.newMessageText;
                const replyTo = this.replyToMessage;
                this.newMessageText = '';
                const fileSent = this.selectedFile;
                this.cancelFileSelect();
                this.replyToMessage = null;

                // Group message
                if (this.activeUser.is_group) {
                    const groupId = this.activeUser.group_db_id;
                    const formData = new FormData();
                    formData.append('group_id', groupId);
                    if (tempText.trim()) formData.append('message', tempText.trim());
                    if (fileSent) formData.append('file', fileSent);
                    if (replyTo) formData.append('reply_to_id', replyTo.id);

                    axios.post('/chat/group/send', formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }).then(res => {
                        if (res.data.success) {
                            const newMsgObj = res.data.message;
                            this.messages.push(newMsgObj);
                            this.scrollToBottom();
                            if (newMsgObj.id > this.lastPollMessageId) this.lastPollMessageId = newMsgObj.id;
                            if (newMsgObj.id > this.pollGlobalLastId) this.pollGlobalLastId = newMsgObj.id;

                            const chatItem = this.chatPreviews.find(c => c.id === this.activeUser.id);
                            if (chatItem) {
                                chatItem.last_message_preview = tempText;
                                chatItem.last_message_time = newMsgObj.time;
                                chatItem.last_message_sender_self = true;
                                chatItem.last_message_tick = '';
                                chatItem.last_message_timestamp = Math.floor(Date.now() / 1000);
                                this.reorderChatPreviews();
                            }
                        }
                    }).catch(err => console.error(err));
                    return;
                }

                // Normal message
                const formData = new FormData();
                formData.append('receiver_id', this.activeUser.id);

                if (tempText.trim()) {
                    formData.append('message', tempText.trim());
                }
                if (fileSent) {
                    formData.append('file', fileSent);
                }
                if (replyTo) {
                    formData.append('reply_to_id', replyTo.id);
                }

                axios.post('/chat/send', formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    })
                    .then(res => {
                        if (res.data.success) {
                            const newMsgObj = res.data.message;
                            newMsgObj.sender_id = this.currentUserId;
                            this.messages.push(newMsgObj);
                            this.scrollToBottom();
                            if (newMsgObj.id > this.lastPollMessageId) {
                                this.lastPollMessageId = newMsgObj.id;
                            }
                            if (newMsgObj.id > this.pollGlobalLastId) {
                                this.pollGlobalLastId = newMsgObj.id;
                            }

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

            subscribeToGroup(groupId) {
                if (!groupId || typeof window.Echo === 'undefined') return;
                if (this._groupChannels && this._groupChannels.includes(groupId)) return;
                if (!this._groupChannels) this._groupChannels = [];
                this._groupChannels.push(groupId);
                try {
                    window.Echo.private(`group.${groupId}`)
                        .listen('.GroupMessageSent', (data) => {
                            if (data.sender_id === this.currentUserId) return;
                            if (this.activeUser && this.activeUser.is_group && this.activeUser.group_db_id === groupId) {
                                this.messages.push(data);
                                this.scrollToBottom();
                            }
                            const chatItem = this.chatPreviews.find(c => c.group_db_id === groupId);
                            if (chatItem) {
                                chatItem.last_message_preview = data.type !== 'text' ? '📎 File' : data.message;
                                chatItem.last_message_time = data.time;
                                chatItem.last_message_sender_self = false;
                                chatItem.last_message_timestamp = Math.floor(Date.now() / 1000);
                                if (!this.activeUser || this.activeUser.group_db_id !== groupId) {
                                    chatItem.unreadCount = (chatItem.unreadCount || 0) + 1;
                                }
                                this.reorderChatPreviews();
                            }
                            if (data.id > this.pollGlobalLastId) this.pollGlobalLastId = data.id;
                        });
                } catch (e) {
                    console.error('Group WS error:', e);
                }
            },

            setupWebSocketListeners() {
                if (typeof window.Echo === 'undefined') {
                    setTimeout(() => this.setupWebSocketListeners(), 2000);
                    return;
                }
                try {
                    // Private user channel for receiving chats/notifications
                    window.Echo.private(`chat.${this.currentUserId}`)
                        .listen('.MessageSent', (data) => {
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

                            // ============ TRIGGER NOTIFICATIONS ============
                            // Don't notify when actively viewing the sender's chat
                            if (!document.hasFocus() || !this.activeUser || this.activeUser.id !== data.sender_id) {
                                const senderChat = this.chatPreviews.find(c => c.id === data.sender_id);
                                const senderName = senderChat ? senderChat.name : 'New Message';
                                const previewText = data.type !== 'text' ? '📎 File' : data.message;

                                this.playNotificationSound();
                                this.showBrowserNotification(data.sender_id, senderName, previewText);
                            }

                            // Update title bar
                            this.updateDocumentTitle();

                            // Track latest message ID for polling
                            if (data.id > this.lastPollMessageId) {
                                this.lastPollMessageId = data.id;
                            }
                            if (data.id > this.pollGlobalLastId) {
                                this.pollGlobalLastId = data.id;
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
                                            msg.tick_html = '<svg class="tick-svg tick-seen" viewBox="0 0 16 11" width="16" height="11"><path d="M11.071.653a.457.457 0 0 0-.304-.102-.493.493 0 0 0-.381.178l-6.19 7.636-2.011-2.095a.463.463 0 0 0-.353-.145.47.47 0 0 0-.335.136.474.474 0 0 0-.016.678l2.375 2.459a.447.447 0 0 0 .347.156h.014a.472.472 0 0 0 .352-.176l6.544-8.058a.448.448 0 0 0-.042-.665z" fill="currentColor"/><path d="M15.071.653a.457.457 0 0 0-.304-.102-.493.493 0 0 0-.381.178l-6.19 7.636-1.2-1.251-.352.435 1.202 1.258a.447.447 0 0 0 .347.156h.014a.472.472 0 0 0 .352-.176l6.544-8.058a.448.448 0 0 0-.042-.665l-.088-.412z" fill="currentColor"/></svg>';
                                        } else if (data.status === 'delivered' && msg.status === 'sent') {
                                            msg.status = 'delivered';
                                            msg.tick_html = '<svg class="tick-svg tick-delivered" viewBox="0 0 16 11" width="16" height="11"><path d="M11.071.653a.457.457 0 0 0-.304-.102-.493.493 0 0 0-.381.178l-6.19 7.636-2.011-2.095a.463.463 0 0 0-.353-.145.47.47 0 0 0-.335.136.474.474 0 0 0-.016.678l2.375 2.459a.447.447 0 0 0 .347.156h.014a.472.472 0 0 0 .352-.176l6.544-8.058a.448.448 0 0 0-.042-.665z" fill="currentColor"/><path d="M15.071.653a.457.457 0 0 0-.304-.102-.493.493 0 0 0-.381.178l-6.19 7.636-1.2-1.251-.352.435 1.202 1.258a.447.447 0 0 0 .347.156h.014a.472.472 0 0 0 .352-.176l6.544-8.058a.448.448 0 0 0-.042-.665l-.088-.412z" fill="currentColor"/></svg>';
                                        }
                                    }
                                });
                            }

                            // Update status ticks in preview list
                            const chatItem = this.chatPreviews.find(c => c.id === data.receiver_id);
                            if (chatItem) {
                                if (data.status === 'seen') {
                                    chatItem.last_message_tick = '<svg class="tick-svg tick-seen" viewBox="0 0 16 11" width="16" height="11"><path d="M11.071.653a.457.457 0 0 0-.304-.102-.493.493 0 0 0-.381.178l-6.19 7.636-2.011-2.095a.463.463 0 0 0-.353-.145.47.47 0 0 0-.335.136.474.474 0 0 0-.016.678l2.375 2.459a.447.447 0 0 0 .347.156h.014a.472.472 0 0 0 .352-.176l6.544-8.058a.448.448 0 0 0-.042-.665z" fill="currentColor"/><path d="M15.071.653a.457.457 0 0 0-.304-.102-.493.493 0 0 0-.381.178l-6.19 7.636-1.2-1.251-.352.435 1.202 1.258a.447.447 0 0 0 .347.156h.014a.472.472 0 0 0 .352-.176l6.544-8.058a.448.448 0 0 0-.042-.665l-.088-.412z" fill="currentColor"/></svg>';
                                } else if (data.status === 'delivered') {
                                    chatItem.last_message_tick = '<svg class="tick-svg tick-delivered" viewBox="0 0 16 11" width="16" height="11"><path d="M11.071.653a.457.457 0 0 0-.304-.102-.493.493 0 0 0-.381.178l-6.19 7.636-2.011-2.095a.463.463 0 0 0-.353-.145.47.47 0 0 0-.335.136.474.474 0 0 0-.016.678l2.375 2.459a.447.447 0 0 0 .347.156h.014a.472.472 0 0 0 .352-.176l6.544-8.058a.448.448 0 0 0-.042-.665z" fill="currentColor"/><path d="M15.071.653a.457.457 0 0 0-.304-.102-.493.493 0 0 0-.381.178l-6.19 7.636-1.2-1.251-.352.435 1.202 1.258a.447.447 0 0 0 .347.156h.014a.472.472 0 0 0 .352-.176l6.544-8.058a.448.448 0 0 0-.042-.665l-.088-.412z" fill="currentColor"/></svg>';
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
                } catch (e) {
                    console.error('WebSocket setup failed, will retry:', e);
                    setTimeout(() => this.setupWebSocketListeners(), 3000);
                }
            }
        };
    }
</script>