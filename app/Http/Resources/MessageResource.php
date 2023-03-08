<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'sender_id' => $this->sender_id,
            'sender_name' => $this->user->name,
            'sender_nickname' => $this->nickname,
            'content' => $this->content,
            'sent_by' => $this->sent_by,
            'created_at' => $this->created_at,
        ];
    }

    public function only(...$attributes)
    {
        return collect($this->resolve())
            ->only($attributes)
            ->toArray();
    }
}
