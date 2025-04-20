<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GajiNotification
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $title;
    public $body;
    public $type;

    public function __construct($title, $body, $type = 'success')
    {
        //
        $this->title = $title;
        $this->body = $body;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new Channel('notifications');
    }

    public function broadcastAs()
    {
        return 'notification.sent';
    }

    public function broadcastWith()
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
        ];
    }
}
