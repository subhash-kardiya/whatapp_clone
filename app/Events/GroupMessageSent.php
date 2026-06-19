<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('group.' . $this->message->group_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->message->id,
            'sender_id'   => $this->message->sender_id,
            'group_id'    => $this->message->group_id,
            'message'     => $this->message->message,
            'type'        => $this->message->type,
            'status'      => $this->message->status,
            'file_url'    => $this->message->fileUrl(),
            'file_name'   => $this->message->file_name,
            'time'        => $this->message->timeFormatted(),
            'sender_name' => $this->message->sender->name,
            'reply_to_id' => $this->message->reply_to_id,
        ];
    }

    public function broadcastAs(): string
    {
        return 'GroupMessageSent';
    }
}
