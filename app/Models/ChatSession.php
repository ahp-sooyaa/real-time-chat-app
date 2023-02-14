<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    use HasFactory;

    public function users()
    {
        return $this->belongsToMany(User::class, 'participants');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }
}
