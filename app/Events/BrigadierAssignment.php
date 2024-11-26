<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BrigadierAssignment implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $brigadierId;
    public $institutionId;

    /**
     * Create a new event instance.
     */
    public function __construct($institutionId, $brigadierId)
    {
        $this->institutionId = $institutionId;
        $this->brigadierId = $brigadierId;
    }

    public function broadcastAs(): string
    {
        return "brigadierAssignment";
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('public-channel.' . $this->institutionId . '.' . $this->brigadierId),
            new PrivateChannel('public-channel.' . $this->institutionId),
        ];
    }
}
