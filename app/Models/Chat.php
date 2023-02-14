<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    public $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chat_session()
    {
        return $this->belongsTo(ChatSession::class);
    }
}
