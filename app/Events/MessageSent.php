<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->receiver_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->message->id,
            'sender_id'   => $this->message->sender_id,
            'receiver_id' => $this->message->receiver_id,
            'message'     => $this->message->message,
            'type'        => $this->message->type,
            'status'      => $this->message->status,
            'file_path'   => $this->message->file_path ? asset('storage/' . $this->message->file_path) : null,
            'file_name'   => $this->message->file_name,
            'time'        => $this->message->created_at->format('h:i A'),
            'sender_name' => $this->message->sender->name,
            'sender_avatar' => $this->message->sender->avatarUrl(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MessageSent';
    }
}
