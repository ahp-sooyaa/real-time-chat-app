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
        return $this->belongsToMany(User::class, 'participants')->withPivot('nickname')->withTimestamps();
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

        $memberIds = collect($users)->pluck('id');

        $groupChat->users()->attach([
            $memberIds,
            Auth::id() => ['is_owner' => true]
        ]);

        // need to dispatch newchatsession event
    }

    public static function createAsNormal($userId, $token)
    {
        $user = User::find($userId);

        if ($user->qrCode->token == $token) { // this condition seem like duplicate because it is already done in chatsessionmembercontroller
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
