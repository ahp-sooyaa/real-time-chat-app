<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
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

    // this relationship is not require
    // public function chatSession()
    // {
    //     return $this->hasMany(ChatSession::class);
    // }

    // protected static function booted()
    // {
    //     static::addGlobalScope('nickname', function (Builder $builder) {
    //         $builder->addSelect(['nickname' => Participant::select('nickname')->whereColumn('users.id', 'participants.user_id')->take(1)]);
    //     });
    // }

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
        // dd($chatSessions);

        return $chatSessions;
    }
}
