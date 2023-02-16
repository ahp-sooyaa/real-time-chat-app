<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ChatSessionMemberController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users'],
        ]);

        $chat_session = ChatSession::find($request->chatSessionId);
        $member = User::where('email', $request->email)->first();

        $chat_session->users()->attach($member->id);

        return Redirect::back();
    }
}
