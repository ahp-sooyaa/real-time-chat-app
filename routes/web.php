<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatSessionController;
use App\Http\Controllers\ChatSessionMemberController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/chats', function () {
    return Inertia::render('Dashboard', [
        'chatSessions' => Auth::user()->loadChatSessions()
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // display route to add friend with qr code or link
    Route::get('/users/{user}/qr-code/regenerate', [QrCodeController::class, 'store'])->name('qrCode.regenerate');
    Route::get('/users/{user}/qr-code/{token}', [ChatSessionMemberController::class, 'addFriendWithQrCodeOrLink'])->name('friend.add');

    // add friend, create group
    Route::post('/chat-session', [ChatSessionController::class, 'store'])->name('chatsession.store');
    Route::get('/chat-session/{chatSession}', [ChatSessionController::class, 'show'])->name('chatsession.show');

    // send message
    Route::post('/message', [MessageController::class, 'store'])->name('message.store');

    // group chat member
    Route::post('/chat-session/members', [ChatSessionMemberController::class, 'store'])->name('chatsession.member.store');
    Route::delete('/chat-session/{chatSession}/members/{member}', [ChatSessionMemberController::class, 'destroy'])->name('chatsession.member.destroy');
    Route::patch('/chat-session/{chatSession}/member/{member}', [ChatSessionMemberController::class, 'update'])->name('chatsession.member.update');

    // setting
    Route::get('/chat-session/{chatSession}/setting', [ChatSessionController::class, 'edit'])->name('chatsession.setting.edit');
    Route::patch('/chat-session/{chatSession}/setting', [ChatSessionController::class, 'update'])->name('chatsession.setting.update');

    // search friend to add in group
    Route::get('/search', [UserController::class, 'index'])->name('user.index');
});

require __DIR__ . '/auth.php';
