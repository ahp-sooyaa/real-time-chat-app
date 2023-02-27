<?php

namespace App\Http\Resources;

use App\Models\Message;
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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_group' => $this->is_group,
            'latest_message' => $this->messages()->latest()->first(),
            // 'latest_message' => MessageResource::make(
            //     Message::where('chat_session_id', $this->id)->latest()->first()
            // )->only('sender_id', 'content', 'read_at'),
            'new_message_count' => $this->messages()
                ->where('read_at', null)
                ->where('sender_id', '!=', auth()->id())
                ->count(),
            'user' => $this->when(!$this->is_group, $this->users->filter(function ($user) {
                return $user->id != auth()->id();
            })->first()),
            'users' => UserResource::collection($this->users)
        ];
    }

    public function only(...$attributes)
    {
        return collect($this->resolve())
            ->only($attributes)
            ->toArray();
    }
}
