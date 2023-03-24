<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChatSessionResource;
use App\Http\Resources\MessageResource;
use App\Http\Resources\UserResource;
use Inertia\Inertia;
use App\Models\ChatSession;
use App\Models\Message;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ChatSessionController extends Controller
{
    public function index()
    {
        // trying to refactor
        // $chatSessions = ChatSession::query()
        //     ->select('id', 'name as group_name', 'is_group')
        //     ->where('is_group', true)
        //     ->whereHas('users', function ($query) {
        //         $query->where('users.id', auth()->id());
        //     })
        //     ->addSelect([
        //         'latest_message' => Message::select('content')
        //             ->whereColumn('chat_session_id', 'chat_sessions.id')
        //             ->latest()
        //             ->take(1)
        //     ])
        //     ->get();

        // $privateChats = ChatSession::query()
        //     ->select('id', 'name as group_name', 'is_group')
        //     ->where('is_group', false)
        //     ->whereHas('users', function ($query) {
        //         $query->where('users.id', auth()->id());
        //     });

        return Inertia::render('Chat/Index', [
            'chatSessions' => ChatSessionResource::collection(Auth::user()->chatSessions)
        ]);
    }

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
            ->withTrashed()
            ->where('chat_session_id', $chatSession->id)
            ->addSelect([
                'nickname' => Participant::select('nickname')
                    ->whereColumn('user_id', 'messages.sender_id')
                    ->where('chat_session_id', $chatSession->id)
                    ->take(1)
            ])
            ->with('user')
            ->orderBy('created_at')
            ->get();

        Participant::query()
            ->where('chat_session_id', $chatSession->id)
            ->where('user_id', Auth::id())
            ->update(['last_read_at' => now()]);

        return Inertia::render('Chat/Show', [
            'messages' => MessageResource::collection($messages),
            'chatSession' => ChatSessionResource::make($chatSession)->only('id', 'name', 'is_group'),
            'participants' => UserResource::collection($chatSession->users)
        ]);
    }

    public function store(Request $request)
    {
        // this method will have 3 conditions
        // 1. creating group chat
        // 2. adding friend with qr code
        // 3. adding friend via search feature
        if ($request->is_group) {
            $request->validate([
                'name' => ['required', 'string'],
            ]);

            ChatSession::createAsGroup($request->name, $request->users);

            return Redirect::back()->with('success_message', 'Group created!');
        }

        // token shouldn't require if user add friend from search feature (otherwise I need to pass token when display user in search result)
        $request->validate([
            'id' => 'required|exists:users',
            'token' => 'required', // need to use something like 'required if' rule (require if user turn off allow other to add me as friend)
        ]);

        ChatSession::createAsNormal($request->id, $request->token);

        return Redirect::back()->with('success_message', 'Friend added!');
    }
}
