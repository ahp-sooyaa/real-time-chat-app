<?php

namespace App\Models;

use App\Events\NewChatSessionCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ChatSession extends Model
{
    use HasFactory;

    public $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'participants')->withPivot('nickname')->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public static function createAsGroup($name, $users)
    {
        $groupChat = ChatSession::create([
            'creator_id' => Auth::id(),
            'name' => $name,
            'type' => 'group',
            'active_at' => now()
        ]);

        $memberIds = collect($users)->pluck('id')->merge(Auth::id());

        $groupChat->users()->attach($memberIds);
    }

    public static function createAsNormal($userId, $token)
    {
        $user = User::find($userId);

        if ($user->qrCode->token == $token) { // this condition seem like duplicate because it is already done in chatsessionmembercontroller
            $chatSession = ChatSession::create(['creator_id' => Auth::id(), 'type' => 'normal']);

            $chatSession->users()->attach([$user->id, Auth::id()]);

            $chatSession->load([
                'users' => function ($query) use ($user) {
                    $query->where('name', '!=', $user->name);
                },
                'messages' => function ($query) {
                    $query->latest();
                }
            ]);

            $chatSession->loadCount([
                'messages' => function ($query) use ($user) {
                    $query->where('read_at', null)->where('sender_id', '!=', $user->id);
                }
            ]);

            NewChatSessionCreated::dispatch($chatSession);
        } else {
            return back()->withErrors('Your token mismatch');
        }
    }
}
