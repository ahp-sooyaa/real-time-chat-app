<?php

namespace App\Events;

use App\Http\Resources\MessageResource;
use App\Models\Participant;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public $message)
    {
        $this->dontBroadcastToCurrentUser();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('chatsession.' . $this->message->chat_session_id);
    }

    public function broadcastWith()
    {
        return [
            'message' => [
                'sender_id' => auth()->id(),
                'sender_name' => auth()->user()->name,
                'sender_nickname' => Participant::where('user_id', auth()->id())->value('nickname'),
                'content' => $this->message->content,
                'sent_by' => 'user',
                'created_at' => now()
            ]
        ];
    }
}
