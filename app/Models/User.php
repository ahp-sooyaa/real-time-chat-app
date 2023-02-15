<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function chatSessions()
    {
        return $this->belongsToMany(ChatSession::class, 'participants');
    }

    public function loadChatSessions()
    {
        $chat_sessions = $this->chatSessions;

        $chat_sessions->load([
            'users' => function ($query) {
                $query->where('name', '!=', $this->name);
            },
            'messages' => function ($query) {
                $query->latest();
            }
        ]);

        $chat_sessions->loadCount([
            'messages' => function ($query) {
                $query->where('read_at', null)->where('sender_id', '!=', $this->id);
            }
        ]);

        return $chat_sessions;
    }
}
