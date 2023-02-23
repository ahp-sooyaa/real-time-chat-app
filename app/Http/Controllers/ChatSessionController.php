<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\ChatSession;
use App\Models\Message;
use App\Models\Participant;
use Illuminate\Http\Request;
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
        // this method will have 3 conditions
        // 1. creating group chat
        // 2. adding friend with qr code
        // 3. adding friend via search feature
        if ($request->type == 'group') {
            $request->validate([
                'name' => ['required', 'string'],
            ]);

            ChatSession::createAsGroup($request->name, $request->users);

            return Redirect::back()->with('success', 'Group created!');
        }

        // token shouldn't require if user add friend from search feature (otherwise I need to pass token when display user in search result)
        $request->validate([
            'id' => 'required|exists:users',
            'token' => 'required', // need to use something like required if rule (require if user turn off allow other to add me as friend)
        ]);

        ChatSession::createAsNormal($request->id, $request->token);

        return Redirect::back()->with('success', 'Contact added!');
    }
}
