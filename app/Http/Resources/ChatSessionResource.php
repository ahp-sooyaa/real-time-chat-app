<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatSessionResource extends JsonResource
{
    public $preserveKeys = true;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $messages = $this->messages();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_group' => $this->is_group,
            'latest_message' => $messages->latest()->first(),
            'new_message_count' => $messages->where('read_at', null)->where('sender_id', '!=', auth()->id())->count(),
            'messages' => $this->messages,
            // 'user' => $this->when(!$this->is_group, User::where('id', '!=', auth()->id())->whereHas('chatSessions', function ($query) {
            //     $query->where('chat_sessions.id', $this->id);
            // })->first()),
            'user' => $this->when(!$this->is_group, $this->users->filter(function ($user) {
                return $user->id != auth()->id();
            })->first())
        ];
    }
}
