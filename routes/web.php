<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home — redirect to login or chat
Route::get('/', function () {
    return auth()->check() ? redirect()->route('chat') : redirect()->route('login');
});

// -----------------------------------------------------------------------
// Auth Routes (Guest Only)
// -----------------------------------------------------------------------
Route::middleware('guest')->group(function () {

    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');

    // Register
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

    // Forgot Password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
});

// -----------------------------------------------------------------------
// Logout (Auth Only)
// -----------------------------------------------------------------------
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// -----------------------------------------------------------------------
// Protected Routes
// -----------------------------------------------------------------------
Route::middleware('auth')->group(function () {
    // Chat routes
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat');
    Route::post('/chat/send', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/chat/seen/{senderId}', [App\Http\Controllers\ChatController::class, 'markSeen'])->name('chat.seen');
    Route::get('/chat/more/{userId}', [App\Http\Controllers\ChatController::class, 'loadMore'])->name('chat.more');
    Route::get('/chat/new/{userId}/{afterId}', [App\Http\Controllers\ChatController::class, 'loadNew'])->name('chat.new');
    Route::get('/chat/global-new/{afterId}', [App\Http\Controllers\ChatController::class, 'loadAllNew'])->name('chat.new.global');
    Route::get('/chat/group/{groupId}/new/{afterId}', [App\Http\Controllers\ChatController::class, 'loadNewGroupMessages'])->name('chat.group.new');
    Route::post('/chat/typing', [App\Http\Controllers\ChatController::class, 'typing'])->name('chat.typing');
    // Group management
    Route::post('/chat/group/create', [App\Http\Controllers\ChatController::class, 'createGroup'])->name('chat.group.create');
    Route::post('/chat/group/send', [App\Http\Controllers\ChatController::class, 'sendGroupMessage'])->name('chat.group.send');
    Route::get('/chat/group/{groupId}/messages', [App\Http\Controllers\ChatController::class, 'loadGroupMessages'])->name('chat.group.messages');
    Route::post('/chat/group/{groupId}/update-name', [App\Http\Controllers\ChatController::class, 'updateGroupName'])->name('chat.group.updateName');
    Route::post('/chat/group/{groupId}/update-desc', [App\Http\Controllers\ChatController::class, 'updateGroupDesc'])->name('chat.group.updateDesc');
    Route::post('/chat/group/{groupId}/add-member', [App\Http\Controllers\ChatController::class, 'addGroupMember'])->name('chat.group.addMember');
    Route::post('/chat/group/{groupId}/remove-member', [App\Http\Controllers\ChatController::class, 'removeGroupMember'])->name('chat.group.removeMember');
    Route::post('/chat/group/{groupId}/exit', [App\Http\Controllers\ChatController::class, 'exitGroup'])->name('chat.group.exit');
    Route::post('/chat/group/{groupId}/avatar', [App\Http\Controllers\ChatController::class, 'updateGroupAvatar'])->name('chat.group.avatar');

    // Channel routes
    Route::get('/channels', [App\Http\Controllers\ChannelController::class, 'index'])->name('channels.index');
    Route::post('/channels/create', [App\Http\Controllers\ChannelController::class, 'create'])->name('channels.create');
    Route::post('/channels/{channelId}/subscribe', [App\Http\Controllers\ChannelController::class, 'subscribe'])->name('channels.subscribe');
    Route::post('/channels/send', [App\Http\Controllers\ChannelController::class, 'sendMessage'])->name('channels.send');
    Route::get('/channels/{channelId}/messages', [App\Http\Controllers\ChannelController::class, 'loadMessages'])->name('channels.messages');
    Route::get('/channels/discover', [App\Http\Controllers\ChannelController::class, 'discover'])->name('channels.discover');
    Route::post('/channels/{channelId}/update', [App\Http\Controllers\ChannelController::class, 'update'])->name('channels.update');
    Route::delete('/channels/{channelId}', [App\Http\Controllers\ChannelController::class, 'delete'])->name('channels.delete');

    // Community routes
    Route::get('/communities', [App\Http\Controllers\CommunityController::class, 'index'])->name('communities.index');
    Route::get('/communities/{communityId}', [App\Http\Controllers\CommunityController::class, 'show'])->name('communities.show');
    Route::post('/communities/create', [App\Http\Controllers\CommunityController::class, 'create'])->name('communities.create');
    Route::post('/communities/{communityId}/add-group', [App\Http\Controllers\CommunityController::class, 'addGroup'])->name('communities.addGroup');
    Route::post('/communities/{communityId}/remove-group', [App\Http\Controllers\CommunityController::class, 'removeGroup'])->name('communities.removeGroup');
    Route::post('/communities/{communityId}/add-member', [App\Http\Controllers\CommunityController::class, 'addMember'])->name('communities.addMember');
    Route::post('/communities/{communityId}/remove-member', [App\Http\Controllers\CommunityController::class, 'removeMember'])->name('communities.removeMember');
    Route::post('/communities/{communityId}/leave', [App\Http\Controllers\CommunityController::class, 'leave'])->name('communities.leave');
    Route::post('/communities/{communityId}/update', [App\Http\Controllers\CommunityController::class, 'update'])->name('communities.update');
    Route::delete('/communities/{communityId}', [App\Http\Controllers\CommunityController::class, 'delete'])->name('communities.delete');
    Route::post('/communities/{communityId}/join', [App\Http\Controllers\CommunityController::class, 'join'])->name('communities.join');
    Route::post('/communities/{communityId}/announce', [App\Http\Controllers\CommunityController::class, 'announce'])->name('communities.announce');
    Route::get('/communities/{communityId}/announcements', [App\Http\Controllers\CommunityController::class, 'announcements'])->name('communities.announcements');

    // My Groups endpoint for community add-group
    Route::get('/my-groups', [App\Http\Controllers\ChatController::class, 'myGroups'])->name('my.groups');

    // User/Contact routes
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('/profile', [App\Http\Controllers\UserController::class, 'profile'])->name('profile');
    Route::post('/profile', [App\Http\Controllers\UserController::class, 'updateProfile'])->name('profile.update');
    Route::post('/online-status', [App\Http\Controllers\UserController::class, 'updateOnlineStatus'])->name('online.status');
    Route::post('/offline-status', [App\Http\Controllers\UserController::class, 'goOffline'])->name('offline.status');
    Route::post('/clean-stale-online', [App\Http\Controllers\UserController::class, 'cleanStaleOnlineStatuses'])->name('online.clean');

    // Status routes
    Route::get('/status', [App\Http\Controllers\StatusController::class, 'index'])->name('status.index');
    Route::post('/status', [App\Http\Controllers\StatusController::class, 'store'])->name('status.store');
    Route::delete('/status/{id}', [App\Http\Controllers\StatusController::class, 'destroy'])->name('status.destroy');
    Route::post('/status/view/{statusId}', [App\Http\Controllers\StatusController::class, 'markViewed'])->name('status.view');

    // Message actions
    Route::put('/messages/{messageId}', [App\Http\Controllers\MessageController::class, 'edit'])->name('messages.edit');
    Route::delete('/messages/{messageId}', [App\Http\Controllers\MessageController::class, 'delete'])->name('messages.delete');
    Route::post('/messages/{messageId}/star', [App\Http\Controllers\MessageController::class, 'star'])->name('messages.star');
    Route::post('/messages/{messageId}/react', [App\Http\Controllers\MessageController::class, 'react'])->name('messages.react');
    Route::get('/messages/starred', [App\Http\Controllers\MessageController::class, 'starred'])->name('messages.starred');
    Route::get('/messages/search', [App\Http\Controllers\MessageController::class, 'search'])->name('messages.search');

    // Chat preferences (pin, mute, archive, favorite, block)
    Route::get('/chat-preferences', [App\Http\Controllers\ChatPreferenceController::class, 'index'])->name('chat.preferences');
    Route::post('/chat-preferences', [App\Http\Controllers\ChatPreferenceController::class, 'update'])->name('chat.preferences.update');
});
