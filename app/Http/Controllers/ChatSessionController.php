<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\ChatSession;
use App\Models\Message;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ChatSessionController extends Controller
{
    public function edit(ChatSession $chatSession)
    {
        return Inertia::render('Setting/Edit', [
            'chatSession' => $chatSession->load('users'),
        ]);
    }

    public function update(ChatSession $chatSession, Request $request)
    {
        $chatSession->update([
            'name' => $request->name,
        ]);
    }

    public function show(ChatSession $chatSession)
    {
        abort_if(!$chatSession->users->contains(Auth::id()), 404);

        // $messages = $chatSession->messages;

        // $messages->load('user');
        $messages = Message::query()
            ->where('chat_session_id', $chatSession->id)
            ->addSelect([
                'nickname' => Participant::select('nickname')
                    ->whereColumn('user_id', 'messages.sender_id')
                    ->where('chat_session_id', $chatSession->id)
                    ->take(1)
            ])
            ->with('user')
            ->get();

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
        if ($request->type == 'group') {
            $request->validate([
                'name' => ['required', 'string'],
            ]);

            ChatSession::createAsGroup($request->name, $request->users);

            return Redirect::back()->with('success', 'Group created!');
        }

        $request->validate([
            'email' => 'required|email|exists:users',
            'token' => 'required',
        ]);

        ChatSession::createAsNormal($request->email, $request->token);

        return Redirect::back()->with('success', 'Contact added!');
    }
}
