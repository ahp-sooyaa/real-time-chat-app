<?php

namespace App\Http\Controllers;

use App\Events\MessageDeleted;
use App\Models\Message;
use App\Events\MessageSent;
use App\Events\MessageUpdated;
use App\Http\Resources\MessageResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

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

        MessageSent::dispatch(MessageResource::make($message));

        return to_route('chatsession.show', $request->chatSessionId);
    }

    public function update(Message $message, Request $request)
    {
        $message->update(['content' => $request->content]);

        MessageUpdated::dispatch(MessageResource::make($message));

        return to_route('chatsession.show', $message->chat_session_id);
    }

    public function destroy(Message $message)
    {
        $message->delete();
        
        MessageDeleted::dispatch(MessageResource::make($message));

        return to_route('chatsession.show', $message->chat_session_id);
    }
}
