<?php

use App\Http\Controllers\ChatSessionController;
use App\Http\Controllers\ChatSessionMemberController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
        'chatSessions' => Auth::user()->loadChatSessions()
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/chat-session', [ChatSessionController::class, 'store'])->name('chatsession.store');
    Route::get('/chat-session/{chatSession}', [ChatSessionController::class, 'show'])->name('chatsession.show');
    Route::post('/message', [MessageController::class, 'store'])->name('message.store');

    Route::post('/chat-session/members', [ChatSessionMemberController::class, 'store'])->name('chatsession.member.store');
    Route::delete('/chat-session/{chatSession}/members/{member}', [ChatSessionMemberController::class, 'destroy'])->name('chatsession.member.destroy');
    Route::patch('/chat-session/{chatSession}/member/{member}', [ChatSessionMemberController::class, 'update'])->name('chatsession.member.update');
    
    Route::get('/chat-session/{chatSession}/setting', [ChatSessionController::class, 'edit'])->name('chatsession.setting.edit');
    Route::patch('/chat-session/{chatSession}/setting', [ChatSessionController::class, 'update'])->name('chatsession.setting.update');
});

require __DIR__ . '/auth.php';
