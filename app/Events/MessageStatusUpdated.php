<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $senderId,
        public int $receiverId,
        public string $status,          // 'delivered' | 'seen'
        public ?int $messageId = null   // null = all messages in conversation
    ) {
    }

    public function broadcastOn(): array
    {
        // Notify the original sender their message was delivered/seen
        return [
            new PrivateChannel('chat.' . $this->senderId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'sender_id'   => $this->senderId,
            'receiver_id' => $this->receiverId,
            'status'      => $this->status,
            'message_id'  => $this->messageId,
        ];
    }

    public function broadcastAs(): string
    {
        return 'MessageStatusUpdated';
    }
}
