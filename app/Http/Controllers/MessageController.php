<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\MessageSent;
use App\Models\ChatSession;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'chat_session_id' => $request->chatSessionId,
            'content' => $request->message
        ]);

        // $message['sender_nickname'] = Participant::query()
        //     ->where('user_id', Auth::id())
        //     ->where('chat_session_id', $request->chatSessionId)
        //     ->first()
        //     ->nickname;
        // dd($message->load('user'));
        MessageSent::dispatch($message->load('user'));
    }
}
