<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\ChatSession;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ChatSessionController extends Controller
{
    public function show(ChatSession $chatSession)
    {
        $messages = $chatSession->messages;

        $messages->load('user');

        Message::query()
            ->where('chat_session_id', $chatSession->id)
            ->where('sender_id', '!=', Auth::id())
            ->update(['read_at' => now()]);

        return Inertia::render('ChatSession', [
            'messages' => $messages,
            'chatSession' => $chatSession->load(['creator', 'users']),
        ]);
    }

    public function store(Request $request)
    {
        if ($request->type == 'normal') {
            $request->validate([
                'email' => ['required', 'email', 'exists:users'],
            ]);

            $user = User::where('email', $request->email)->first();

            $chat_session = ChatSession::create(['creator_id' => Auth::id(), 'type' => $request->type]);

            $chat_session->users()->attach([$user->id, Auth::id()]);
        } else {
            $request->validate([
                'name' => ['required', 'string'],
            ]);

            $chat_session = ChatSession::create(['creator_id' => Auth::id(), 'name' => $request->name, 'type' => $request->type, 'active_at' => now()]);

            $chat_session->users()->attach(Auth::id());
        }

        return Redirect::back()->with('success', 'Contact added!');
    }
}
