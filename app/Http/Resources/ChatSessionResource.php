<?php

namespace App\Http\Resources;

use App\Models\Message;
use App\Models\Participant;
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
        $participant = Participant::where('user_id', auth()->id())->where('chat_session_id', $this->id)->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_group' => $this->is_group,
            'latest_message' => $this->messages()->latest()->first(),
            'new_message_count' => $this->messages()
                ->where('updated_at', '>', $participant->last_read_at ?? $participant->created_at)
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
