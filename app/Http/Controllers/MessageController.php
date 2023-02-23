<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\ChatSession;
use App\Models\Message;
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

        $chatSession = ChatSession::find($request->chatSessionId);

        if (Auth::id() != $chatSession->creator_id) {
            $chatSession->update(['active_at' => now()]);
        }

        MessageSent::dispatch($message->load('user'));
    }
}
