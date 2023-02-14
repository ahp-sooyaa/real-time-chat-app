<?php

use App\Events\MessageSent;
use App\Http\Controllers\ProfileController;
use App\Models\Chat;
use App\Models\ChatSession;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
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

Route::post('/contact', function (Request $request) {
    $user = User::where('email', $request->email)->first();

    $chat_session = ChatSession::create();

    $chat_session->users()->attach([$user->id, Auth::id()]);
    // Auth::user()->chatSessions()->attach($chat_session->id);

    return back();
})->name('contact.store');

Route::get('/chatsession/{chatSession}', function (ChatSession $chatSession) {
    return Inertia::render('ChatSession', [
        'chats' => $chatSession->chats,
        'sessionId' => $chatSession->id,
    ]);
})->name('chatsession.show');

Route::post('/message', function (Request $request) {
    $chat = Chat::create([
        'sender_id' => Auth::id(),
        'chat_session_id' => $request->chatSessionId,
        'message' => $request->message
    ]);

    MessageSent::dispatch($chat);

    // return redirect()->back();
})->name('message.store');

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    $chat_sessions = Auth::user()->chatSessions;

    $chat_sessions->load([
        'users' => function ($query) {
            $query->where('name', '!=', Auth::user()->name);
        },
        'chats' => function ($query) {
            $query->latest()->first();
        }
    ]);

    // dd($chat_sessions);

    return Inertia::render('Dashboard', [
        'chatSessions' => $chat_sessions
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
