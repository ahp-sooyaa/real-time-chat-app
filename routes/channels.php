<?php

use App\Models\ChatSession;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('chatsession.{id}', function ($user, $id) {
    if (ChatSession::find($id)->users->contains($user->id)) {
        return ['name' => $user->name];
    }
});
