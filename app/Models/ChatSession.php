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

    protected $casts = [
        'is_group' => 'boolean'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'participants')->withPivot('nickname', 'is_owner')->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public static function createAsGroup($name, $users)
    {
        $groupChat = ChatSession::create([
            'name' => $name,
            'is_group' => true,
        ]);

        $memberIds = collect($users)->mapWithKeys(function (array $item, int $key) {
            return [$item['id'] => ['is_owner' => false]];
        });

        $memberIds[Auth::id()] = ['is_owner' => true];

        $groupChat->users()->attach($memberIds);

        NewChatSessionCreated::dispatch($groupChat);
    }

    public static function createAsNormal(User $user)
    {
        $chatSession = ChatSession::create();

        $chatSession->users()->attach([
            $user->id => ['is_owner' => false],
            Auth::id() => ['is_owner' => true]
        ]);

        $chatSession->load([
            'users' => function ($query) use ($user) {
                $query->where('name', '!=', $user->name);
            },
            'messages' => function ($query) {
                $query->latest();
            }
        ]);

        // this message count is not require because first time added friend doesn't have any message yet.
        $participant = Participant::query()
            ->where('user_id', auth()->id())
            ->where('chat_session_id', $chatSession->id)
            ->first();

        $chatSession->loadCount([
            'messages' => function ($query) use ($user, $participant) {
                $query->where('updated_at', '>', $participant->last_read_at ?? $participant->created_at)->where('sender_id', '!=', $user->id);
            }
        ]);

        NewChatSessionCreated::dispatch($chatSession);

        return $chatSession;
    }
}
