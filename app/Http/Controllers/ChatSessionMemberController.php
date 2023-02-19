<?php

namespace App\Http\Controllers;

use App\Events\LeaveChatSession;
use App\Events\MessageSent;
use App\Models\ChatSession;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ChatSessionMemberController extends Controller
{
    public function update(ChatSession $chatSession, User $member, Request $request) {
        $member->chatSessions()->updateExistingPivot($chatSession->id, ['nickname' => $request->nickname]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users'],
        ]);

        if($request->email != Auth::user()->email) {
            $chat_session = ChatSession::find($request->chatSessionId);

            $member = User::where('email', $request->email)->first();
            
            $chat_session->users()->attach($member->id);
        }

        return Redirect::back();
    }

    public function destroy(ChatSession $chatSession, User $member, Request $request) {
        $chatSession->users()->detach($member->id);
        
        $message = Message::create([
            'sender_id' => Auth::id(),
            'chat_session_id' => $chatSession->id,
            'content' => $request->action,
            'sent_by' => 'system',
        ]);
        
        MessageSent::dispatch($message->load('user'));
        // LeaveChatSession::dispatch($chatSession, $member);

        return Redirect::route('dashboard');
    }
}
