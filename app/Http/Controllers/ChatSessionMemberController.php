<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\QrCode;
use App\Models\Message;
use App\Events\MessageSent;
use App\Models\ChatSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\NewChatSessionCreated;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ChatSessionMemberController extends Controller
{
    public function update(ChatSession $chatSession, User $member, Request $request)
    {
        $request->validate([
            'nickname' => 'required|string'
        ]);

        $member->chatSessions()->updateExistingPivot($chatSession->id, ['nickname' => $request->nickname]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        $chatSession = ChatSession::find($request->chatSessionId);

        $member = User::where('email', $request->email)->first();

        if ($request->email == Auth::user()->email || $chatSession->users->contains($member)) {
            return Redirect::back()->with('error_message', 'It is already in the group chat');
        }

        $chatSession->users()->attach($member->id);

        $chatSession->load([
            'users' => function ($query) use ($member) {
                $query->where('name', '!=', $member->name);
            },
            'messages' => function ($query) {
                $query->latest();
            }
        ]);

        $chatSession->loadCount([
            'messages' => function ($query) use ($member) {
                $query->where('read_at', null)->where('sender_id', '!=', $member->id);
            }
        ]);

        NewChatSessionCreated::dispatch($chatSession);

        return Redirect::back();
    }

    public function destroy(ChatSession $chatSession, User $member, Request $request)
    {
        $chatSession->users()->detach($member->id);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'chat_session_id' => $chatSession->id,
            'content' => $request->action,
            'sent_by' => 'system',
        ]);

        MessageSent::dispatch($message->load('user'));

        return Redirect::route('dashboard');
    }

    public function addFriendWithQrCodeOrLink(User $user, $token)
    {
        if ($token != $user->qrCode->token) {
            return redirect('/chats')->withErrors('Your qr code doesn\'t correct.');
        }

        return Inertia::render('AddFriend', [
            'user' => $user,
            'token' => $token,
        ]);
    }
}
