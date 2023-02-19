<?php

namespace App\Models;

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

    public static function createAsGroup($name) {
        $chat_session = ChatSession::create([
            'creator_id' => Auth::id(), 
            'name' => $name, 
            'type' => 'group', 
            'active_at' => now()
        ]);

        $chat_session->users()->attach(Auth::id());
    }

    public static function createAsNormal($email) {
        $user = User::where('email', $email)->first();

        $chatSession = ChatSession::create(['creator_id' => Auth::id(), 'type' => 'normal']);

        $chatSession->users()->attach([$user->id, Auth::id()]);
    }
}
