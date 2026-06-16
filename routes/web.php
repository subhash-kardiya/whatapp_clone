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
    Route::post('/chat/typing', [App\Http\Controllers\ChatController::class, 'typing'])->name('chat.typing');
    Route::post('/chat/group/create', [App\Http\Controllers\ChatController::class, 'createGroup'])->name('chat.group.create');
    Route::post('/chat/group/send', [App\Http\Controllers\ChatController::class, 'sendGroupMessage'])->name('chat.group.send');
    Route::get('/chat/group/{groupId}/messages', [App\Http\Controllers\ChatController::class, 'loadGroupMessages'])->name('chat.group.messages');

    // User/Contact routes
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('/profile', [App\Http\Controllers\UserController::class, 'profile'])->name('profile');
    Route::post('/profile', [App\Http\Controllers\UserController::class, 'updateProfile'])->name('profile.update');
    Route::post('/online-status', [App\Http\Controllers\UserController::class, 'updateOnlineStatus'])->name('online.status');

    // Status routes
    Route::get('/status', [App\Http\Controllers\StatusController::class, 'index'])->name('status.index');
    Route::post('/status', [App\Http\Controllers\StatusController::class, 'store'])->name('status.store');
    Route::delete('/status/{id}', [App\Http\Controllers\StatusController::class, 'destroy'])->name('status.destroy');
    Route::post('/status/view/{statusId}', [App\Http\Controllers\StatusController::class, 'markViewed'])->name('status.view');
});
