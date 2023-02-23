<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
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

    /**
     * Get the qrCode associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function qrCode()
    {
        return $this->hasOne(Qrcode::class);
    }

    public function chatSessions()
    {
        return $this->belongsToMany(ChatSession::class, 'participants')->withPivot('nickname')->withTimestamps();
    }

    public function loadChatSessions()
    {
        $chatSessions = $this->chatSessions;

        $chatSessions->load([
            'users' => function ($query) {
                $query->where('name', '!=', $this->name);
            },
            'messages' => function ($query) {
                $query->latest();
            }
        ]);

        $chatSessions->loadCount([
            'messages' => function ($query) {
                $query->where('read_at', null)->where('sender_id', '!=', $this->id);
            }
        ]);

        return $chatSessions;
    }
}
